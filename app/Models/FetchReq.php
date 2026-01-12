<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FetchReq extends Model
{
    use HasFactory;

    protected $table = 'fetch_reqs';

    protected $fillable = [
        'CPU',
        'GPU',
        'RAM',
        'STORAGE',
        'cpu_id',
        'gpu_id',
    ];

    public function cpu()
    {
        return $this->belongsTo(\App\Models\CPUbench::class, 'cpu_id');
    }

    public function gpu()
    {
        return $this->belongsTo(\App\Models\GPUbench::class, 'gpu_id');
    }
}
