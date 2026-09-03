<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_id')->nullable()->change();
            if (! Schema::hasColumn('payments', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->index();
            }
            if (! Schema::hasColumn('payments', 'paygo_transaction_reference')) {
                $table->string('paygo_transaction_reference')->nullable()->index();
            }
            if (! Schema::hasColumn('payments', 'trip_request_id')) {
                $table->unsignedBigInteger('trip_request_id')->nullable()->unique();
            }
            if (! Schema::hasColumn('payments', 'trip_bid_id')) {
                $table->unsignedBigInteger('trip_bid_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        // Intentionally preserve reconciliation references during rollback.
    }
};
