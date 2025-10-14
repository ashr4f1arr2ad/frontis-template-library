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
        Schema::table('sites', function (Blueprint $table) {
            $table->text('content')->nullable()->change();
            $table->json('color_gradients')->nullable()->change();
            $table->json('custom_typographies')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->text('content')->nullable(false)->change();
            $table->json('color_gradients')->nullable(false)->change();
            $table->json('custom_typographies')->nullable(false)->change();
        });
    }
};
