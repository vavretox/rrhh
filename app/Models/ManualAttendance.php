<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualAttendance extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['recorded_at' => 'datetime', 'reviewed_at' => 'datetime']; }
}
