<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clouds', function (Blueprint $table) {
            $table->string('item_type')->after('saved_item_id'); // Add item_type after saved_item_id
        });
    }

    public function down(): void
    {
        Schema::table('clouds', function (Blueprint $table) {
            $table->dropColumn('item_type');
        });
    }
};
