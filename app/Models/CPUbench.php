<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CPUbench extends Model
{
    use HasFactory;
    protected $table    = 'c_p_ubenches';
    protected $fillable = ['name', 'score'];
}
