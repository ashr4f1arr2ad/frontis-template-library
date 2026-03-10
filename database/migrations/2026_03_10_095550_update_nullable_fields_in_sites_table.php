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
            $table->text('description')->nullable()->change();
            $table->json('colors')->nullable()->change();
            $table->json('dependencies')->nullable()->change();
            $table->json('typographies')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->text('description')->nullable(false)->change();
            $table->json('colors')->nullable(false)->change();
            $table->json('dependencies')->nullable(false)->change();
            $table->json('typographies')->nullable()->change();
        });
    }
};
