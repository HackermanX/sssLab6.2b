<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameRequirement extends Model
{
    use HasFactory;
    protected $fillable = [
        'appid',
        'minimum_parsed',
        'recommended_parsed',
        'min_cpu_score',
        'min_gpu_score',
        'min_ram_gb',
    ];
    protected $casts = [
        'minimum_parsed'     => 'array',
        'recommended_parsed' => 'array',
    ]; // otherwise some weird error
}
