<?php

namespace App\Http\Controllers\Biometric;

use App\Http\Controllers\Controller;
use App\Models\Biometric\BiometricDevice;
use App\Services\Biometric\BiometricConnectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BiometricDeviceController extends Controller
{
    public function index(): View
    {
        return view('biometric.devices.index', ['devices' => BiometricDevice::withCount('communicationLogs')->latest()->paginate(12)]);
    }

    public function store(Request $request): RedirectResponse
    {
        BiometricDevice::create($request->validate($this->rules()));
        return back()->with('status', 'Dispositivo biométrico registrado correctamente.');
    }

    public function update(Request $request, BiometricDevice $device): RedirectResponse
    {
        $device->update($request->validate($this->rules($device)));
        return back()->with('status', 'Dispositivo biométrico actualizado correctamente.');
    }

    public function destroy(BiometricDevice $device): RedirectResponse
    {
        $device->delete();
        return back()->with('status', 'Dispositivo biométrico eliminado correctamente.');
    }

    public function test(BiometricDevice $device, BiometricConnectionService $service): JsonResponse
    {
        $success = $service->test($device);
        return response()->json(['success' => $success, 'message' => $success ? 'El puerto del dispositivo respondió correctamente.' : $device->fresh()->connection_error], $success ? 200 : 422);
    }

    public function logs(BiometricDevice $device): JsonResponse
    {
        return response()->json($device->communicationLogs()->latest()->limit(50)->get());
    }

    private function rules(?BiometricDevice $device = null): array
    {
        return [
            'device_name' => ['required', 'string', 'max:255', Rule::unique('biometric_devices')->ignore($device)],
            'device_ip' => ['nullable', 'ip'], 'device_port' => ['required', 'integer', 'between:1,65535'],
            'device_model' => ['nullable', 'string', 'max:255'], 'manufacturer' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255', Rule::unique('biometric_devices')->ignore($device)],
            'mac_address' => ['nullable', 'regex:/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/'],
            'location' => ['nullable', 'string', 'max:255'], 'timezone' => ['required', 'timezone'],
            'is_active' => ['sometimes', 'boolean'], 'auto_sync' => ['sometimes', 'boolean'],
            'sync_interval' => ['required', 'integer', 'between:1,10080'], 'connection_timeout' => ['required', 'integer', 'between:1,30'],
            'connection_type' => ['required', Rule::in(['TCP/IP', 'HTTP', 'WebSocket', 'Serial'])],
            'protocol' => ['required', 'string', 'max:100'], 'api_key' => ['nullable', 'string', 'max:2000'], 'api_secret' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
