<div class="flex items-center justify-center p-12">
    <div class="mx-auto w-full max-w-[550px] bg-white">
        <div class="text-center text-xl uppercase pb-3 font-bold">Registro de usuario</div>
        <form>
            <div class="mb-5">
                <x-label>Personas *</x-label>
                <x-select name="person_id" wire:model="person_id">
                    <option value="0">Seleccione una persona</option>
                    @foreach ($people as $person)
                        <option value="{{ $person->id }}">{{ $person->first_name }} {{ $person->middle_name }}
                            {{ $person->first_surname }} {{ $person->second_surname }}</option>
                    @endforeach
                </x-select>
                @error('person_id')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Correo *</x-label>
                <x-input type="text" name="email" placeholder="Correo electronico" wire:model="email" />
                @error('email')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Contraseña *</x-label>
                <x-input type="password" name="password" placeholder="Contraseña de usuario" wire:model="password" />
                @error('password')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Repite la contraseña *</x-label>
                <x-input type="password" name="password_confirmation" placeholder="Repetir contraseña"
                    wire:model="password_confirmation" />
                @error('password_confirmation')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Rol del usuario *</x-label>
                <x-select name="role" wire:model="role">
                    <option value="">Seleccione un rol de usuario</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </x-select>
                @error('role')
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
                <x-button type="button" class="bg-green-600" wire:click="save"> Registrar
                </x-button>&nbsp;&nbsp;&nbsp;&nbsp;
                <x-button type="button" wire:click="close" class="bg-red-600"> Cancelar </x-button>
            </div>
        </form>
    </div>
</div>
