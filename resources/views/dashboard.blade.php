<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $settings['gym_name'] ?? 'Dashboard' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Mensajes de sesión --}}
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            {{-- SECCIÓN 1: ESTADÍSTICAS DE MIEMBROS --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Miembros Activos</h3>
                        <p class="text-3xl font-bold text-green-600">{{ $activeMembersCount }}</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Miembros Vencidos</h3>
                        <p class="text-3xl font-bold text-red-600">{{ $inactiveMembersCount }}</p>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total de Miembros</h3>
                        <p class="text-3xl font-bold text-gray-800">{{ $totalMembersCount }}</p>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN 2: CORTE DE CAJA DIARIO (NUEVA) --}}
            <div class="mb-6">
                <h3 class="text-lg font-bold text-gray-700 mb-4">💰 Corte de Caja: {{ now()->format('d/m/Y') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded shadow-sm">
                        <p class="text-xs font-bold text-green-600 uppercase">Efectivo</p>
                        <p class="text-xl font-bold text-green-800">${{ number_format($cashToday, 2) }}</p>
                    </div>
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded shadow-sm">
                        <p class="text-xs font-bold text-blue-600 uppercase">Tarjeta</p>
                        <p class="text-xl font-bold text-blue-800">${{ number_format($cardToday, 2) }}</p>
                    </div>
                    <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded shadow-sm">
                        <p class="text-xs font-bold text-purple-600 uppercase">Transferencia</p>
                        <p class="text-xl font-bold text-purple-800">${{ number_format($transferToday, 2) }}</p>
                    </div>
                    <div class="bg-gray-800 p-4 rounded shadow-md">
                        <p class="text-xs font-bold text-gray-400 uppercase">Total Ingresos</p>
                        <p class="text-xl font-bold text-white">${{ number_format($totalToday, 2) }}</p>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN 3: IMAGEN DEL GIMNASIO --}}
            @if(isset($settings['gym_main_image']) && $settings['gym_main_image'] && \Storage::disk('public')->exists($settings['gym_main_image']))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <img src="{{ \Storage::url($settings['gym_main_image']) }}" alt="Imagen Principal del Gimnasio" class="w-full h-auto object-cover" style="max-height: 400px;">
                </div>
            @else
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 text-center">
                        <p>Bienvenido, {{ Auth::user()->name }}.</p>
                        <p class="mt-2 text-gray-600">Puedes subir una imagen principal en el menú de "Configuración".</p>
                    </div>
                </div>
            @endif

            {{-- SECCIÓN 4: TAREAS PENDIENTES --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Tareas Pendientes</h3>
                    <form method="POST" action="{{ route('tasks.store') }}" class="flex gap-2 mb-4">
                        @csrf
                        <x-text-input name="title" class="flex-grow" placeholder="Nueva tarea..." required />
                        <x-primary-button>Agregar</x-primary-button>
                    </form>
                    <div class="space-y-2">
                        @forelse ($tasks as $task)
                            <div class="flex items-center justify-between p-2 rounded {{ $task->is_completed ? 'bg-gray-100' : 'bg-white border' }}">
                                <form method="POST" action="{{ route('tasks.update', $task) }}">
                                    @csrf @method('PATCH')
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_completed" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked($task->is_completed) onchange="this.form.submit()">
                                        <span class="ms-2 text-sm {{ $task->is_completed ? 'text-gray-500 line-through' : 'text-gray-700' }}">{{ $task->title }}</span>
                                    </label>
                                </form>
                                <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('¿Eliminar esta tarea?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Eliminar</button>
                                </form>
                            </div>
                        @empty
                            <p class="text-gray-500">No hay tareas pendientes.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>