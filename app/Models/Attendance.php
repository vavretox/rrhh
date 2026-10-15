<?php
namespace App\Models;

use App\Models\Biometric\BiometricDevice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['work_date' => 'date', 'recorded_at' => 'datetime', 'raw_payload' => 'array']; }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function device(): BelongsTo { return $this->belongsTo(BiometricDevice::class, 'biometric_device_id'); }
}
