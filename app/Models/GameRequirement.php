<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameRequirement extends Model
{
    use HasFactory;
    protected $fillable = ['appid', 'minimum_parsed', 'recommended_parsed'];

    protected $casts = [
        'minimum_parsed'     => 'array',
        'recommended_parsed' => 'array',
    ]; // otherwise some weird error
}
