<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Charge extends Model
{
    use SoftDeletes;
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName('Charge')
            ->logOnlyDirty();
    }

    protected $fillable = [
        'name',
        'description',
        'code',
        'basic_salary',
        'state',
        'slug',
    ];

    public function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => strtoupper($value),
            set: fn($value) => strtoupper($value)
        );
    }

    public function description(): Attribute
    {
        return Attribute::make(
            get: fn($value) => strtoupper($value),
            set: fn($value) => strtoupper($value)
        );
    }

    public function Employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'charge_employees')
            ->withPivot('start_date', 'end_date', 'state', 'slug', 'created_at', 'updated_at', 'deleted_at');
    }
}
