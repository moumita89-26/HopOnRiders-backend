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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('unique_id')->unique();
            $table->string('phone')->unique();
            $table->string('password');
            $table->string('social_type')->nullable();
            $table->string('social_id')->nullable();
            $table->string('dob')->nullable();
            $table->integer('role');
            $table->string('profile_picture')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('vehicle_make')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_color')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('number_of_seat')->nullable();
            $table->decimal('fuel_cost_per_km', 8, 2)->nullable();
            $table->boolean('is_online')->default(false);
            $table->string('nrc_no')->nullable();
            $table->string('license_no')->nullable();
            $table->string('nrc_back')->nullable();
            $table->string('nrc_front')->nullable();
            $table->string('license_back')->nullable();
            $table->string('license_front')->nullable();
            $table->string('driver_experience')->nullable();
            $table->string('car_image')->nullable();
            $table->string('amenities')->nullable();
            $table->text('travel_preferences')->nullable();
            $table->integer('ac')->default(0)->nullable();
            $table->integer('luggage')->default(0)->nullable();
            $table->integer('chargin')->default(0)->nullable();
            $table->integer('music')->default(0)->nullable();
            $table->integer('pets')->default(0)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
