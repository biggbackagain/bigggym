<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Movimientos de Caja') }} ({{ $date->format('d/m/Y') }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-6">
                    <form method="GET" action="{{ route('cash.index') }}">
                        <div class="flex items-center">
                            <x-input-label for="date" :value="__('Seleccionar Fecha:')" class="me-2" />
                            <x-text-input id="date" name="date" type="date" value="{{ $date->format('Y-m-d') }}" />
                            <x-primary-button class="ms-2">Filtrar</x-primary-button>
                            <a href="{{ route('cash.index') }}" class="ms-2 text-sm text-gray-500 hover:underline">Ver Hoy</a>
                        </div>
                    </form>
                </div>
            </div>

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                    <p><strong>Error al registrar:</strong></p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Registrar Nuevo Movimiento</h3>
                    <form method="POST" action="{{ route('cash.store') }}">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end"> {{-- Added items-end --}}
                            <div>
                                <x-input-label for="type" :value="__('Tipo')" />
                                <select name="type" id="type" required class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="entry">Entrada de Efectivo</option>
                                    <option value="exit">Salida de Efectivo</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="amount" :value="__('Monto ($)')" />
                                <x-text-input id="amount" class="block mt-1 w-full" type="number" step="0.01" min="0.01" name="amount" :value="old('amount')" required />
                            </div>
                             <div class="md:col-span-3">
                                <x-input-label for="description" :value="__('Descripción / Motivo')" />
                                <x-text-input id="description" class="block mt-1 w-full" type="text" name="description" :value="old('description')" required />
                            </div>
                            {{-- Botón ahora al final en línea con descripción --}}
                             <div class="md:col-start-3 flex justify-end">
                                <x-primary-button>
                                    {{ __('Registrar Movimiento') }}
                                </x-primary-button>
                             </div>
                        </div>
                        {{-- <div class="flex justify-end mt-4"> --}}
                           {{-- Se movió el botón arriba --}}
                        {{-- </div> --}}
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">
                            Movimientos del Día
                        </h3>
                        <div class="text-right">
                            <span class="text-sm font-medium text-gray-500 uppercase tracking-wider">Saldo del Día:</span>
                            <span class="text-xl font-bold {{ $dailyBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                ${{ number_format($dailyBalance, 2) }}
                            </span>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registrado por</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($movements as $movement)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $movement->created_at->format('h:i A') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($movement->type == 'entry')
                                                <span class="font-semibold text-green-600">Entrada</span>
                                            @else
                                                <span class="font-semibold text-red-600">Salida</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $movement->type == 'entry' ? 'text-green-600' : 'text-red-600' }}">
                                            ${{ number_format($movement->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $movement->description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $movement->user?->name ?? 'Usuario no encontrado' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            No se registraron movimientos en esta fecha.
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