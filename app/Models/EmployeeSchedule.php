<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSchedule extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['starts_on' => 'date', 'ends_on' => 'date']; }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function schedule(): BelongsTo { return $this->belongsTo(WorkSchedule::class, 'work_schedule_id'); }
}
