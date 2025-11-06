<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Crear Nuevo Usuario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form method="POST" action="{{ route('users.store') }}">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Nombre')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Correo Electrónico')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password" :value="__('Contraseña')" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                         <div class="mt-4">
                            <x-input-label for="role" :value="__('Rol del Usuario')" />
                            <select name="role" id="role" required x-model="selectedRole" {{-- Alpine para mostrar/ocultar permisos --}}
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Selecciona un rol --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}" @selected(old('role') == $role)>
                                        @if($role === 'superadmin') Super Admin
                                        @elseif($role === 'admin') Admin
                                        @elseif($role === 'receptionist') Recepcionista
                                        @else {{ ucfirst($role) }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <div class="mt-6 border-t pt-4" x-data="{ selectedRole: '{{ old('role', '') }}' }" x-show="selectedRole && selectedRole !== 'admin' && selectedRole !== 'superadmin'">
                             <h3 class="text-md font-medium text-gray-700 mb-2">Permisos Específicos</h3>
                             <p class="text-sm text-gray-500 mb-4">Selecciona a qué módulos tendrá acceso este rol (los Admins y Super Admins tienen acceso a todo).</p>
                             <div class="grid grid-cols-2 gap-4">
                                @foreach ($permissions as $permissionKey => $permissionLabel)
                                    <label for="permission_{{ $permissionKey }}" class="inline-flex items-center">
                                        <input id="permission_{{ $permissionKey }}" type="checkbox"
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                               name="permissions[{{ $permissionKey }}]" value="1" {{-- El valor 1 se convierte a true --}}
                                               @checked(old('permissions.'.$permissionKey, false)) >
                                        <span class="ms-2 text-sm text-gray-600">{{ $permissionLabel }}</span>
                                    </label>
                                @endforeach
                             </div>
                              <x-input-error :messages="$errors->get('permissions')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('users.index') }}" class="text-sm text-gray-600 hover:text-gray-900 me-4">
                                {{ __('Cancelar') }}
                            </a>

                            <x-primary-button class="ms-4">
                                {{ __('Crear Usuario') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>