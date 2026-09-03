<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('driver_payouts')) {
            Schema::table('driver_payouts', function (Blueprint $table) {
                $table->unsignedBigInteger('booking_id')->nullable()->change();
                $table->unsignedBigInteger('ride_id')->nullable()->change();

                if (! Schema::hasColumn('driver_payouts', 'trip_request_id')) {
                    $table->unsignedBigInteger('trip_request_id')->nullable()->unique();
                }
                if (! Schema::hasColumn('driver_payouts', 'trip_bid_id')) {
                    $table->unsignedBigInteger('trip_bid_id')->nullable()->index();
                }
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedBigInteger('booking_id')->nullable()->change();

                if (! Schema::hasColumn('payments', 'trip_request_id')) {
                    $table->unsignedBigInteger('trip_request_id')->nullable()->unique();
                }
                if (! Schema::hasColumn('payments', 'trip_bid_id')) {
                    $table->unsignedBigInteger('trip_bid_id')->nullable()->index();
                }
            });
        }
    }

    public function down(): void
    {
        // Preserve settlement/payment links because these columns may contain financial audit data.
    }
};
