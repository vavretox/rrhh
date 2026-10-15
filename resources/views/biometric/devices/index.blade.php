<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div><h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Biometría</h2><p class="mt-1 text-sm text-gray-500">Administración y diagnóstico de dispositivos biométricos.</p></div>
            <button type="button" onclick="document.getElementById('device-form').classList.toggle('hidden')" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Nuevo dispositivo</button>
        </div>
    </x-slot>
    <div class="py-8"><div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
        @if (session('status')) <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div> @endif
        @if ($errors->any()) <div class="rounded-md bg-red-50 p-4 text-sm text-red-800"><ul class="list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
        <div id="device-form" class="{{ $errors->any() ? '' : 'hidden' }} rounded-lg bg-white p-6 shadow dark:bg-gray-800">
            <form method="POST" action="{{ route('biometric.devices.store') }}" class="grid gap-4 md:grid-cols-3">@csrf
                <label class="text-sm dark:text-gray-200">Nombre *<input name="device_name" value="{{ old('device_name') }}" required class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900"></label>
                <label class="text-sm dark:text-gray-200">Dirección IP<input name="device_ip" value="{{ old('device_ip') }}" placeholder="192.168.1.100" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900"></label>
                <label class="text-sm dark:text-gray-200">Puerto *<input type="number" name="device_port" value="{{ old('device_port', 4370) }}" required class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900"></label>
                <label class="text-sm dark:text-gray-200">Fabricante *<select name="manufacturer" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900">@foreach (['ZKSoftware', 'Suprema', 'HID', 'Anviz', 'FingerTec', 'Generic'] as $manufacturer)<option @selected(old('manufacturer') === $manufacturer)>{{ $manufacturer }}</option>@endforeach</select></label>
                <label class="text-sm dark:text-gray-200">Modelo<input name="device_model" value="{{ old('device_model') }}" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900"></label>
                <label class="text-sm dark:text-gray-200">Número de serie<input name="serial_number" value="{{ old('serial_number') }}" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900"></label>
                <label class="text-sm dark:text-gray-200">Ubicación<input name="location" value="{{ old('location') }}" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900"></label>
                <label class="text-sm dark:text-gray-200">Conexión<select name="connection_type" class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900">@foreach (['TCP/IP', 'HTTP', 'WebSocket', 'Serial'] as $type)<option>{{ $type }}</option>@endforeach</select></label>
                <label class="text-sm dark:text-gray-200">Protocolo<input name="protocol" value="{{ old('protocol', 'ZKProtocol') }}" required class="mt-1 w-full rounded-md border-gray-300 dark:bg-gray-900"></label>
                <input type="hidden" name="timezone" value="America/Caracas"><input type="hidden" name="sync_interval" value="60"><input type="hidden" name="connection_timeout" value="5"><input type="hidden" name="is_active" value="1">
                <div class="md:col-span-3 flex justify-end"><button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Guardar dispositivo</button></div>
            </form>
        </div>
        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($devices as $device)
                <article class="rounded-lg bg-white p-5 shadow dark:bg-gray-800">
                    <div class="flex items-start justify-between gap-3"><div><h3 class="font-semibold dark:text-white">{{ $device->device_name }}</h3><p class="text-sm text-gray-500">{{ $device->manufacturer }} · {{ $device->device_model ?: 'Modelo no indicado' }}</p></div>
                    @php $statusClass = match ($device->connection_status) { 'connected' => 'bg-green-100 text-green-800', 'error' => 'bg-red-100 text-red-800', default => 'bg-gray-100 text-gray-700' }; @endphp
                    <span class="rounded-full px-2 py-1 text-xs {{ $statusClass }}">{{ ucfirst($device->connection_status) }}</span></div>
                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm"><div><dt class="text-gray-500">Dirección</dt><dd class="font-medium dark:text-gray-200">{{ $device->device_ip ?: 'Sin IP' }}:{{ $device->device_port }}</dd></div><div><dt class="text-gray-500">Ubicación</dt><dd class="font-medium dark:text-gray-200">{{ $device->location ?: 'Sin ubicación' }}</dd></div><div><dt class="text-gray-500">Última prueba</dt><dd class="font-medium dark:text-gray-200">{{ $device->last_connection_test?->format('d/m/Y H:i') ?: 'Nunca' }}</dd></div><div><dt class="text-gray-500">Registros</dt><dd class="font-medium dark:text-gray-200">{{ $device->communication_logs_count }}</dd></div></dl>
                    @if ($device->connection_error)<p class="mt-3 rounded bg-red-50 p-2 text-xs text-red-700">{{ $device->connection_error }}</p>@endif
                    <div class="mt-5 flex justify-between"><button type="button" data-test-url="{{ route('biometric.devices.test', $device) }}" class="test-device rounded-md border border-indigo-300 px-3 py-2 text-sm text-indigo-700">Probar conexión</button><form method="POST" action="{{ route('biometric.devices.destroy', $device) }}" onsubmit="return confirm('¿Eliminar este dispositivo y sus registros?')">@csrf @method('DELETE')<button class="text-sm text-red-600">Eliminar</button></form></div>
                </article>
            @empty <div class="col-span-full rounded-lg border-2 border-dashed border-gray-300 p-10 text-center text-gray-500">Aún no hay dispositivos biométricos registrados.</div> @endforelse
        </div>{{ $devices->links() }}
    </div></div>
    <script>document.querySelectorAll('.test-device').forEach((button) => button.addEventListener('click', async () => { button.disabled=true; button.textContent='Probando…'; try { const response=await fetch(button.dataset.testUrl,{method:'POST',headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}}); const result=await response.json(); alert(result.message||'Prueba finalizada.'); window.location.reload(); } catch(error) { alert('No fue posible completar la prueba.'); button.disabled=false; button.textContent='Probar conexión'; } }));</script>
</x-app-layout>
