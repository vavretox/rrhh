<div class="flex items-center justify-center p-12">
    <div class="mx-auto w-full max-w-[550px] bg-white">
        <div class="text-center text-xl uppercase pb-3 font-bold">Actualizacion de planilla</div>
        <form>
            <div class="mb-5">
                <x-label>Tipo de planilla *</x-label>
                <x-select name="type_id" wire:model="type_id">
                    <option value="0">Seleccione un tipo de planilla</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </x-select>
                @error('type_id')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Salario minimo nacional *</x-label>
                @if ($setting_state)
                    <x-input type="number" name="setting_name" wire:model="national_minimum_wage" disabled />
                @else
                    <x-input type="number" name="setting_name"
                        placeholder="Verifique su configuracion antes de continuar" class="placeholder-red-600"
                        disabled />
                @endif

                @error('setting_name')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Periodo *</x-label>
                <x-input type="date" name="period" wire:model="period" />
                @error('period')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
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
            </div>
            <div class="items-center justify-center flex">
                @if ($setting_state)
                    <x-button type="button" class="bg-green-600" wire:click="update"> Actualizar
                    </x-button>
                @endif
                &nbsp;&nbsp;&nbsp;&nbsp;
                <x-button type="button" wire:click="close" class="bg-red-600"> Cancelar </x-button>
            </div>
        </form>
    </div>
</div>
