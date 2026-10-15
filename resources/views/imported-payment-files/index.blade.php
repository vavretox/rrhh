<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('ARCHIVOS DESDE EXCEL') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <form method="POST" action="{{ route('imported-payment-files.download') }}" enctype="multipart/form-data"
                    class="space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-label for="planilla">Archivo Excel *</x-label>
                            <x-input id="planilla" name="planilla" type="file" class="mt-1 block w-full"
                                accept=".xlsx,.xls,.csv" required />
                            @error('planilla')
                                <span class="text-red-500 text-xs italic">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <x-label for="tipo_planilla">Tipo de planilla *</x-label>
                            <select id="tipo_planilla" name="tipo_planilla"
                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm mt-1 block w-full"
                                required>
                                <option value="banco" @selected(old('tipo_planilla') === 'banco')>Banco</option>
                                <option value="cheque" @selected(old('tipo_planilla') === 'cheque')>Cheque</option>
                            </select>
                            @error('tipo_planilla')
                                <span class="text-red-500 text-xs italic">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <x-label for="codigo_planilla">Correlativo C31 *</x-label>
                            <x-input id="codigo_planilla" name="codigo_planilla" type="number" min="1"
                                class="mt-1 block w-full" value="{{ old('codigo_planilla') }}" required />
                            @error('codigo_planilla')
                                <span class="text-red-500 text-xs italic">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <x-label for="mes_planilla">Mes de planilla *</x-label>
                            <x-input id="mes_planilla" name="mes_planilla" type="number" min="1" max="12"
                                class="mt-1 block w-full" value="{{ old('mes_planilla') }}" required />
                            @error('mes_planilla')
                                <span class="text-red-500 text-xs italic">{{ $message }}</span>
                            @enderror
                        </div>

                        <div>
                            <x-label for="salario_minimo">Salario minimo nacional</x-label>
                            <x-input id="salario_minimo" name="salario_minimo" type="number" min="0" step="0.01"
                                class="mt-1 block w-full" value="{{ old('salario_minimo', 3300) }}" />
                            @error('salario_minimo')
                                <span class="text-red-500 text-xs italic">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <x-label for="descripcion_mes">Descripcion para glosa *</x-label>
                        <x-input id="descripcion_mes" name="descripcion_mes" type="text" class="mt-1 block w-full"
                            placeholder="Ejemplo: Junio 2025" value="{{ old('descripcion_mes') }}" required />
                        @error('descripcion_mes')
                            <span class="text-red-500 text-xs italic">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-900 rounded-md p-4 text-sm text-gray-700 dark:text-gray-300">
                        <p class="font-semibold mb-2">Formatos aceptados</p>
                        <p class="leading-6 mb-3">
                            Este modulo lee libros oficiales con hojas de sueldo como 01_sld_65-v0, 01_sld_65+v0,
                            01_sld_65-sv0, 01_sld_65+sv0, 50_sld_* y 147_sld_*. Las hojas IVA se omiten.
                        </p>
                        <p class="font-semibold mb-2">Columnas requeridas si usa una hoja normalizada</p>
                        <p class="leading-6">
                            planilla, codigo, cedula, apaterno, amaterno, nivel, haber_basico, dias_trabajados,
                            monto_categoria, total_ganado, faltas_multas, retencion_judicial, otros_descuentos,
                            sexo, nacimiento, afiliado, libreta_militar, codigo_rentista, tipo_relacion,
                            afp_destino, fecha_ingreso, lugar_trabajo, grado_academico, unidad_organizacional,
                            primer_nombre.
                        </p>
                    </div>

                    <div class="flex justify-end">
                        <x-button type="submit" class="bg-green-600">
                            Generar ZIP
                        </x-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
