<x-app-layout>
    <x-slot name="header">
        {{ __('Editar Usuario: ') }} {{ $user->name }}
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-[#000000] overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-white">
                    
                    <form method="POST" action="{{ route('users.update', $user->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="name" value="Nombre Completo" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="email" value="Correo Electrónico (Para iniciar sesión)" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" value="{{ old('email', $user->email) }}" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>
                        <div class="mt-6 border-t border-gray-200 dark:border-[#333333] pt-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Cambiar Contraseña (Opcional)</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Deja estos campos en blanco si no deseas cambiar la contraseña actual del usuario.</p>
    
    <div class="mt-4">
        <x-input-label for="password" :value="__('Nueva Contraseña')" />
        <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <div class="mt-4 mb-6">
        <x-input-label for="password_confirmation" :value="__('Confirmar Nueva Contraseña')" />
        <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" />
        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
    </div>
</div>

                        <div class="mb-6">
                            <x-input-label for="role" value="Nivel de Acceso (Rol)" />
                            <select id="role" name="role" class="block mt-1 w-full border-gray-300 dark:border-[#444444] focus:border-[#171A20] dark:focus:border-white focus:ring-[#171A20] dark:focus:ring-white rounded-md shadow-sm" required>
                                <option value="" disabled>Seleccione un rol...</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" 
                                        {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                        {{ strtoupper($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('users.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white mr-4">Cancelar</a>
                            <x-primary-button>
                                {{ __('Guardar Cambios') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>






