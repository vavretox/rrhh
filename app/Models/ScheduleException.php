<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleException extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['exception_date' => 'date', 'is_working_day' => 'boolean']; }
}
