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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->string('content');
            $table->json('tags');
            $table->text('description');
            $table->string('image')->nullable();
            $table->json('dependencies');
            $table->json('colors');
            $table->json('color_gradients');
            $table->json('typographies');
            $table->json('custom_typographies');
            $table->json('pages');
            $table->boolean('is_premium')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
