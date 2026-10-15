<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['holiday_date' => 'date', 'is_recurring' => 'boolean', 'is_active' => 'boolean']; }
}
