<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Backups y Restauración') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 shadow rounded">
                    <p class="font-bold">¡Éxito!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 shadow rounded">
                    <p class="font-bold">Error</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 shadow rounded">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>- {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- SECCIÓN: IMPORTAR BACKUP EXTERNO --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border-2 border-dashed border-gray-300">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Importar Backup Externo</h3>
                    <p class="text-sm text-gray-500 mb-4">
                        Si formateaste la PC o cambiaste de servidor, sube aquí tu archivo <strong>.zip</strong> que descargaste anteriormente para restaurarlo.
                    </p>
                    
                    <form action="{{ route('backups.upload') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-center gap-4">
                        @csrf
                        <input type="file" name="backup_file" accept=".zip" required 
                               class="block w-full text-sm text-gray-500
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-full file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-indigo-50 file:text-indigo-700
                                      hover:file:bg-indigo-100">
                        
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded shadow transition duration-200">
                            Subir Archivo
                        </button>
                    </form>
                </div>
            </div>

            {{-- SECCIÓN: LISTADO Y ACCIONES --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Backups en el Servidor</h3>
                            <p class="text-sm text-gray-500">Lista de puntos de restauración disponibles.</p>
                        </div>
                        <a href="{{ route('backups.create') }}" 
                           onclick="return confirm('¿Crear un respaldo nuevo ahora?');"
                           class="mt-4 sm:mt-0 bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded shadow transition duration-200">
                            + Generar Respaldo Nuevo
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Archivo</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tamaño</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Fecha</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($backups as $backup)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ basename($backup['name']) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $backup['size'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $backup['date']->format('d/m/Y h:i A') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            
                                            <a href="{{ route('backups.download', ['filename' => $backup['name']]) }}" 
                                               class="text-blue-600 hover:text-blue-900 mr-4 font-semibold">
                                                ⬇ Descargar
                                            </a>
                                            
                                            <a href="{{ route('backups.restore', ['filename' => $backup['name']]) }}" 
                                               onclick="return confirm('⚠️ ¡ALERTA DE SEGURIDAD! ⚠️\n\nVas a RESTAURAR el sistema con este archivo.\n\nSe borrarán los datos actuales y se pondrán los de este respaldo.\n\n¿Estás seguro?')"
                                               class="text-orange-600 hover:text-orange-900 mr-4 font-bold">
                                                ↺ Restaurar
                                            </a>

                                            <a href="{{ route('backups.delete', ['filename' => $backup['name']]) }}" 
                                               onclick="return confirm('¿Borrar permanentemente?')"
                                               class="text-red-600 hover:text-red-900 font-semibold">
                                                ✖ Borrar
                                            </a>

                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                            No hay respaldos. Sube uno o crea uno nuevo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>