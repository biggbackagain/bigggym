<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Tarifa') }}: {{ $type->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form method="POST" action="{{ route('admin.memberships.update', $type->id) }}">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="name" :value="__('Nombre del Plan')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $type->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="duration_days" :value="__('Duración (Días)')" />
                            <x-text-input id="duration_days" class="block mt-1 w-full" type="number" name="duration_days" :value="old('duration_days', $type->duration_days)" required />
                            <x-input-error :messages="$errors->get('duration_days')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="price_general" :value="__('Precio General ($)')" />
                            <x-text-input id="price_general" class="block mt-1 w-full" type="number" name="price_general" :value="old('price_general', $type->price_general)" step="0.01" required />
                            <x-input-error :messages="$errors->get('price_general')" class="mt-2" />
                        </div>
                        
                        <div class="mt-4">
                            <x-input-label for="price_student" :value="__('Precio Estudiante ($)')" />
                            <x-text-input id="price_student" class="block mt-1 w-full" type="number" name="price_student" :value="old('price_student', $type->price_student)" step="0.01" required />
                            <x-input-error :messages="$errors->get('price_student')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.memberships.index') }}" class="text-gray-600 hover:text-gray-900 me-4">Cancelar</a>
                            <x-primary-button class="ms-4">
                                {{ __('Actualizar Tarifa') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>