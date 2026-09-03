<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('driver_settlements')) {
            return;
        }

        Schema::table('driver_settlements', function (Blueprint $table) {
            if (! Schema::hasColumn('driver_settlements', 'payment_source')) {
                $table->string('payment_source', 30)->default('admin_manual')->index();
            }
            if (! Schema::hasColumn('driver_settlements', 'customer_payment_method')) {
                $table->string('customer_payment_method', 50)->nullable();
            }
        });
    }

    public function down(): void
    {
        // Preserve financial audit fields during rollback.
    }
};
