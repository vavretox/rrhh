<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Employee extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName('Employee')
            ->logOnlyDirty();
    }

    protected $fillable = [
        'person_id',
        'dependency_relationship_id',
        'category_id',
        'academic_degree_id',
        'item_number',
        'date_of_entry',
        "state",
        "slug",
    ];

    public function DependencyRelationship(): BelongsTo
    {
        return $this->belongsTo(DependencyRelationship::class);
    }

    public function Person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function AcademicDegree(): BelongsTo
    {
        return $this->belongsTo(AcademicDegree::class);
    }

    public function OrganizationalAreas(): BelongsToMany
    {
        return $this->belongsToMany(OrganizationalArea::class, 'organizational_area_employees')
            ->withPivot('start_date', 'end_date', 'state', 'slug', 'created_at', 'updated_at', 'deleted_at');
    }

    public function Charges(): BelongsToMany
    {
        return $this->belongsToMany(Charge::class, 'charge_employees')
            ->withPivot('start_date', 'end_date', 'state', 'slug', 'created_at', 'updated_at', 'deleted_at');
    }

    public function Category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function Forms(): BelongsToMany
    {
        return $this->belongsToMany(Form::class, 'employee_forms')
            ->withPivot('sheet_id', 'worked_days', 'border_bonus', 'iva', 'violations_and_fines', 'judicial_withholdings', 'other_discounts', 'state', 'slug', 'created_at', 'updated_at', 'deleted_at');
    }
}
