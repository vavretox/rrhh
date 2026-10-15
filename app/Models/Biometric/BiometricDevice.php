<?php

namespace App\Models\Biometric;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BiometricDevice extends Model
{
    protected $fillable = ['device_name', 'device_ip', 'device_port', 'device_model', 'manufacturer', 'serial_number', 'mac_address', 'location', 'timezone', 'is_active', 'auto_sync', 'sync_interval', 'max_connections', 'connection_timeout', 'connection_type', 'protocol', 'api_key', 'api_secret', 'additional_config', 'capabilities', 'last_sync', 'last_connection_test', 'connection_status', 'connection_error'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'auto_sync' => 'boolean', 'additional_config' => 'array', 'capabilities' => 'array', 'last_sync' => 'datetime', 'last_connection_test' => 'datetime', 'api_key' => 'encrypted', 'api_secret' => 'encrypted'];
    }

    public function communicationLogs(): HasMany
    {
        return $this->hasMany(BiometricCommunicationLog::class, 'device_id');
    }
}
