<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GameRequirement extends Model
{
    use HasFactory;
    protected $fillable = [
        'appid',
        'name',
        'slug',
        'year',
        'minimum_parsed',
        'recommended_parsed',
        'min_cpu_score',
        'min_gpu_score',
        'min_ram_gb',
    ];
    protected $casts = [
        'minimum_parsed'     => 'array',
        'recommended_parsed' => 'array',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        if (! $this->slug) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }
}
