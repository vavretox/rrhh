<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRule extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['round_clock_times' => 'boolean', 'is_active' => 'boolean']; }
    public function schedule(): BelongsTo { return $this->belongsTo(WorkSchedule::class, 'work_schedule_id'); }
}
