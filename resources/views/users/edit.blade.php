<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Usuario') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('users.update', $user->id) }}">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="name" :value="__('Nombre')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Correo Electrónico')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                         <div class="mt-4" x-data="{ selectedRole: '{{ old('role', $user->role) }}' }"> {{-- Alpine para mostrar/ocultar permisos --}}
                            <x-input-label for="role" :value="__('Rol del Usuario')" />
                            <select name="role" id="role" required x-model="selectedRole"
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm {{ $disableRole ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                    {{ $disableRole ? 'disabled' : '' }}> {{-- Deshabilitar si es superadmin editándose --}}
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}" @selected(old('role', $user->role) == $role)>
                                         @if($role === 'superadmin') Super Admin
                                        @elseif($role === 'admin') Admin
                                        @elseif($role === 'receptionist') Recepcionista
                                        @else {{ ucfirst($role) }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @if($disableRole)
                                <p class="mt-1 text-sm text-yellow-600">No puedes cambiar tu propio rol de Super Administrador.</p>
                                {{-- Enviar el rol actual oculto si está deshabilitado --}}
                                <input type="hidden" name="role" value="{{ $user->role }}">
                            @endif
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />

                            <div class="mt-6 border-t pt-4" x-show="selectedRole && selectedRole !== 'admin' && selectedRole !== 'superadmin'">
                                 <h3 class="text-md font-medium text-gray-700 mb-2">Permisos Específicos</h3>
                                 <p class="text-sm text-gray-500 mb-4">Selecciona a qué módulos tendrá acceso este rol.</p>
                                 <div class="grid grid-cols-2 gap-4">
                                    @foreach ($permissions as $permissionKey => $permissionLabel)
                                        <label for="permission_{{ $permissionKey }}" class="inline-flex items-center">
                                            <input id="permission_{{ $permissionKey }}" type="checkbox"
                                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                   name="permissions[{{ $permissionKey }}]" value="1"
                                                   {{-- Marca si el permiso existe y es true en el array de permisos del usuario --}}
                                                   @checked(old('permissions.'.$permissionKey, !empty($user->permissions[$permissionKey]) && $user->permissions[$permissionKey] === true )) >
                                            <span class="ms-2 text-sm text-gray-600">{{ $permissionLabel }}</span>
                                        </label>
                                    @endforeach
                                 </div>
                                  <x-input-error :messages="$errors->get('permissions')" class="mt-2" />
                            </div>
                        </div>


                        <hr class="my-6">

                         <p class="text-sm text-gray-600 mb-4">Deja los campos de contraseña vacíos si no deseas cambiarla.</p>

                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Nueva Contraseña (Opcional)')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('Confirmar Nueva Contraseña')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('users.index') }}" class="text-sm text-gray-600 hover:text-gray-900 me-4">
                                {{ __('Cancelar') }}
                            </a>

                            <x-primary-button class="ms-4">
                                {{ __('Actualizar Usuario') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>