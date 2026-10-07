<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Crear Nuevo Usuario') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-[#000000] overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-white">

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
                            <select name="role" id="role" required x-model="selectedRole" class="border-gray-300 dark:border-[#444444] focus:border-[#171A20] dark:focus:border-white focus:ring-[#171A20] dark:focus:ring-white rounded-md shadow-sm block mt-1 w-full">
    <option value="">-- Selecciona un rol --</option>
    @foreach ($roles as $role)
        <option value="{{ $role->name }}" @selected(old('role') == $role->name)>
            @if($role->name === 'superadmin') Super Admin
            @elseif($role->name === 'admin') Admin
            @elseif($role->name === 'recepcionista') Recepcionista
            @else {{ ucfirst($role->name) }}
            @endif
        </option>
    @endforeach
</select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <div class="mt-6 border-t pt-4" x-data="{ selectedRole: '{{ old('role', '') }}' }" x-show="selectedRole && selectedRole !== 'admin' && selectedRole !== 'superadmin'">
                             <h3 class="text-md font-medium text-gray-700 dark:text-gray-300 mb-2">Permisos Específicos</h3>
                             <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Selecciona a qué módulos tendrá acceso este rol (los Admins y Super Admins tienen acceso a todo).</p>
                             <div class="grid grid-cols-2 gap-4">
                                @foreach ($permissions as $permissionKey => $permissionLabel)
                                    <label for="permission_{{ $permissionKey }}" class="inline-flex items-center">
                                        <input id="permission_{{ $permissionKey }}" type="checkbox"
                                               class="rounded border-gray-300 dark:border-[#444444] text-[#171A20] dark:text-white underline underline-offset-4 shadow-sm focus:ring-[#171A20] dark:focus:ring-white"
                                               name="permissions[{{ $permissionKey }}]" value="1" {{-- El valor 1 se convierte a true --}}
                                               @checked(old('permissions.'.$permissionKey, false)) >
                                        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ $permissionLabel }}</span>
                                    </label>
                                @endforeach
                             </div>
                              <x-input-error :messages="$errors->get('permissions')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('users.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:text-white me-4">
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






