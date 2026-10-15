<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class EmployeeForm extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName('EmployeeForm')
            ->logOnlyDirty();
    }

    protected $fillable = [
        'employee_id',
        'form_id',
        'sheet_id',
        'worked_days',
        'border_bonus',
        'iva',
        'violations_and_fines',
        'judicial_withholdings',
        'other_discounts',
        'state',
        'slug',
    ];

    public function Employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function Form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function Sheet(): BelongsTo
    {
        return $this->belongsTo(Sheet::class);
    }
}
