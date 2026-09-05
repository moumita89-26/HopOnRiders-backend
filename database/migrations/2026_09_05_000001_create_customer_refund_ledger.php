<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->index();
            $table->decimal('amount', 12, 2);
            $table->date('refund_date')->index();
            $table->string('reference', 255);
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('paid_by')->nullable()->index();
            $table->uuid('request_key')->unique();
            $table->timestamps();
        });
        Schema::create('customer_refund_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_refund_id')->constrained('customer_refunds')->restrictOnDelete();
            $table->string('source_type', 20);
            $table->unsignedBigInteger('source_id');
            $table->decimal('amount', 12, 2);
            $table->timestamps();
            $table->index(['source_type', 'source_id'], 'customer_refund_source_index');
        });
        // Snapshot old flags once so subsequent additional entitlements are not
        // accidentally treated as already paid. No journey foreign keys: retain audit history.
        Schema::create('customer_refund_legacy', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 20);
            $table->unsignedBigInteger('source_id');
            $table->decimal('amount', 12, 2)->default(0);
            $table->boolean('needs_review')->default(false);
            $table->timestamps();
            $table->unique(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::table('customer_refunds')->exists()
            || \Illuminate\Support\Facades\DB::table('customer_refund_legacy')->exists()) {
            throw new RuntimeException('Refund history exists. Rollback is blocked to preserve the payment audit trail.');
        }
        Schema::dropIfExists('customer_refund_allocations');
        Schema::dropIfExists('customer_refund_legacy');
        Schema::dropIfExists('customer_refunds');
    }
};
