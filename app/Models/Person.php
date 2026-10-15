<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Person extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->useLogName('Person')
            ->logOnlyDirty();
    }

    protected $fillable = [
        'pension_fund_manager_id',
        'unique_affiliate_number',
        'identity_card',
        'first_name',
        'middle_name',
        'first_surname',
        'second_surname',
        'married_name',
        'gender',
        'date_of_birth',
        'military_service_booklet_number',
        'social_security_affiliation_code',
        'identification_document_plugin',
        "state",
        "slug",
    ];

    public function firstName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => strtoupper($value),
            set: fn($value) => strtoupper($value)
        );
    }

    public function middleName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => strtoupper($value),
            set: fn($value) => strtoupper($value)
        );
    }

    public function firstSurname(): Attribute
    {
        return Attribute::make(
            get: fn($value) => strtoupper($value),
            set: fn($value) => strtoupper($value)
        );
    }

    public function secondSurname(): Attribute
    {
        return Attribute::make(
            get: fn($value) => strtoupper($value),
            set: fn($value) => strtoupper($value)
        );
    }

    public function marriedName(): Attribute
    {
        return Attribute::make(
            get: fn($value) => strtoupper($value),
            set: fn($value) => strtoupper($value)
        );
    }

    public function identificationDocumentPlugin(): Attribute
    {
        return Attribute::make(
            get: fn($value) => strtoupper($value),
            set: fn($value) => strtoupper($value)
        );
    }

    public function PensionFundManager(): BelongsTo
    {
        return $this->belongsTo(PensionFundManager::class);
    }

    public function Employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }

    public function User(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
