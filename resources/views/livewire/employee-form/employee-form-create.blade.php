<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
    <p class="text-xl text-center"><b>Lista de funcionarios sin asignar</b></p>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
        @livewire('employee-form.employee-form-adding-table', ['formId' => $formId])
        <div class="flex justify-end pt-3">
            <x-button class="bg-red-600" wire:click="close">Cerrar</x-button>
        </div>
    </div>
</div>
