<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('trip_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passenger_id')->constrained('users')->onDelete('cascade');
            $table->string('pickup_point');
            $table->string('dropoff_point');
            $table->string('dropoff_lat');
            $table->string('dropoff_long');
            $table->string('pickup_lat');
            $table->string('pickup_long');
            $table->string('message');
            $table->dateTime('requested_date');
            $table->integer('seats_required');
            $table->integer('luggage_count')->default(0);
            $table->integer('cart_type')->default(0);
            $table->integer('ac')->default(0)->nullable();
            $table->integer('luggage')->default(0)->nullable();
            $table->integer('chargin')->default(0)->nullable();
            $table->integer('music')->default(0)->nullable();
            $table->integer('pets')->default(0)->nullable();
            $table->integer('status')->default(1)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_requests');
    }
};
