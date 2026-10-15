<?php

namespace App\Services\Biometric;

use App\Models\Biometric\BiometricCommunicationLog;
use App\Models\Biometric\BiometricDevice;
use Throwable;

class BiometricConnectionService
{
    public function test(BiometricDevice $device): bool
    {
        $startedAt = hrtime(true);
        $socket = null;
        try {
            if (! $device->device_ip) {
                throw new \RuntimeException('El dispositivo no tiene una dirección IP configurada.');
            }
            $socket = @fsockopen($device->device_ip, $device->device_port, $code, $message, max(1, min($device->connection_timeout, 30)));
            if (! is_resource($socket)) {
                throw new \RuntimeException($message ?: "No fue posible abrir el puerto ({$code}).");
            }
            $elapsed = $this->elapsed($startedAt);
            $device->update(['last_connection_test' => now(), 'connection_status' => 'connected', 'connection_error' => null]);
            $this->log($device, 'success', $elapsed);
            return true;
        } catch (Throwable $exception) {
            $elapsed = $this->elapsed($startedAt);
            $device->update(['last_connection_test' => now(), 'connection_status' => 'error', 'connection_error' => $exception->getMessage()]);
            $this->log($device, 'failed', $elapsed, $exception->getMessage());
            return false;
        } finally {
            if (is_resource($socket)) fclose($socket);
        }
    }

    private function elapsed(int $startedAt): int
    {
        return (int) round((hrtime(true) - $startedAt) / 1_000_000);
    }

    private function log(BiometricDevice $device, string $status, int $elapsed, ?string $error = null): void
    {
        BiometricCommunicationLog::create(['device_id' => $device->id, 'command' => 'test_connection', 'status' => $status, 'request_data' => ['ip' => $device->device_ip, 'port' => $device->device_port], 'error_message' => $error, 'response_time_ms' => $elapsed]);
    }
}
