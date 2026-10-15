<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('AREAS ORGANIZACIONALES') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">

                <button type="button"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    wire:click="$dispatch('openModal', { component: 'organizational-area.organizational-area-create', arguments: { employee_id: {{ $employee->id }} } })">Agregar</button>

                @livewire('organizational-area.organizational-area-table', ['employee_id' => $employee->id])
            </div>
        </div>
    </div>
</x-app-layout>
