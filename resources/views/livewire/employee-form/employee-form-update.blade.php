<div class="flex items-center justify-center p-12">
    <div class="mx-auto w-full max-w-[550px] bg-white">
        <div class="text-center text-xl uppercase pb-3 font-bold">Actualizar funcionario en planilla</div>
        <form>
            <div class="mb-5">
                <x-label>Tipo de calculo *</x-label>
                <x-select name="sheet_id" wire:model="sheet_id">
                    <option value="0">Seleccione una opcion</option>
                    @foreach ($sheets as $sheet)
                        <option value="{{ $sheet->id }}">{{ $sheet->number }} - {{ $sheet->description }}</option>
                    @endforeach
                </x-select>
                @error('sheet_id')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Dias trabajados *</x-label>
                <x-input type="number" step="any" name="worked_days" placeholder="Dias trabajados"
                    wire:model="worked_days" />
                @error('worked_days')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Bono frontera *</x-label>
                <x-input type="number" step="any" name="border_bonus" placeholder="Dias trabajados"
                    wire:model="border_bonus" />
                @error('border_bonus')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>IVA *</x-label>
                <x-input type="number" step="any" name="iva" placeholder="Dias trabajados" wire:model="iva" />
                @error('iva')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Faltas y multas *</x-label>
                <x-input type="number" step="any" name="violations_and_fines" wire:model="violations_and_fines" />
                @error('violations_and_fines')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Retenciones judiciales *</x-label>
                <x-input type="number" step="any" name="judicial_withholdings"
                    wire:model="judicial_withholdings" />
                @error('judicial_withholdings')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Otros descuentos *</x-label>
                <x-input type="number" step="any" name="other_discounts" wire:model="other_discounts" />
                @error('other_discounts')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            {{-- <div class="mb-5">
                <x-label>Estado *</x-label>
                @foreach ($states as $key => $state)
                    <x-label>
                        @if ($key == 0)
                            <input type="radio" name="state" value="{{ $state }}"
                                wire:model="state">&nbsp;&nbsp;{{ strtoupper($state) }}
                        @else
                            <input type="radio" name="state" value="{{ $state }}"
                                wire:model="state">&nbsp;&nbsp;{{ strtoupper($state) }}
                        @endif
                    </x-label>
                @endforeach
            </div> --}}
            <div class="items-center justify-center flex">
                <x-button type="button" class="bg-green-600" wire:click="update"> Actualizar
                </x-button>&nbsp;&nbsp;&nbsp;&nbsp;
                <x-button type="button" wire:click="close" class="bg-red-600"> Cancelar </x-button>
            </div>
        </form>
    </div>
</div>
