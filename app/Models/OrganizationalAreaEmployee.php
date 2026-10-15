<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OrganizationalAreaEmployee extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName('OrganizationalAreaEmployee')
            ->logOnlyDirty();
    }

    protected $fillable = [
        'organizational_area_id',
        'employee_id',
        'start_date',
        'end_date',
        'state',
        'slug',
    ];

    public function OrganizationalArea(): BelongsTo
    {
        return $this->belongsTo(OrganizationalArea::class);
    }

    public function Employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
