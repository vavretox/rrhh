<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
    <p class="text-xl text-center"><b>Area organizacional del funcionario</b><br>{{ $employee->Person->first_name }}
        {{ $employee->Person->middle_name }} {{ $employee->Person->first_surname }}
        {{ $employee->Person->second_surname }}</p>

    @if ($canAdd)
        <button type="button"
            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
            wire:click="$dispatch('openModal', { component: 'organizational-area-employee.organizational-area-employee-create', arguments: { employee_id: {{ $employee->id }} } })">Agregar</button>
    @else
        <button type="button"
            class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest opacity-60"
            disabled>Agregar</button>
    @endif


    @livewire('organizational-area-employee.organizational-area-employee-table', ['employee_id' => $employee->id])

    <div class="flex justify-end pt-3">
        <x-button class="bg-red-600" wire:click="close">Cerrar</x-button>
    </div>
</div>
