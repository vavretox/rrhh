<div class="flex items-center justify-center p-12">
    <div class="mx-auto w-full max-w-[550px] bg-white">
        <div class="text-center text-xl uppercase pb-3 font-bold">Registro de funcionario</div>
        <form>
            <div class="mb-5">
                <x-label>Personas</x-label>
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
                <x-label>Relacion de dependencia</x-label>
                <x-select name="dependency_relationship_id" wire:model="dependency_relationship_id">
                    <option value="0">Seleccione una relacion de dependencia</option>
                    @foreach ($dependencyRelationships as $dependencyRelationship)
                        <option value="{{ $dependencyRelationship->id }}">{{ $dependencyRelationship->description }}
                        </option>
                    @endforeach
                </x-select>
                @error('dependency_relationship_id')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Categoria *</x-label>
                <x-select name="category_id" wire:model="category_id">
                    <option value="-1">Seleccione una categoria</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->percentage }}
                        </option>
                    @endforeach
                </x-select>
                @error('category_id')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Grado academico *</x-label>
                <x-select name="academic_degree_id" wire:model="academic_degree_id">
                    <option value="-1">Seleccione un grado academico</option>
                    @foreach ($academicDegrees as $academicDegree)
                        <option value="{{ $academicDegree->code }}">{{ $academicDegree->description }}
                        </option>
                    @endforeach
                </x-select>
                @error('academic_degree_id')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Numero unico de item (ITE) *</x-label>
                <x-input type="number" name="item_number" placeholder="Numero de item" wire:model="item_number" />
                @error('item_number')
                    <span class="text-red-500 text-xs italic">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-5">
                <x-label>Fecha de ingreso (ING) *</x-label>
                <x-input type="date" name="date_of_entry" placeholder="dd/mm/yyyy" wire:model="date_of_entry" />
                @error('date_of_entry')
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
