<div class="flex items-center justify-center p-12">
    <div class="mx-auto w-full max-w-[550px] bg-white">
        <div class="text-center text-xl uppercase pb-3 font-bold">Resetear contraseña</div>
        <div class="text-center text-lg pb-3">{{ $user->email }}</div>
        <form>
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
            <div class="items-center justify-center flex">
                <x-button type="button" class="bg-green-600" wire:click="reset_password"> Resetear
                </x-button>&nbsp;&nbsp;&nbsp;&nbsp;
                <x-button type="button" wire:click="close" class="bg-red-600"> Cancelar </x-button>
            </div>
        </form>
    </div>
</div>
