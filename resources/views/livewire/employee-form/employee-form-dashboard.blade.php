<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
    <p class="text-xl text-center"><b>Funcionarios dentro de la planilla</b><br>{{ $form->Type->name }}
        para {{ \Carbon\Carbon::parse($form->period)->translatedFormat('F \d\e\l Y') }}</p>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
        @if ($form->state != 'consolidado')
            <button type="button"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                wire:click="$dispatch('openModal', { component: 'employee-form.employee-form-create', arguments: { formId: {{ $form->id }} } })">Seleccionar</button>
            &nbsp;&nbsp;
            <button type="button"
                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                wire:click="$dispatch('openModal', { component: 'employee-form.employee-form-import-excel', arguments: { formId: {{ $form->id }} } })">Importar Excel</button>
        @endif

        @livewire('employee-form.employee-form-table', ['formId' => $form->id])

        <div class="flex justify-end pt-3">
            <x-button class="bg-red-600" wire:click="close">Cerrar</x-button>
        </div>
    </div>
</div>
