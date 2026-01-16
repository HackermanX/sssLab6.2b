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
        Schema::table('game_requirements', function (Blueprint $table) {
            $table->string('name')->nullable()->after('appid');
            $table->string('slug')->unique()->nullable()->after('name');
            $table->unsignedSmallInteger('year')->nullable()->after('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_requirements', function (Blueprint $table) {
            $table->dropColumn(['name','slug','year']);
        });
    }
};
