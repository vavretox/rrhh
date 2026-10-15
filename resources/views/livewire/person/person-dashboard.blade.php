<div>
    <header class="bg-white dark:bg-gray-800 shadow">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ __('PERSONAS') }}</h2>
        </div>
    </header>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                @if (session('excel_success'))
                    <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                        {{ session('excel_success') }}
                    </div>
                @endif

                @if (session('excel_errors'))
                    <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
                        <div class="font-semibold">No se pudo importar el archivo:</div>
                        <ul class="mt-2 list-disc pl-5">
                            @foreach (session('excel_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mb-6 flex flex-wrap items-center gap-3">
                    <button type="button"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        wire:click="$dispatch('openModal', { component: 'person.person-create' })">Agregar</button>

                    <a href="{{ route('person.template') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">Descargar plantilla</a>

                    <a href="{{ route('person.export') }}"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Exportar Excel</a>

                    <button type="button"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        wire:click="$dispatch('openModal', { component: 'person.person-import-excel' })">Importar Excel</button>
                </div>

                @livewire('person.person-table')
            </div>
        </div>
    </div>
</div>
