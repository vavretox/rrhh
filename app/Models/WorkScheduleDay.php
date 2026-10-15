<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkScheduleDay extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['is_working_day' => 'boolean']; }
    public function schedule(): BelongsTo { return $this->belongsTo(WorkSchedule::class, 'work_schedule_id'); }
}
