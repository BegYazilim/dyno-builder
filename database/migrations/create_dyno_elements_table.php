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
        Schema::create('theme_elements', function (Blueprint $table) {
            $table->id();
            $table->string('element_key')->index();
            $table->string('element_type')->index();
            $table->string('page_id')->nullable()->index();
            $table->json('data')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();

            // Unique index
            $table->unique(['element_key', 'page_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theme_elements');
    }
};
