<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OrganizationalArea extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName('OrganizationalArea')
            ->logOnlyDirty();
    }

    protected $fillable = [
        'entity_id',
        'relationship',
        'name',
        'description',
        'code',
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

    public function Entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function Employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'organizational_area_employees')
            ->withPivot('start_date', 'end_date', 'state', 'slug', 'created_at', 'updated_at', 'deleted_at');
    }

    public function ChildAreas(): HasMany
    {
        return $this->hasMany(OrganizationalArea::class, 'relationship');
    }

    public function ParentArea(): BelongsTo
    {
        return $this->belongsTo(OrganizationalArea::class, 'relationship');
    }
}
