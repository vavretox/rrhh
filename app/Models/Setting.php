<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Setting extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName('Setting')
            ->logOnlyDirty();
    }

    protected $fillable = [
        'national_minimum_wage',
        'description',
        'start_date',
        'end_date',
        'state',
        'slug',
    ];

    public function Forms(): HasMany
    {
        return $this->hasMany(Form::class);
    }
}
