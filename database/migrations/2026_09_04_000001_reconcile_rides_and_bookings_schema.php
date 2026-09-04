<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rides')) {
            Schema::table('rides', function (Blueprint $table) {
                if (! Schema::hasColumn('rides', 'total_seats')) {
                    $table->integer('total_seats')->nullable()->default(0);
                }
                if (! Schema::hasColumn('rides', 'kilometer')) {
                    $table->float('kilometer')->nullable()->default(0);
                }
                if (! Schema::hasColumn('rides', 'ride_status')) {
                    $table->string('ride_status', 20)->nullable();
                }
                if (! Schema::hasColumn('rides', 'payout_status')) {
                    $table->integer('payout_status')->default(0);
                }
                if (! Schema::hasColumn('rides', 'refund_status')) {
                    $table->integer('refund_status')->default(0);
                }
            });
        }

        if (Schema::hasTable('bookings')) {
            Schema::table('bookings', function (Blueprint $table) {
                if (! Schema::hasColumn('bookings', 'seat_price')) {
                    $table->decimal('seat_price', 8, 2)->default(0);
                }
                if (! Schema::hasColumn('bookings', 'booking_fee')) {
                    $table->decimal('booking_fee', 8, 2)->default(0);
                }
                if (! Schema::hasColumn('bookings', 'pin_start')) {
                    $table->string('pin_start', 10)->nullable();
                }
                if (! Schema::hasColumn('bookings', 'pin_end')) {
                    $table->string('pin_end', 10)->nullable();
                }
                if (! Schema::hasColumn('bookings', 'is_verify_spin')) {
                    $table->integer('is_verify_spin')->default(0);
                }
                if (! Schema::hasColumn('bookings', 'is_verify_epin')) {
                    $table->integer('is_verify_epin')->default(0);
                }
                if (! Schema::hasColumn('bookings', 'refund_seat_amount')) {
                    $table->decimal('refund_seat_amount', 8, 2)->nullable()->default(0);
                }
                if (! Schema::hasColumn('bookings', 'refund_booking_fee_amount')) {
                    $table->decimal('refund_booking_fee_amount', 8, 2)->nullable()->default(0);
                }
                if (! Schema::hasColumn('bookings', 'driver_compensation')) {
                    $table->decimal('driver_compensation', 8, 2)->nullable()->default(0);
                }
                if (! Schema::hasColumn('bookings', 'is_late_cancellation')) {
                    $table->integer('is_late_cancellation')->nullable()->default(0);
                }
                if (! Schema::hasColumn('bookings', 'late_review_added')) {
                    $table->string('late_review_added', 200)->nullable();
                }
                if (! Schema::hasColumn('bookings', 'remaining_refund_paid')) {
                    $table->decimal('remaining_refund_paid', 8, 2)->nullable()->default(0);
                }
                if (! Schema::hasColumn('bookings', 'cancel_type')) {
                    $table->integer('cancel_type')->default(0);
                }
                if (! Schema::hasColumn('bookings', 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable();
                }
                if (! Schema::hasColumn('bookings', 'is_drop_off')) {
                    $table->integer('is_drop_off')->default(0);
                }
            });

            Schema::table('bookings', function (Blueprint $table) {
                $table->integer('status')->default(1)->change();
            });
        }
    }

    public function down(): void
    {
        // Schema reconciliation is intentionally non-destructive on rollback.
    }
};
