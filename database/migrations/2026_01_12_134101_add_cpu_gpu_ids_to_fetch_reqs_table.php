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
        Schema::table('fetch_reqs', function (Blueprint $table) {
            if (!Schema::hasColumn('fetch_reqs', 'cpu_id')) {
                $table->foreignId('cpu_id')
                    ->nullable()
                    ->after('GPU')
                    ->constrained('c_p_ubenches')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('fetch_reqs', 'gpu_id')) {
                $table->foreignId('gpu_id')
                    ->nullable()
                    ->after('cpu_id')
                    ->constrained('g_p_ubenches')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fetch_reqs', function (Blueprint $table) {
            if (Schema::hasColumn('fetch_reqs', 'cpu_id')) {
                $table->dropConstrainedForeignId('cpu_id');
            }
            if (Schema::hasColumn('fetch_reqs', 'gpu_id')) {
                $table->dropConstrainedForeignId('gpu_id');
            }
        });
    }
};
