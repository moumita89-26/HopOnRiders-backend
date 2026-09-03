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
        Schema::create('cms_email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 250);
            $table->string('slug', 255);
            $table->string('subject', 250)->nullable();
            $table->longText('content')->nullable();
            $table->string('description', 250)->nullable();
            $table->string('from_name', 250)->nullable();
            $table->string('from_email', 250)->nullable();
            $table->string('cc_email', 250)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_email_templates');
    }
};
