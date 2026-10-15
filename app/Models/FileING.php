<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FileING extends Model
{
    protected $fillable = [
        'ano',
        'ent',
        'c31',
        'ite',
        'doc',
        'cat',
        'par',
        'tot',
        'nco',
    ];
}
