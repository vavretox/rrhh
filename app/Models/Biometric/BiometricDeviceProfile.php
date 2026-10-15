<?php

namespace App\Models\Biometric;

use Illuminate\Database\Eloquent\Model;

class BiometricDeviceProfile extends Model
{
    protected $fillable = ['manufacturer', 'model', 'protocol', 'default_ports', 'capabilities', 'commands', 'config_parameters', 'description', 'is_active'];

    protected function casts(): array
    {
        return ['default_ports' => 'array', 'capabilities' => 'array', 'commands' => 'array', 'config_parameters' => 'array', 'is_active' => 'boolean'];
    }
}
