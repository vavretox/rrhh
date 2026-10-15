<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['is_paid' => 'boolean', 'requires_approval' => 'boolean', 'is_active' => 'boolean', 'annual_day_limit' => 'decimal:2']; }
    public function requests(): HasMany { return $this->hasMany(LeaveRequest::class); }
}
