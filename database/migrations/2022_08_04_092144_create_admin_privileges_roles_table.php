<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminPrivilegesRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_privileges_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_admin_privileges');
            $table->integer('id_admin_menus')->nullable();
            $table->tinyInteger('is_visible')->nullable();
            $table->tinyInteger('is_create')->nullable();
            $table->tinyInteger('is_read')->nullable();
            $table->tinyInteger('is_edit')->nullable();
            $table->tinyInteger('is_delete')->nullable();
            $table->foreignId('created_by');
            $table->foreignId('updated_by')->nullable();
            $table->ipAddress('user_ip')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_privileges_roles');
    }
}
