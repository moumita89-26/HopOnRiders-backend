<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\DriverPayout;
use App\Models\Payment;
use App\Models\TripRequest;
use App\Services\DriverPayoutService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DriverPayoutServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');

        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->timestamps();
        });
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('passenger_id');
            $table->unsignedInteger('seats_booked');
            $table->decimal('total_fare', 12, 2);
            $table->decimal('booking_fee', 12, 2);
            $table->unsignedInteger('is_verify_epin')->default(0);
            $table->timestamps();
        });
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->unsignedBigInteger('trip_request_id')->nullable();
            $table->unsignedBigInteger('trip_bid_id')->nullable();
            $table->string('status');
            $table->string('payment_reference')->nullable();
            $table->string('paygo_transaction_reference')->nullable();
            $table->timestamps();
        });
        Schema::create('driver_payouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable()->unique();
            $table->unsignedBigInteger('ride_id')->nullable();
            $table->unsignedBigInteger('trip_request_id')->nullable()->unique();
            $table->unsignedBigInteger('trip_bid_id')->nullable();
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('passenger_id');
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedInteger('seats_booked');
            $table->decimal('passenger_fare', 12, 2);
            $table->decimal('hopon_fee', 12, 2);
            $table->decimal('driver_payable', 12, 2);
            $table->string('passenger_payment_reference')->nullable();
            $table->string('paygo_transaction_reference')->nullable();
            $table->string('customer_payment_status');
            $table->timestamp('completion_verified_at');
            $table->string('payout_status');
            $table->string('settlement_reference')->nullable();
            $table->date('settlement_date')->nullable();
            $table->unsignedBigInteger('settled_by')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });
        Schema::create('trip_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->unsignedBigInteger('passenger_id');
            $table->unsignedInteger('seats_required')->default(1);
            $table->unsignedInteger('is_verify_epin')->default(0);
            $table->unsignedInteger('status')->default(1);
            $table->timestamps();
        });
        Schema::create('trip_bids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id');
            $table->unsignedBigInteger('driver_id');
            $table->decimal('total_fare', 12, 2)->nullable();
            $table->decimal('booking_fee', 12, 2)->nullable();
            $table->unsignedInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function test_completed_paid_booking_creates_one_pending_settlement(): void
    {
        $rideId = DB::table('rides')->insertGetId(['driver_id' => 10, 'created_at' => now(), 'updated_at' => now()]);
        $bookingId = DB::table('bookings')->insertGetId([
            'trip_id' => $rideId,
            'passenger_id' => 20,
            'seats_booked' => 1,
            'total_fare' => 100,
            'booking_fee' => 10,
            'is_verify_epin' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $booking = Booking::findOrFail($bookingId);
        Payment::create([
            'booking_id' => $booking->id,
            'status' => 'completed',
            'payment_reference' => 'ORDER-1',
            'paygo_transaction_reference' => 'PG-1',
        ]);

        $service = app(DriverPayoutService::class);
        $service->createPendingForCompletedBooking($booking);
        $service->createPendingForCompletedBooking($booking);

        $this->assertDatabaseCount('driver_payouts', 1);
        $this->assertDatabaseHas('driver_payouts', [
            'booking_id' => $booking->id,
            'driver_payable' => 90,
            'payout_status' => DriverPayout::STATUS_PENDING,
            'paygo_transaction_reference' => 'PG-1',
        ]);
    }

    public function test_unpaid_booking_does_not_create_a_settlement(): void
    {
        $rideId = DB::table('rides')->insertGetId(['driver_id' => 10, 'created_at' => now(), 'updated_at' => now()]);
        $bookingId = DB::table('bookings')->insertGetId([
            'trip_id' => $rideId,
            'passenger_id' => 20,
            'seats_booked' => 1,
            'total_fare' => 100,
            'booking_fee' => 10,
            'is_verify_epin' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $booking = Booking::findOrFail($bookingId);
        Payment::create(['booking_id' => $booking->id, 'status' => 'pending']);

        $result = app(DriverPayoutService::class)->createPendingForCompletedBooking($booking);

        $this->assertNull($result);
        $this->assertDatabaseCount('driver_payouts', 0);
    }

    public function test_completed_paid_trip_request_creates_one_pending_settlement(): void
    {
        $tripRequestId = DB::table('trip_requests')->insertGetId([
            'driver_id' => 10,
            'passenger_id' => 20,
            'seats_required' => 2,
            'is_verify_epin' => 1,
            'status' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $tripBidId = DB::table('trip_bids')->insertGetId([
            'trip_id' => $tripRequestId,
            'driver_id' => 10,
            'total_fare' => 120,
            'booking_fee' => 20,
            'status' => 3,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        Payment::create([
            'booking_id' => null,
            'trip_request_id' => $tripRequestId,
            'trip_bid_id' => $tripBidId,
            'status' => 'completed',
            'payment_reference' => 'TRIP-ORDER-1',
            'paygo_transaction_reference' => 'TRIP-PG-1',
        ]);

        $service = app(DriverPayoutService::class);
        $tripRequest = TripRequest::findOrFail($tripRequestId);
        $service->createPendingForCompletedTripRequest($tripRequest);
        $service->createPendingForCompletedTripRequest($tripRequest);

        $this->assertDatabaseCount('driver_payouts', 1);
        $this->assertDatabaseHas('driver_payouts', [
            'trip_request_id' => $tripRequestId,
            'trip_bid_id' => $tripBidId,
            'driver_payable' => 100,
            'payout_status' => DriverPayout::STATUS_PENDING,
        ]);
    }
}
