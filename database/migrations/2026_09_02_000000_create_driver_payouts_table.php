<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_payouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('booking_id')->nullable()->unique();
            $table->unsignedBigInteger('ride_id')->nullable()->index();
            $table->unsignedBigInteger('trip_request_id')->nullable()->unique();
            $table->unsignedBigInteger('trip_bid_id')->nullable()->index();
            $table->unsignedBigInteger('driver_id')->index();
            $table->unsignedBigInteger('passenger_id')->index();
            $table->unsignedBigInteger('payment_id')->nullable()->index();
            $table->unsignedInteger('seats_booked')->default(1);
            $table->decimal('passenger_fare', 12, 2);
            $table->decimal('hopon_fee', 12, 2)->default(0);
            $table->decimal('driver_payable', 12, 2);
            $table->string('passenger_payment_reference')->nullable()->index();
            $table->string('paygo_transaction_reference')->nullable()->index();
            $table->string('customer_payment_status', 30);
            $table->timestamp('completion_verified_at');
            $table->string('payout_status', 30)->default('pending_settlement')->index();
            $table->string('settlement_reference')->nullable()->index();
            $table->date('settlement_date')->nullable();
            $table->unsignedBigInteger('settled_by')->nullable()->index();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_payouts');
    }
};
