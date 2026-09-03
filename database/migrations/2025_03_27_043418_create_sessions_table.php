<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id', 255)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index('sessions_user_id_index');
            $table->string('ip_address', 45)->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable();
            $table->text('user_agent')->charset('utf8mb4')->collation('utf8mb4_unicode_ci')->nullable();
            $table->longText('payload')->charset('utf8mb4')->collation('utf8mb4_unicode_ci');
            $table->integer('last_activity')->index('sessions_last_activity_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sessions');
    }
};
