<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Sheet extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName('Sheet')
            ->logOnlyDirty();
    }

    protected $fillable = [
        'number',
        'description',
        'state',
        'slug',
    ];

    public function EmployeeForms(): HasMany
    {
        return $this->hasMany(EmployeeForm::class);
    }
}
