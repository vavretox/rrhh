<div class="flex items-center justify-center p-12">
    <div class="mx-auto w-full max-w-[550px] bg-white">
        <div class="text-center text-xl uppercase pb-3 font-bold">Importar personas desde Excel</div>

        <form action="{{ route('person.import') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-5">
                <x-label>Archivo Excel *</x-label>
                <x-input type="file" name="file" accept=".xlsx,.xls,.csv" required />
            </div>

            <div class="items-center justify-center flex">
                <x-button type="submit" class="bg-green-600">Importar</x-button>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <x-button type="button" wire:click="close" class="bg-red-600">Cancelar</x-button>
            </div>
        </form>
    </div>
</div>
