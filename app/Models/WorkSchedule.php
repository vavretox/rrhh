<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkSchedule extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['is_default' => 'boolean', 'is_active' => 'boolean']; }
    public function days(): HasMany { return $this->hasMany(WorkScheduleDay::class); }
    public function rules(): HasMany { return $this->hasMany(AttendanceRule::class); }
    public function assignments(): HasMany { return $this->hasMany(EmployeeSchedule::class); }
}
