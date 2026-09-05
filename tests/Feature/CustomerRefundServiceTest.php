<?php

namespace Tests\Feature;

use App\Services\CustomerRefundService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CustomerRefundServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if ($socket = getenv('HOPON_REFUND_TEST_MYSQL_SOCKET')) {
            config()->set('database.default', 'mysql');
            config()->set('database.connections.mysql', [
                'driver' => 'mysql', 'unix_socket' => $socket, 'database' => 'hopon_refund_test',
                'username' => 'root', 'password' => '', 'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci', 'prefix' => '', 'strict' => true,
            ]);
            DB::purge('mysql');
        } else {
            if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
                $this->markTestSkipped('Requires pdo_sqlite or isolated HOPON_REFUND_TEST_MYSQL_SOCKET.');
            }
            config()->set('database.default', 'sqlite');
            config()->set('database.connections.sqlite.database', ':memory:');
            DB::purge('sqlite');
        }
        foreach (['customer_refund_allocations', 'customer_refunds', 'customer_refund_legacy', 'driver_settlements', 'bookings', 'trip_bids', 'rides', 'trip_requests', 'users', 'admin_users'] as $table) {
            Schema::dropIfExists($table);
        }
        foreach (['users', 'admin_users'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->string('name');
                $t->string('phone')->nullable();
            });
        }
        foreach (['rides', 'trip_requests'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('passenger_id')->nullable();
                $t->unsignedBigInteger('driver_id')->nullable();
                $t->integer('refund_status')->default(0);
            });
        }
        foreach (['bookings', 'trip_bids'] as $table) {
            Schema::create($table, function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('trip_id');
                $t->unsignedBigInteger('passenger_id')->nullable();
                $t->integer('status')->default(4);
                $t->integer('cancel_type')->default(5);
                $t->decimal('total_fare', 12, 2)->default(120);
                $t->decimal('booking_fee', 12, 2)->default(20);
                $t->integer('is_late_cancellation')->default(0);
                $t->integer('remaining_refund_paid')->default(0);
                $t->timestamp('cancelled_at')->nullable();
                $t->timestamps();
            });
        }
        (require database_path('migrations/2026_09_05_000001_create_customer_refund_ledger.php'))->up();
        Schema::create('driver_settlements', function (Blueprint $t) {
            $t->id();
            $t->date('settlement_date');
            $t->timestamps();
        });
        DB::table('users')->insert([['id' => 1, 'name' => 'Customer One', 'phone' => '100'], ['id' => 2, 'name' => 'Customer Two', 'phone' => '200']]);
        DB::table('admin_users')->insert(['id' => 1, 'name' => 'Test Admin']);
        DB::table('rides')->insert(['id' => 10]);
        DB::table('trip_requests')->insert(['id' => 101, 'passenger_id' => 1, 'driver_id' => null]);
    }

    private function booking(array $data = []): int
    {
        return DB::table('bookings')->insertGetId($data + [
            'trip_id' => 10, 'passenger_id' => 1, 'cancelled_at' => '2026-08-01 10:00:00',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function pay(string $amount, ?string $key = null, array $filters = [], string $reference = 'REF-1')
    {
        return app(CustomerRefundService::class)->record(1, $amount, '2026-08-10', $reference, null, 1, $key ?? (string) Str::uuid(), $filters);
    }

    public function test_partial_refund_allocates_oldest_first_across_rides_and_cancelled_bids(): void
    {
        $id = $this->booking();
        DB::table('trip_bids')->insert(['id' => 20, 'trip_id' => 101, 'cancel_type' => 4, 'cancelled_at' => '2026-08-02 10:00:00']);
        $this->booking(['passenger_id' => 2]);
        $refund = $this->pay('140.00');
        $this->assertDatabaseHas('customer_refund_allocations', ['customer_refund_id' => $refund->id, 'source_type' => 'booking', 'source_id' => $id, 'amount' => 120]);
        $this->assertDatabaseHas('customer_refund_allocations', ['customer_refund_id' => $refund->id, 'source_type' => 'trip_bid', 'source_id' => 20, 'amount' => 20]);
        $summaries = app(CustomerRefundService::class)->summaries()->keyBy('customer_id');
        $this->assertSame(3000, $summaries[1]['pending_cents']);
        $this->assertSame(12000, $summaries[2]['pending_cents']);
    }

    public function test_over_refund_and_fractional_ngwee_are_rejected_without_history(): void
    {
        $this->booking();
        foreach (['120.01', '0', '-1', '1.001', '1e2'] as $amount) {
            try {
                $this->pay($amount);
                $this->fail('Invalid refund accepted.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('amount', $e->errors());
            }
        }
        $this->assertDatabaseCount('customer_refunds', 0);
        $this->assertDatabaseCount('customer_refund_allocations', 0);
    }

    public function test_repeated_submissions_and_reused_payment_reference_do_not_pay_twice(): void
    {
        $this->booking();
        $key = (string) Str::uuid();
        $first = $this->pay('50.00', $key);
        $this->assertSame($first->id, $this->pay('50.00', $key)->id);
        $this->assertSame($first->id, $this->pay('50.00')->id);
        $this->assertDatabaseCount('customer_refunds', 1);
        $this->assertSame(7000, app(CustomerRefundService::class)->summaries()->first()['pending_cents']);
    }

    public function test_old_refund_flags_are_snapshotted_and_additional_late_refund_remains_available(): void
    {
        $id = $this->booking(['cancel_type' => 4, 'is_late_cancellation' => 1]);
        DB::table('rides')->where('id', 10)->update(['refund_status' => 1]);
        $service = app(CustomerRefundService::class);
        $this->assertSame(0, $service->summaries()->first()['pending_cents']);
        $this->assertDatabaseHas('customer_refund_legacy', ['source_id' => $id, 'amount' => 50]);
        DB::table('bookings')->where('id', $id)->update(['cancel_type' => 5]);
        $this->assertSame(5000, $service->summaries()->first()['pending_cents']);
        $this->pay('50.00');
        $this->assertSame(0, $service->summaries()->first()['pending_cents']);
    }

    public function test_unknown_cancel_reason_and_no_show_do_not_enter_available_wallet(): void
    {
        $this->booking(['cancel_type' => 0]);
        $this->booking(['status' => 7, 'cancel_type' => 7]);
        $this->booking(['status' => 3]);
        $summary = app(CustomerRefundService::class)->summaries()->first();
        $this->assertSame(0, $summary['pending_cents']);
        $this->assertSame(1, $summary['review_count']);
        $this->assertCount(2, $summary['entries']);
    }

    public function test_filters_limit_refund_allocation_and_do_not_change_cancellation_dates(): void
    {
        $first = $this->booking();
        $second = $this->booking(['cancelled_at' => '2026-08-20 10:00:00']);
        $this->pay('120.00', null, ['from' => '2026-08-15', 'to' => '2026-08-31', 'source_type' => 'booking', 'journey_id' => 10]);
        $this->assertDatabaseHas('customer_refund_allocations', ['source_id' => $second, 'amount' => 120]);
        $this->assertDatabaseMissing('customer_refund_allocations', ['source_id' => $first]);
        $this->assertDatabaseHas('bookings', ['id' => $second, 'cancelled_at' => '2026-08-20 10:00:00']);
    }

    public function test_customer_tab_renders_and_refund_endpoint_requires_admin_and_confirmation(): void
    {
        $this->booking();
        $url = route('admin.settlements.refund-customer', 1);
        $this->post($url, [])->assertRedirect();
        view()->getFinder()->prependNamespace('admin', base_path('tests/Fixtures/views/admin'));
        $this->withSession(['admin_id' => 1, 'admin_lock' => false])
            ->get('/settlements?tab=customers')->assertOk()->assertSee('Customer Wallets')->assertSee('Customer One')->assertSee('Record refund');
        $payload = ['amount' => '25.00', 'refund_date' => '2026-08-10', 'reference' => 'WEB-REF', 'request_key' => (string) Str::uuid()];
        $this->post($url, $payload)->assertSessionHasErrors('confirmed');
        $this->assertDatabaseCount('customer_refunds', 0);
        $this->post($url, $payload + ['confirmed' => 1])->assertRedirect(route('admin.settlements.index', ['tab' => 'customers']));
        $this->assertDatabaseHas('customer_refunds', ['customer_id' => 1, 'amount' => 25, 'paid_by' => 1]);
    }

    public function test_driver_tab_remains_default_and_uses_existing_driver_service(): void
    {
        view()->getFinder()->prependNamespace('admin', base_path('tests/Fixtures/views/admin'));
        $this->mock(\App\Services\DriverSettlementService::class, function ($mock) {
            $mock->shouldReceive('driverSummaries')->once()->with(null, null)->andReturn(collect());
        });
        $this->withSession(['admin_id' => 1, 'admin_lock' => false])->get('/settlements')
            ->assertOk()->assertSee('Driver Settlement')->assertSee('Customer Refund')
            ->assertSee('Driver Payment History')->assertDontSee('Customer Wallets');
    }

    public function test_audit_history_survives_deleted_source_and_rollback_is_blocked(): void
    {
        $this->booking();
        $this->pay('50.00');
        DB::table('bookings')->delete();
        view()->getFinder()->prependNamespace('admin', base_path('tests/Fixtures/views/admin'));
        $this->withSession(['admin_id' => 1, 'admin_lock' => false])->get('/settlements?tab=customers')
            ->assertOk()->assertSee('REF-1')->assertSee('K50.00');

        $migration = require database_path('migrations/2026_09_05_000001_create_customer_refund_ledger.php');
        try {
            $migration->down();
            $this->fail('Rollback must not delete refund history.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Refund history exists', $e->getMessage());
        }
        $this->assertDatabaseCount('customer_refunds', 1);
    }

    public function test_customer_api_matches_admin_summary_and_hides_actions_and_other_customer_data(): void
    {
        $this->booking();
        $this->booking(['passenger_id' => 2]);
        $this->pay('50.00');
        $summary = app(CustomerRefundService::class)->summaries(['customer_id' => 1])->first();
        $response = $this->postJson('/api/customerRefunds', ['customerId' => 1]);
        $response->assertOk()->assertJsonPath('responseCode', 1)
            ->assertJsonPath('responseData.customer.id', 1)
            ->assertJsonPath('responseData.summary.policyRefund', '120.00')
            ->assertJsonPath('responseData.summary.alreadyRefunded', '50.00')
            ->assertJsonPath('responseData.summary.availableWallet', number_format($summary['pending_cents'] / 100, 2, '.', ''))
            ->assertJsonCount(1, 'responseData.bookings')
            ->assertJsonPath('responseData.bookings.0.alreadyRefunded', '50.00')
            ->assertJsonPath('responseData.refundHistory.0.amount', '50.00')
            ->assertJsonPath('responseData.historyPagination.total', 1);
        $this->assertStringNotContainsString('Customer Two', $response->getContent());
        $walk = function (array $data) use (&$walk) {
            foreach ($data as $key => $value) {
                $this->assertNotContains($key, ['action', 'actions', 'legacy_cents', 'legacyRefunded', 'paid_by', 'request_key', 'reason']);
                if (is_array($value)) {
                    $walk($value);
                }
            }
        };
        $walk($response->json());
    }

    public function test_customer_api_selects_requested_customer_and_returns_not_found_for_unknown_id(): void
    {
        $this->booking();
        $this->booking(['passenger_id' => 2, 'total_fare' => 200]);
        $this->postJson('/api/customerRefunds', ['customerId' => 2])
            ->assertOk()->assertJsonPath('responseData.customer.id', 2)
            ->assertJsonPath('responseData.summary.policyRefund', '200.00')
            ->assertJsonCount(1, 'responseData.bookings');
        $this->postJson('/api/customerRefunds', ['customerId' => 999])
            ->assertNotFound()->assertJsonPath('responseCode', 0);
        $this->assertDatabaseCount('customer_refunds', 0);
    }

    public function test_customer_api_reads_old_markers_without_creating_legacy_records(): void
    {
        $this->booking();
        DB::table('rides')->where('id', 10)->update(['refund_status' => 1]);
        $this->postJson('/api/customerRefunds', ['customerId' => 1])
            ->assertOk()->assertJsonPath('responseData.summary.alreadyRefunded', '120.00')
            ->assertJsonPath('responseData.summary.availableWallet', '0.00');
        $this->assertDatabaseCount('customer_refund_legacy', 0);
        $this->assertDatabaseCount('customer_refunds', 0);
        $this->assertDatabaseCount('customer_refund_allocations', 0);
    }

    public function test_customer_api_empty_wallet_and_invalid_payloads(): void
    {
        $this->postJson('/api/customerRefunds', ['customerId' => 1])
            ->assertOk()->assertJsonPath('responseData.summary.latestCancellation', null)
            ->assertJsonPath('responseData.summary.availableWallet', '0.00')
            ->assertJsonPath('responseData.summary.status', 'no_refund_due')
            ->assertJsonCount(0, 'responseData.bookings')->assertJsonCount(0, 'responseData.refundHistory');
        foreach ([[], ['customerId' => 0], ['customerId' => 'abc'], ['customerId' => 1, 'from' => '2026-08-10', 'to' => '2026-08-01'], ['customerId' => 1, 'historyLimit' => 101]] as $payload) {
            $this->postJson('/api/customerRefunds', $payload)->assertUnprocessable();
        }
    }

    public function test_customer_api_date_filter_and_history_pagination(): void
    {
        $this->booking();
        $this->booking(['cancelled_at' => '2026-08-20 10:00:00']);
        $this->pay('20.00', null, [], 'FIRST');
        $this->pay('30.00', null, [], 'SECOND');
        $this->postJson('/api/customerRefunds', [
            'customerId' => 1, 'from' => '2026-08-15', 'to' => '2026-08-31', 'historyPage' => 2, 'historyLimit' => 1,
        ])->assertOk()->assertJsonCount(1, 'responseData.bookings')
            ->assertJsonPath('responseData.summary.availableWallet', '120.00')
            ->assertJsonCount(1, 'responseData.refundHistory')
            ->assertJsonPath('responseData.refundHistory.0.reference', 'FIRST')
            ->assertJsonPath('responseData.historyPagination.total', 2);
    }
}
