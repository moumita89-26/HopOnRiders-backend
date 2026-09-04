<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'is_suspend')) {
                    $table->integer('is_suspend')->default(0);
                }
                if (! Schema::hasColumn('users', 'is_document_verify')) {
                    $table->integer('is_document_verify')->default(0);
                }
                if (! Schema::hasColumn('users', 'is_email_verify')) {
                    $table->integer('is_email_verify')->default(0);
                }
                if (! Schema::hasColumn('users', 'emergency_number')) {
                    $table->string('emergency_number', 50)->nullable();
                }
                if (! Schema::hasColumn('users', 'emergency_name')) {
                    $table->string('emergency_name', 500)->nullable();
                }
                if (! Schema::hasColumn('users', 'suspended_at')) {
                    $table->timestamp('suspended_at')->nullable();
                }
                if (! Schema::hasColumn('users', 'suspend_reason')) {
                    $table->string('suspend_reason', 250)->nullable();
                }
                if (! Schema::hasColumn('users', 'device_type')) {
                    $table->string('device_type', 10)->nullable();
                }
                if (! Schema::hasColumn('users', 'device_token')) {
                    $table->string('device_token')->nullable();
                }
            });
        }

        if (Schema::hasTable('admin_settings')) {
            Schema::table('admin_settings', function (Blueprint $table) {
                if (! Schema::hasColumn('admin_settings', 'booking_fee')) {
                    $table->integer('booking_fee')->default(0);
                }
                if (! Schema::hasColumn('admin_settings', 'trip_booking_fee')) {
                    $table->integer('trip_booking_fee')->default(0);
                }
                if (! Schema::hasColumn('admin_settings', 'driver_payout_fee')) {
                    $table->integer('driver_payout_fee')->default(0);
                }
            });
        }
    }

    public function down(): void
    {
        // Schema reconciliation is intentionally non-destructive on rollback.
    }
};
