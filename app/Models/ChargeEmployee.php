<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ChargeEmployee extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName('ChargeEmployee')
            ->logOnlyDirty();
    }

    protected $fillable = [
        'charge_id',
        'employee_id',
        'start_date',
        'end_date',
        'state',
        'slug',
    ];

    public function Charge(): BelongsTo
    {
        return $this->belongsTo(Charge::class);
    }

    public function Employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
