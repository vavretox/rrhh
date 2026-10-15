<div class="flex items-center justify-center p-12">
    <div class="mx-auto w-full max-w-[900px] bg-white">
        <div class="text-center text-xl uppercase pb-3 font-bold">Importar datos desde Excel</div>

        <form wire:submit.prevent="import">
            <div class="mb-5">
                <x-label>Libro de planilla *</x-label>
                <x-input type="file" name="planilla" wire:model="planilla" accept=".xlsx,.xls,.csv" />
                @error('planilla')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>

            <div wire:loading wire:target="planilla,import" class="mb-5 text-sm text-gray-600">
                Procesando archivo...
            </div>

            @if ($summary)
                <div class="mb-5 rounded-md bg-green-50 p-3 text-sm text-green-700">
                    {{ $summary }}
                </div>
            @endif

            @if ($foundRows || $notFoundRows || $skippedRows)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div>
                        <div class="font-semibold text-sm mb-2 text-green-700">
                            Encontrados y actualizados ({{ count($foundRows) }})
                        </div>
                        <div class="border rounded-md max-h-72 overflow-auto">
                            <table class="w-full text-xs">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="text-left p-2">Hoja</th>
                                        <th class="text-left p-2">Item</th>
                                        <th class="text-left p-2">CI</th>
                                        <th class="text-left p-2">Nombre</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($foundRows as $row)
                                        <tr class="border-t">
                                            <td class="p-2">{{ $row['sheet'] }}</td>
                                            <td class="p-2">{{ $row['codigo'] }}</td>
                                            <td class="p-2">{{ $row['cedula'] }}</td>
                                            <td class="p-2">{{ $row['nombre'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="p-3 text-gray-500">Sin coincidencias.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <div class="font-semibold text-sm mb-2 text-red-700">
                            No encontrados u omitidos ({{ count($notFoundRows) + count($skippedRows) }})
                        </div>
                        <div class="border rounded-md max-h-72 overflow-auto">
                            <table class="w-full text-xs">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <th class="text-left p-2">Hoja</th>
                                        <th class="text-left p-2">Item</th>
                                        <th class="text-left p-2">CI</th>
                                        <th class="text-left p-2">Nombre</th>
                                        <th class="text-left p-2">Motivo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse (array_merge($notFoundRows, $skippedRows) as $row)
                                        <tr class="border-t">
                                            <td class="p-2">{{ $row['sheet'] }}</td>
                                            <td class="p-2">{{ $row['codigo'] }}</td>
                                            <td class="p-2">{{ $row['cedula'] }}</td>
                                            <td class="p-2">{{ $row['nombre'] }}</td>
                                            <td class="p-2">{{ $row['status'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="p-3 text-gray-500">Sin pendientes.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <div class="items-center justify-center flex">
                <x-button type="submit" class="bg-green-600">Importar</x-button>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <x-button type="button" wire:click="close" class="bg-red-600">Cerrar</x-button>
            </div>
        </form>
    </div>
</div>
