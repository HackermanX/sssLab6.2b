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
        'RAM',
        'STORAGE',
        'GPU',
    ];
}
