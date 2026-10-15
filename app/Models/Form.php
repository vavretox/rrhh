<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Form extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName('Form')
            ->logOnlyDirty();
    }

    protected $fillable = [
        'type_id',
        'setting_id',
        'period',
        'state',
        'slug',
    ];

    public function Type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function Setting(): BelongsTo
    {
        return $this->belongsTo(Setting::class);
    }

    public function Employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'employee_forms')
            ->withPivot('sheet_id', 'worked_days', 'border_bonus', 'iva', 'violations_and_fines', 'judicial_withholdings', 'other_discounts', 'state', 'slug', 'created_at', 'updated_at', 'deleted_at');
    }
}
