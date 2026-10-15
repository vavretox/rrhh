<div class="flex items-center justify-center p-12">
    <div class="mx-auto w-full max-w-[550px] bg-white">
        <div class="text-center text-xl uppercase pb-3 font-bold">Actualizacion de unidad organizacional a empleado</div>
        <form>
            <div class="mb-5">
                <x-label>Unidad organizacional *</x-label>
                <x-select name="organizational_area_id" wire:model="organizational_area_id" disabled>
                    <option value="0">Ninguno</option>
                    @foreach ($organizationalAreas as $organizationalArea)
                        <option value="{{ $organizationalArea->id }}">{{ $organizationalArea->name }}</option>
                    @endforeach
                </x-select>
                @error('organizational_area_id')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            {{-- <div class="mb-5">
                <x-label>Empleado *</x-label>
                <x-select name="employee_id" wire:model="employee_id">
                    <option value="0">Ninguno</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">{{ $employee->person->first_name }}
                            {{ $employee->person->middle_name }} {{ $employee->person->first_surname }}
                            {{ $employee->person->second_surname }}</option>
                    @endforeach
                </x-select>
                @error('employee_id')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div> --}}
            <div class="mb-5">
                <x-label>Fecha de inicio *</x-label>
                <x-input type="date" name="start_date" wire:model="start_date" disabled />
                @error('start_date')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Fecha de fin</x-label>
                <x-input type="date" name="end_date" wire:model="end_date" />
                @error('end_date')
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
                <x-button type="button" class="bg-green-600" wire:click="update"> Actualizar
                </x-button>&nbsp;&nbsp;&nbsp;&nbsp;
                <x-button type="button" wire:click="close" class="bg-red-600"> Cancelar </x-button>
            </div>
        </form>
    </div>
</div>
