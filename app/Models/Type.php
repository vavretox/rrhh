<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Type extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName('Type')
            ->logOnlyDirty();
    }

    protected $fillable = [
        'name',
        'description',
        'state',
        'slug',
    ];

    public function Forms(): HasMany
    {
        return $this->hasMany(Form::class);
    }
}
