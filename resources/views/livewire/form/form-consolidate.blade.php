<div class="flex items-center justify-center p-12">
    <div class="mx-auto w-full max-w-[550px] bg-white">
        <div class="text-center text-xl uppercase pb-3 font-bold">Consolidar planilla</div>
        <form>
            {{-- <p>Para consolidar escriba: <b>abc-123-asd</b></p> --}}
            <div class="mb-5">
                <x-label class="text-lg">Para consolidar escriba el siguiente c&oacute;digo: <b>
                        <span
                            class="bg-yellow-200 font-mono text-gray-600 text-xl p-1 rounded-md">{{ $consolidate_code }}</span>
                    </b></x-label>
                <x-input type="text" name="consolidate_input" placeholder="Escriba el codigo de consolidacion"
                    wire:model="consolidate_input" />
                @error('consolidate_input')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="items-center justify-center flex">
                <x-button type="button" class="bg-green-600" wire:click="consolidate"> Consolidar
                </x-button>
                &nbsp;&nbsp;&nbsp;&nbsp;
                <x-button type="button" wire:click="close" class="bg-red-600"> Cancelar </x-button>
            </div>
        </form>
    </div>
</div>
