<div class="flex items-center justify-center p-12">
    <div class="mx-auto w-full max-w-[550px] bg-white">
        <div class="text-center text-xl uppercase pb-3 font-bold">Registro de persona</div>
        <form>
            <div class="mb-5">
                <x-label>Administradora de pensiones</x-label>
                <x-select name="pension_fund_manager_id" wire:model="pension_fund_manager_id">
                    <option value="0">Seleccione una administradora de fondo de pensiones</option>
                    @foreach ($pensionFundManagers as $pensionFundManager)
                        <option value="{{ $pensionFundManager->id }}">{{ $pensionFundManager->name }}</option>
                    @endforeach
                </x-select>
                @error('pension_fund_manager_id')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Numero unico de afiliado *</x-label>
                <x-input type="number" name="unique_affiliate_number" placeholder="Numero unico de afiliado"
                    wire:model="unique_affiliate_number" />
                @error('unique_affiliate_number')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Numero de documento de identidad *</x-label>
                <x-input type="number" name="identity_card" placeholder="Numero de documento de identidad"
                    wire:model="identity_card" />
                @error('identity_card')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Primer nombre (NO1) *</x-label>
                <x-input type="text" name="first_name" placeholder="Primer nombre" wire:model="first_name" />
                @error('first_name')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Segundo nombre (NO2) *</x-label>
                <x-input type="text" name="middle_name" placeholder="Segundo nombre" wire:model="middle_name" />
                @error('middle_name')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Apellido paterno (AP1) *</x-label>
                <x-input type="text" name="first_surname" placeholder="Apellido paterno"
                    wire:model="first_surname" />
                @error('first_surname')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Apellido materno (AP2) *</x-label>
                <x-input type="text" name="second_surname" placeholder="Apellido materno"
                    wire:model="second_surname" />
                @error('second_surname')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Apellido de casada (AP3) *</x-label>
                <x-input type="text" name="married_name" placeholder="Apellido de casada"
                    wire:model="married_name" />
                @error('married_name')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Genero (SEX)</x-label>
                <x-select name="gender" wire:model="gender">
                    <option value="0">Seleccione un genero</option>
                    <option value="1">MASCULINO</option>
                    <option value="2">FEMENINO</option>
                </x-select>
                @error('gender')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>A&ntilde;o de nacimiento (NAC) *</x-label>
                <x-input type="date" name="date_of_birth" placeholder="dd/mm/yyyy" wire:model="date_of_birth" />
                @error('date_of_birth')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Numero de libreta de servicio militar (LIB) *</x-label>
                <x-input type="text" name="military_service_booklet_number"
                    placeholder="Numero de libreta de servicio militar" wire:model="military_service_booklet_number" />
                @error('military_service_booklet_number')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Codigo de afiliacion de seguro social (REN) *</x-label>
                <x-input type="text" name="social_security_affiliation_code"
                    placeholder="Codigo de afiliacion de seguro social" wire:model="social_security_affiliation_code" />
                @error('social_security_affiliation_code')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Complemento (NCO) *</x-label>
                <div class="grid grid-cols-12 items-center gap-2">
                    <div class="col-span-8">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="checkNCO" wire:model="checkNCO" class="form-checkbox">
                            <span>El documento de identidad tiene complemento</span>
                        </label>
                    </div>
                    <div class="col-span-4">
                        <x-input type="text" name="identification_document_plugin" placeholder="Ej. 1A"
                            wire:model="identification_document_plugin" maxlength="2" class="w-full" />
                    </div>
                </div>
                @error('identification_document_plugin')
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
