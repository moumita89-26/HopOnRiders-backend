<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver_settlements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id')->index();
            $table->decimal('amount', 12, 2);
            $table->string('payment_source', 30)->default('admin_manual')->index();
            $table->string('customer_payment_method', 50)->nullable();
            $table->text('reason');
            $table->string('settlement_reference')->nullable()->index();
            $table->date('settlement_date')->index();
            $table->unsignedBigInteger('paid_by')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('driver_settlement_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_settlement_id')->index();
            $table->string('source_type', 30);
            $table->unsignedBigInteger('source_id');
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            $table->index(['source_type', 'source_id'], 'settlement_allocations_source_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_settlement_allocations');
        Schema::dropIfExists('driver_settlements');
    }
};
