<x-app-layout>
    <x-slot name="header">
        {{-- Usa $settings pasada por el DashboardController --}}
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $settings['gym_name'] ?? 'Dashboard' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

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

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
                {{-- Miembros Activos --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Miembros Activos</h3>
                        <p class="text-3xl font-bold text-green-600">{{ $activeMembersCount }}</p>
                    </div>
                </div>
                {{-- Miembros Vencidos --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Miembros Vencidos</h3>
                        <p class="text-3xl font-bold text-red-600">{{ $inactiveMembersCount }}</p>
                    </div>
                </div>
                {{-- Total de Miembros --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total de Miembros</h3>
                        <p class="text-3xl font-bold text-gray-800">{{ $totalMembersCount }}</p>
                    </div>
                </div>
            </div>

            {{-- Usa $settings pasada por el DashboardController y Storage::url --}}
            @if(isset($settings['gym_main_image']) && $settings['gym_main_image'] && Storage::disk('public')->exists($settings['gym_main_image']))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <img src="{{ Storage::url($settings['gym_main_image']) }}" alt="Imagen Principal del Gimnasio" class="w-full h-auto object-cover" style="max-height: 400px;">
                </div>
            @else
                {{-- Mensaje si no hay imagen --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900 text-center">
                        <p>Bienvenido, {{ Auth::user()->name }}.</p>
                        <p class="mt-2 text-gray-600">Puedes subir una imagen principal en el menú de "Configuración".</p>
                    </div>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Tareas Pendientes</h3>
                    {{-- Formulario nueva tarea --}}
                    <form method="POST" action="{{ route('tasks.store') }}" class="flex gap-2 mb-4">
                        @csrf
                        <x-text-input name="title" class="flex-grow" placeholder="Nueva tarea..." required />
                        <x-primary-button>Agregar</x-primary-button>
                    </form>
                    {{-- Lista de Tareas --}}
                    <div class="space-y-2">
                        @forelse ($tasks as $task)
                            <div class="flex items-center justify-between p-2 rounded {{ $task->is_completed ? 'bg-gray-100' : 'bg-white' }}">
                                {{-- Checkbox completar --}}
                                <form method="POST" action="{{ route('tasks.update', $task) }}">
                                    @csrf @method('PATCH')
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" name="is_completed" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" @checked($task->is_completed) onchange="this.form.submit()">
                                        <span class="ms-2 text-sm {{ $task->is_completed ? 'text-gray-500 line-through' : 'text-gray-700' }}">{{ $task->title }}</span>
                                    </label>
                                </form>
                                {{-- Botón eliminar --}}
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