<x-app-layout>
    <x-slot name="header">
        {{ __('Gestión del Staff y Usuarios') }}
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-4 text-right">
                <a href="{{ route('users.create') }}" class="inline-flex items-center px-4 py-2 bg-[#171A20] dark:bg-white text-white dark:text-[#171A20] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#393C41] dark:hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-[#171A20] dark:focus:ring-white focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                    + Registrar Nuevo Usuario
                </a>
            </div>
            <div class="bg-white dark:bg-[#000000] overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg shadow-sm">
                        {{ session('success') }}
                    </div>
                @endif
                
                @if (session('error'))
                    <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg shadow-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-[#333333] mt-4">
                        <thead class="bg-gray-50 dark:bg-[#111111]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Nombre</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Correo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Roles Asignados</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-[#000000] divide-y divide-gray-200 dark:divide-[#333333]">
                            @foreach ($users as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        @if($user->roles->isNotEmpty())
                                            @foreach($user->roles as $role)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 uppercase">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-[#1A1A1A] text-gray-500 dark:text-gray-400">
                                                Sin rol
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('users.edit', $user->id) }}" class="text-[#171A20] dark:text-white underline underline-offset-4 hover:text-[#393C41] dark:hover:text-gray-300 mr-3 font-bold">Editar</a>
                                        
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario del staff?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 font-bold">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>






