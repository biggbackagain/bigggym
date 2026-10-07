<x-app-layout>
    <x-slot name="header">
        <h2 class="text-[20px] font-medium text-[#171A20] dark:text-white tracking-tight">
            {{ $settings['gym_name'] ?? 'Dashboard' }}
        </h2>
    </x-slot>

    <div class="py-12 bg-white dark:bg-[#000000] min-h-screen">
        <div class="max-w-[1383px] mx-auto px-6">

            {{-- Mensajes de sesión --}}
            @if (session('success'))
                <div class="mb-8 p-4 bg-[#F4F4F4] dark:bg-[#111111] border-l-4 border-[#3E6AE1] text-[#171A20] dark:text-white dark:text-[#171A20] text-[14px]">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-8 p-4 bg-[#F4F4F4] dark:bg-[#111111] border-l-4 border-[#ED4E50] text-[#171A20] dark:text-white dark:text-[#171A20] text-[14px]">
                    {{ session('error') }}
                </div>
            @endif

            {{-- SECCIÓN 1: ESTADÍSTICAS DE MIEMBROS (Minimalista) --}}
            <div class="mb-16">
                <h3 class="text-[14px] font-medium text-[#171A20] dark:text-white mb-6 tracking-tight">Visión General</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 border-t border-b border-[#EEEEEE] dark:border-[#333333] py-8">
                    <div class="flex flex-col">
                        <span class="text-[12px] font-medium text-[#8E8E8E] uppercase tracking-[1px] mb-2">Miembros Activos</span>
                        <span class="text-[48px] font-medium text-[#171A20] dark:text-white leading-none">{{ $activeMembersCount }}</span>
                    </div>
                    <div class="flex flex-col sm:border-l sm:border-[#EEEEEE] dark:border-[#333333] sm:pl-8">
                        <span class="text-[12px] font-medium text-[#8E8E8E] uppercase tracking-[1px] mb-2">Miembros Vencidos</span>
                        <span class="text-[48px] font-medium text-[#ED4E50] dark:text-[#F87171] leading-none">{{ $inactiveMembersCount }}</span>
                    </div>
                    <div class="flex flex-col sm:border-l sm:border-[#EEEEEE] dark:border-[#333333] sm:pl-8">
                        <span class="text-[12px] font-medium text-[#8E8E8E] uppercase tracking-[1px] mb-2">Total de Miembros</span>
                        <span class="text-[48px] font-medium text-[#171A20] dark:text-white leading-none">{{ $totalMembersCount }}</span>
                    </div>
                </div>
            </div>

            {{-- SECCIÓN 2: CORTE DE CAJA DIARIO (Tesla Finance Style) --}}
            <div class="mb-16">
                <div class="flex items-baseline justify-between mb-6">
                    <h3 class="text-[14px] font-medium text-[#171A20] dark:text-white tracking-tight">Corte de Caja</h3>
                    <span class="text-[12px] text-[#5C5E62] dark:text-gray-400 dark:text-[#A0A0A0]">{{ now()->format('d / m / Y') }}</span>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <div class="bg-[#F4F4F4] dark:bg-[#111111] p-6 rounded-[4px] flex flex-col justify-between h-[120px]">
                        <span class="text-[12px] font-medium text-[#5C5E62] dark:text-gray-400 dark:text-[#A0A0A0] uppercase tracking-[1px]">Efectivo</span>
                        <span class="text-[24px] font-medium text-[#171A20] dark:text-white">${{ number_format($cashToday, 2) }}</span>
                    </div>
                    <div class="bg-[#F4F4F4] dark:bg-[#111111] p-6 rounded-[4px] flex flex-col justify-between h-[120px]">
                        <span class="text-[12px] font-medium text-[#5C5E62] dark:text-gray-400 dark:text-[#A0A0A0] uppercase tracking-[1px]">Tarjeta</span>
                        <span class="text-[24px] font-medium text-[#171A20] dark:text-white">${{ number_format($cardToday, 2) }}</span>
                    </div>
                    <div class="bg-[#F4F4F4] dark:bg-[#111111] p-6 rounded-[4px] flex flex-col justify-between h-[120px]">
                        <span class="text-[12px] font-medium text-[#5C5E62] dark:text-gray-400 dark:text-[#A0A0A0] uppercase tracking-[1px]">Transferencia</span>
                        <span class="text-[24px] font-medium text-[#171A20] dark:text-white">${{ number_format($transferToday, 2) }}</span>
                    </div>
                    <div class="bg-[#171A20] dark:bg-[#222222] p-6 rounded-[4px] flex flex-col justify-between h-[120px]">
                        <span class="text-[12px] font-medium text-[#EEEEEE] uppercase tracking-[1px]">Total Ingresos</span>
                        <span class="text-[24px] font-medium text-white">${{ number_format($totalToday, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
                {{-- SECCIÓN 3: PRÓXIMOS VENCIMIENTOS --}}
                <div>
                    <h3 class="text-[14px] font-medium text-[#171A20] dark:text-white mb-6 tracking-tight">Próximos Vencimientos (7 días)</h3>
                    
                    @if($expiringSubscriptions->count() > 0)
                        <div class="flex flex-col gap-2">
                            @foreach($expiringSubscriptions as $sub)
                                <div class="flex items-center justify-between p-4 rounded-[4px] bg-[#F4F4F4] dark:bg-[#111111] transition-colors">
                                    <div class="flex flex-col">
                                        <span class="text-[14px] font-medium text-[#171A20] dark:text-white">{{ $sub->member->name }}</span>
                                        <span class="text-[12px] text-[#5C5E62] dark:text-gray-400 dark:text-[#A0A0A0] mt-1">{{ $sub->member->member_code }}</span>
                                    </div>
                                    <div class="text-right flex flex-col">
                                        <span class="text-[14px] font-medium {{ \Carbon\Carbon::parse($sub->end_date)->isToday() ? 'text-[#ED4E50] dark:text-[#F87171]' : 'text-[#171A20] dark:text-white' }}">
                                            {{ \Carbon\Carbon::parse($sub->end_date)->isToday() ? 'Vence HOY' : \Carbon\Carbon::parse($sub->end_date)->format('d / m / Y') }}
                                        </span>
                                        <span class="text-[12px] text-[#8E8E8E] mt-1">{{ $sub->plan_name }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-[#F4F4F4] dark:bg-[#111111] rounded-[4px] p-8 aspect-[4/3] flex flex-col items-center justify-center text-center">
                            <span class="text-[20px] font-medium text-[#137333] mb-2">Todo al día</span>
                            <p class="text-[14px] text-[#5C5E62] dark:text-gray-400 dark:text-[#A0A0A0] max-w-xs">Ninguna membresía expira en los próximos 7 días. El negocio fluye con normalidad.</p>
                        </div>
                    @endif
                </div>

                {{-- SECCIÓN 4: TAREAS PENDIENTES --}}
                <div>
                    <h3 class="text-[14px] font-medium text-[#171A20] dark:text-white mb-6 tracking-tight">Tareas Pendientes</h3>
                    <form method="POST" action="{{ route('tasks.store') }}" class="flex gap-3 mb-6">
                        @csrf
                        <input type="text" name="title" class="flex-grow bg-[#F4F4F4] dark:bg-[#111111] border-transparent focus:bg-white dark:focus:bg-[#000000] focus:border-[#3E6AE1] focus:ring-1 focus:ring-[#3E6AE1] text-[14px] text-[#171A20] dark:text-white rounded-[4px] px-4 py-2 placeholder-[#8E8E8E] transition-colors h-[40px]" placeholder="Nueva tarea..." required>
                        <button type="submit" class="h-[40px] px-6 bg-[#171A20] dark:bg-white hover:bg-[#393C41] dark:hover:bg-gray-200 text-white dark:text-[#171A20] text-[14px] font-medium rounded-[4px] transition-colors">
                            Agregar
                        </button>
                    </form>
                    
                    <div class="flex flex-col gap-2">
                        @forelse ($tasks as $task)
                            <div class="flex items-center justify-between p-4 rounded-[4px] transition-colors {{ $task->is_completed ? 'bg-white dark:bg-[#000000] opacity-60' : 'bg-[#F4F4F4] dark:bg-[#111111]' }}">
                                <form method="POST" action="{{ route('tasks.update', $task) }}" class="w-full flex items-center">
                                    @csrf @method('PATCH')
                                    <label class="flex items-center cursor-pointer w-full">
                                        <input type="checkbox" name="is_completed" value="1" class="w-4 h-4 rounded-[2px] border-[#EEEEEE] dark:border-[#333333] text-[#3E6AE1] shadow-none focus:ring-[#3E6AE1] bg-white dark:bg-[#000000] transition-colors" @checked($task->is_completed) onchange="this.form.submit()">
                                        <span class="ms-4 text-[14px] {{ $task->is_completed ? 'text-[#8E8E8E] line-through' : 'text-[#171A20] dark:text-white' }}">{{ $task->title }}</span>
                                    </label>
                                </form>
                                <form method="POST" action="{{ route('tasks.destroy', $task) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-[#8E8E8E] hover:text-[#ED4E50] dark:text-[#F87171] text-[13px] ml-4 transition-colors">
                                        Eliminar
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="p-4 text-[14px] text-[#5C5E62] dark:text-gray-400 dark:text-[#A0A0A0]">
                                No hay tareas pendientes.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>








