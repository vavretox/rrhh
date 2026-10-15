<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileRDE extends Model
{
    protected $fillable = [
        'ano',
        'ent',
        'c31',
        'acr',
        'dis',
        'chq',
        'tot',
        'ser',
    ];
}
