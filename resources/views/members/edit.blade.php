<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Miembro') }}: {{ $member->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('members.update', $member->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        @if ($member->profile_photo_path)
                            <div class="mb-4">
                                <img src="{{ Storage::url($member->profile_photo_path) }}" alt="Foto de perfil" class="w-24 h-24 rounded-full object-cover">
                            </div>
                        @endif

                        <div>
                             <x-input-label for="name" :value="__('Nombre Completo')" />
                             <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $member->name)" required autofocus />
                             <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                             <x-input-label for="phone" :value="__('Teléfono')" />
                             <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone', $member->phone)" />
                             <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                             <x-input-label for="email" :value="__('Email (Opcional)')" />
                             <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $member->email)" />
                             <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                             <x-input-label for="profile_photo" :value="__('Cambiar Foto de Perfil (Opcional)')" />
                             <input id="profile_photo" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="file" name="profile_photo" />
                             <x-input-error :messages="$errors->get('profile_photo')" class="mt-2" />
                        </div>

                        <div class="block mt-4">
                            <label for="is_student" class="inline-flex items-center">
                                <input id="is_student" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_student" value="1" @checked(old('is_student', $member->is_student))>
                                <span class="ms-2 text-sm text-gray-600">{{ __('¿Es estudiante? (Para descuento)') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-6 pt-4 border-t border-gray-200">
                            {{-- Renew Button --}}
                             <a href="{{ route('members.renew', $member->id) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Renovar Suscripción
                            </a>

                            {{-- Existing Buttons --}}
                            <a href="{{ route('members.index') }}" class="text-gray-600 hover:text-gray-900 ms-4 me-4">Cancelar Edición</a>
                            <x-primary-button class="ms-auto"> {{-- ms-auto pushes to the right --}}
                                {{ __('Actualizar Datos Miembro') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>