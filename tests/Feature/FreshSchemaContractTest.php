<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FreshSchemaContractTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('The pdo_sqlite extension is required for the fresh-schema test.');
        }

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Artisan::call('migrate:fresh', ['--force' => true]);
    }

    public function test_fresh_database_contains_columns_required_by_application(): void
    {
        $expectedColumns = [
            'users' => [
                'is_suspend', 'is_document_verify', 'is_email_verify', 'emergency_number',
                'emergency_name', 'suspended_at', 'suspend_reason', 'device_type', 'device_token',
            ],
            'admin_settings' => ['booking_fee', 'trip_booking_fee', 'driver_payout_fee'],
            'rides' => ['total_seats', 'kilometer', 'ride_status', 'payout_status', 'refund_status'],
            'bookings' => [
                'seat_price', 'booking_fee', 'pin_start', 'pin_end', 'is_verify_spin',
                'is_verify_epin', 'refund_seat_amount', 'refund_booking_fee_amount',
                'driver_compensation', 'is_late_cancellation', 'late_review_added',
                'remaining_refund_paid', 'cancel_type', 'cancelled_at', 'is_drop_off',
            ],
            'trip_bids' => [
                'driver_id', 'total_fare', 'seat_price', 'booking_fee', 'refund_seat_amount',
                'refund_booking_fee_amount', 'driver_compensation', 'is_late_cancellation',
                'late_review_added', 'remaining_refund_paid', 'cancel_type', 'cancelled_at',
            ],
            'trip_requests' => [
                'driver_id', 'is_verify_spin', 'is_verify_epin', 'pin_start', 'pin_end',
                'is_drop_off', 'payout_status', 'refund_status',
            ],
            'ratings' => ['user_id', 'driver_id'],
            'notifications' => [
                'trip_id', 'bid_id', 'ride_id', 'title', 'booked_user_id',
                'ride_trip_type', 'is_my_ride', 'n_type',
            ],
        ];

        foreach ($expectedColumns as $table => $columns) {
            foreach ($columns as $column) {
                $this->assertTrue(
                    Schema::hasColumn($table, $column),
                    "Fresh schema is missing {$table}.{$column}."
                );
            }
        }

        $this->assertFalse(Schema::hasColumn('trip_bids', 'passenger_id'));
        $this->assertFalse(Schema::hasColumn('ratings', 'reviewer_id'));
        $this->assertFalse(Schema::hasColumn('ratings', 'reviewee_id'));
    }
}
