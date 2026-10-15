<div class="flex items-center justify-center p-12">
    <div class="mx-auto w-full max-w-[550px] bg-white">
        <div class="text-center text-xl uppercase pb-3 font-bold">Asignacion de cargo a empleado
        </div>
        <form>
            <div class="mb-5">
                <x-label>Cargos</x-label>
                <x-select name="charge_id" wire:model="charge_id">
                    <option value="0">Seleccione un cargo</option>
                    @foreach ($charges as $charge)
                        <option value="{{ $charge->id }}">{{ $charge->name }} | {{ $charge->basic_salary }}</option>
                    @endforeach
                </x-select>
                @error('charge_id')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            {{-- <div class="mb-5">
                <x-label>Empleados *</x-label>
                <x-select name="employee_id" wire:model="employee_id">
                    <option value="0">Seleccione un empleado</option>
                    @foreach ($employees as $employee)
                        @php
                            $person = App\Models\Person::where('id', $employee->person_id)->first();
                        @endphp
                        <option value="{{ $employee->id }}">
                            {{ $person->first_name }} {{ $person->middle_name }} {{ $person->first_surname }}
                            {{ $person->second_surname }}</option>
                    @endforeach
                </x-select>
                @error('employee_id')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div> --}}
            <div class="mb-5">
                <x-label>Fecha inicio *</x-label>
                <x-input type="date" name="start_date" placeholder="Descripcion del cargo" wire:model="start_date" />
                @error('start_date')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            {{-- <div class="mb-5">
                <x-label>Fecha fin</x-label>
                <x-input type="date" name="end_date" placeholder="Descripcion del cargo" wire:model="end_date" />
                @error('end_date')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div> --}}
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
                <x-button type="button" class="bg-green-600" wire:click="save"> Registrar
                </x-button>&nbsp;&nbsp;&nbsp;&nbsp;
                <x-button type="button" wire:click="close" class="bg-red-600"> Cancelar </x-button>
            </div>
        </form>
    </div>
</div>
