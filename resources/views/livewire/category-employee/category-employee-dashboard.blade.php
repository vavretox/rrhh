<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <span class="text-xl"><b>Categoria del empleado</b><br>{{ $employee->Person->first_name }}
            {{ $employee->Person->middle_name }} {{ $employee->Person->first_surname }}
            <b>{{ $canCreate }}</b></span>
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg pt-6 pb-3">
            @if ($canCreate)
                <button type="button"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    wire:click="$dispatch('openModal', { component: 'employee-category.employee-category-create' })">Agregar</button>
            @else
                <button type="button"
                    class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest opacity-60"
                    disabled>Agregar</button>
            @endif

            @livewire('category-employee.category-employee-table')

        </div>
        <div class="flex justify-end">
            <x-button class="bg-red-600 mt-6" wire:click="close">Cerrar</x-button>
        </div>
    </div>
</div>
