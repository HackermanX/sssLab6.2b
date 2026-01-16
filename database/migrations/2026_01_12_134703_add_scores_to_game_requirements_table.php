<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_requirements', function (Blueprint $table) {
            // minimum scores
            if (! Schema::hasColumn('game_requirements', 'min_cpu_score')) {
                $table->unsignedInteger('min_cpu_score')->nullable()->after('minimum_parsed');
            }
            if (! Schema::hasColumn('game_requirements', 'min_gpu_score')) {
                $table->unsignedInteger('min_gpu_score')->nullable()->after('min_cpu_score');
            }
            if (! Schema::hasColumn('game_requirements', 'min_ram_gb')) {
                $table->unsignedInteger('min_ram_gb')->nullable()->after('min_gpu_score');
            }
            if (! Schema::hasColumn('game_requirements', 'min_storage_gb')) {
                $table->unsignedInteger('min_storage_gb')->nullable()->after('min_ram_gb');
            }

            // recommended scores
            if (! Schema::hasColumn('game_requirements', 'rec_cpu_score')) {
                $table->unsignedInteger('rec_cpu_score')->nullable()->after('min_storage_gb');
            }
            if (! Schema::hasColumn('game_requirements', 'rec_gpu_score')) {
                $table->unsignedInteger('rec_gpu_score')->nullable()->after('rec_cpu_score');
            }
            if (! Schema::hasColumn('game_requirements', 'rec_ram_gb')) {
                $table->unsignedInteger('rec_ram_gb')->nullable()->after('rec_gpu_score');
            }
            if (! Schema::hasColumn('game_requirements', 'rec_storage_gb')) {
                $table->unsignedInteger('rec_storage_gb')->nullable()->after('rec_ram_gb');
            }
        });
    }

    public function down(): void
    {
        Schema::table('game_requirements', function (Blueprint $table) {
            if (Schema::hasColumn('game_requirements', 'min_cpu_score')) {
                $table->dropColumn('min_cpu_score');
            }
            if (Schema::hasColumn('game_requirements', 'min_gpu_score')) {
                $table->dropColumn('min_gpu_score');
            }
            if (Schema::hasColumn('game_requirements', 'min_ram_gb')) {
                $table->dropColumn('min_ram_gb');
            }
            if (Schema::hasColumn('game_requirements', 'min_storage_gb')) {
                $table->dropColumn('min_storage_gb');
            }
            if (Schema::hasColumn('game_requirements', 'rec_cpu_score')) {
                $table->dropColumn('rec_cpu_score');
            }
            if (Schema::hasColumn('game_requirements', 'rec_gpu_score')) {
                $table->dropColumn('rec_gpu_score');
            }
            if (Schema::hasColumn('game_requirements', 'rec_ram_gb')) {
                $table->dropColumn('rec_ram_gb');
            }
            if (Schema::hasColumn('game_requirements', 'rec_storage_gb')) {
                $table->dropColumn('rec_storage_gb');
            }
        });
    }
};