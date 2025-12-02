<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// steam api key 6F58283754B796F6984389D1074B91C6

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fetch_reqs', function (Blueprint $table) {
            $table->id();

            $table->string("CPU");
            $table->string("RAM");
            $table->string("Storage");
            $table->string("GPU");

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fetch_reqs');
    }
};
