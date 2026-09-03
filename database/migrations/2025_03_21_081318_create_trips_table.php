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
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->string('origin');
            $table->string('destination');
            $table->string('des_lat');
            $table->string('des_long');
            $table->string('origin_lat');
            $table->string('origin_long');
            $table->dateTime('departure_time');
            $table->integer('available_seats');
            $table->decimal('fare_per_seat', 8, 2);
            $table->string('message');
            $table->integer('status')->default(1);  //'1 open', '2 booked', '3 completed', '4 cancelled'
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rides');
    }
};
