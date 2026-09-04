<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->reconcileTripBids();
        $this->reconcileTripRequests();
        $this->reconcileRatings();
        $this->reconcileNotifications();
    }

    private function reconcileTripBids(): void
    {
        if (! Schema::hasTable('trip_bids')) {
            return;
        }

        if (! Schema::hasColumn('trip_bids', 'driver_id') && Schema::hasColumn('trip_bids', 'passenger_id')) {
            Schema::table('trip_bids', function (Blueprint $table) {
                $table->renameColumn('passenger_id', 'driver_id');
            });
        }

        Schema::table('trip_bids', function (Blueprint $table) {
            if (! Schema::hasColumn('trip_bids', 'total_fare')) {
                $table->decimal('total_fare', 8, 2)->default(0);
            }
            if (! Schema::hasColumn('trip_bids', 'seat_price')) {
                $table->decimal('seat_price', 8, 2)->default(0);
            }
            if (! Schema::hasColumn('trip_bids', 'booking_fee')) {
                $table->decimal('booking_fee', 8, 2)->default(0);
            }
            if (! Schema::hasColumn('trip_bids', 'refund_seat_amount')) {
                $table->decimal('refund_seat_amount', 8, 2)->nullable()->default(0);
            }
            if (! Schema::hasColumn('trip_bids', 'refund_booking_fee_amount')) {
                $table->decimal('refund_booking_fee_amount', 8, 2)->nullable()->default(0);
            }
            if (! Schema::hasColumn('trip_bids', 'driver_compensation')) {
                $table->decimal('driver_compensation', 8, 2)->nullable()->default(0);
            }
            if (! Schema::hasColumn('trip_bids', 'is_late_cancellation')) {
                $table->integer('is_late_cancellation')->nullable()->default(0);
            }
            if (! Schema::hasColumn('trip_bids', 'late_review_added')) {
                $table->string('late_review_added', 200)->nullable();
            }
            if (! Schema::hasColumn('trip_bids', 'remaining_refund_paid')) {
                $table->decimal('remaining_refund_paid', 8, 2)->nullable()->default(0);
            }
            if (! Schema::hasColumn('trip_bids', 'cancel_type')) {
                $table->integer('cancel_type')->default(0);
            }
            if (! Schema::hasColumn('trip_bids', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable();
            }
        });

        Schema::table('trip_bids', function (Blueprint $table) {
            $table->integer('status')->default(0)->change();
        });
    }

    private function reconcileTripRequests(): void
    {
        if (! Schema::hasTable('trip_requests')) {
            return;
        }

        Schema::table('trip_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('trip_requests', 'driver_id')) {
                $table->integer('driver_id')->nullable();
            }
            if (! Schema::hasColumn('trip_requests', 'is_verify_spin')) {
                $table->integer('is_verify_spin')->default(0);
            }
            if (! Schema::hasColumn('trip_requests', 'is_verify_epin')) {
                $table->integer('is_verify_epin')->default(0);
            }
            if (! Schema::hasColumn('trip_requests', 'pin_start')) {
                $table->string('pin_start', 10)->nullable();
            }
            if (! Schema::hasColumn('trip_requests', 'pin_end')) {
                $table->string('pin_end', 10)->nullable();
            }
            if (! Schema::hasColumn('trip_requests', 'is_drop_off')) {
                $table->integer('is_drop_off')->default(0);
            }
            if (! Schema::hasColumn('trip_requests', 'payout_status')) {
                $table->integer('payout_status')->default(0);
            }
            if (! Schema::hasColumn('trip_requests', 'refund_status')) {
                $table->integer('refund_status')->default(0);
            }
        });

        Schema::table('trip_requests', function (Blueprint $table) {
            $table->string('message')->nullable()->change();
            $table->integer('status')->default(0)->change();
        });
    }

    private function reconcileRatings(): void
    {
        if (! Schema::hasTable('ratings')) {
            return;
        }

        if (! Schema::hasColumn('ratings', 'user_id') && Schema::hasColumn('ratings', 'reviewer_id')) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->renameColumn('reviewer_id', 'user_id');
            });
        }
        if (! Schema::hasColumn('ratings', 'driver_id') && Schema::hasColumn('ratings', 'reviewee_id')) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->renameColumn('reviewee_id', 'driver_id');
            });
        }
    }

    private function reconcileNotifications(): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        Schema::table('notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('notifications', 'trip_id')) {
                $table->integer('trip_id')->nullable();
            }
            if (! Schema::hasColumn('notifications', 'bid_id')) {
                $table->integer('bid_id')->nullable();
            }
            if (! Schema::hasColumn('notifications', 'ride_id')) {
                $table->integer('ride_id')->nullable();
            }
            if (! Schema::hasColumn('notifications', 'title')) {
                $table->string('title')->nullable();
            }
            if (! Schema::hasColumn('notifications', 'booked_user_id')) {
                $table->integer('booked_user_id')->nullable();
            }
            if (! Schema::hasColumn('notifications', 'ride_trip_type')) {
                $table->integer('ride_trip_type')->default(1);
            }
            if (! Schema::hasColumn('notifications', 'is_my_ride')) {
                $table->integer('is_my_ride')->nullable()->default(0);
            }
            if (! Schema::hasColumn('notifications', 'n_type')) {
                $table->integer('n_type')->default(1);
            }
        });
    }

    public function down(): void
    {
        // Schema reconciliation is intentionally non-destructive on rollback.
    }
};
