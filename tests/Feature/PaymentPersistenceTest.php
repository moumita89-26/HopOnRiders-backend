<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\CommonController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class PaymentPersistenceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('passenger_id');
            $table->decimal('total_fare', 12, 2);
            $table->timestamps();
        });
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('trip_request_id')->nullable();
            $table->unsignedBigInteger('trip_bid_id')->nullable();
            $table->string('payment_method');
            $table->string('payment_provider')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('status');
            $table->string('payment_reference')->nullable();
            $table->string('paygo_transaction_reference')->nullable();
            $table->timestamps();
        });
    }

    public function test_successful_status_response_inserts_completed_payment(): void
    {
        $bookingId = DB::table('bookings')->insertGetId([
            'trip_id' => 18,
            'passenger_id' => 80,
            'total_fare' => 1125,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $request = Request::create('/', 'POST', [
            'tripId' => '18',
            'userId' => '80',
            'totalFare' => '1125',
            'provider' => 'airtel',
            'merRef' => 'HOPON_20260903162916_3129',
            'paygoReqRef' => '6a33372e043cf52e10f0a50bd8d8e912',
        ]);
        $response = [
            'status' => 'SUCCESS',
            'txn' => [
                'reqRef' => '6a33372e043cf52e10f0a50bd8d8e912',
                'merRef' => 'HOPON_20260903162916_3129',
                'mnoRef' => 'PGAC6a33372e043cf52e10f0a50bd8d8e912',
            ],
        ];

        $method = new ReflectionMethod(CommonController::class, 'storePaymentResult');
        $method->invoke(app(CommonController::class), $request, $response);

        $this->assertDatabaseHas('payments', [
            'booking_id' => $bookingId,
            'payment_method' => 'mobile_money',
            'payment_provider' => 'airtel',
            'amount' => 1125,
            'status' => 'completed',
            'payment_reference' => 'HOPON_20260903162916_3129',
            'paygo_transaction_reference' => 'PGAC6a33372e043cf52e10f0a50bd8d8e912',
        ]);
    }
}
