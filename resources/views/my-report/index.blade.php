<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight print:hidden">
            {{-- Título dinámico --}}
            @if($startDate->isSameDay($endDate))
                {{ __('Mi Corte del Día') }} ({{ $startDate->format('d/m/Y') }})
            @else
                {{ __('Mi Corte del') }} {{ $startDate->format('d/m/Y') }} {{ __('al') }} {{ $endDate->format('d/m/Y') }}
            @endif
        </h2>
    </x-slot>

    <div class="py-12 print:py-0">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:max-w-full print:px-0">
            
            {{-- ENCABEZADO SOLO PARA IMPRESIÓN --}}
            <div class="hidden print:block text-center mb-6 border-b pb-4">
                <h1 class="text-2xl font-bold uppercase">{{ $globalSettings['gym_name'] ?? 'BIGG GYM' }}</h1>
                <p class="text-lg">CORTE INDIVIDUAL: {{ Auth::user()->name }}</p>
                <p>Fecha: {{ now()->format('d/m/Y h:i A') }}</p>
                <p class="text-sm">Rango: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</p>
            </div>

            {{-- FILTROS (OCULTOS AL IMPRIMIR) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 print:hidden">
                <div class="p-6">
                    <form method="GET" action="{{ route('my.report') }}">
                        <div class="flex flex-wrap items-center gap-2">
                            <div>
                                <x-input-label for="start_date" :value="__('Fecha Inicio:')" class="text-sm" />
                                <x-text-input id="start_date" name="start_date" type="date" value="{{ $startDate->format('Y-m-d') }}" class="text-sm" required />
                            </div>
                           <div>
                                <x-input-label for="end_date" :value="__('Fecha Fin:')" class="text-sm" />
                                <x-text-input id="end_date" name="end_date" type="date" value="{{ $endDate->format('Y-m-d') }}" class="text-sm" required />
                            </div>
                            <div class="self-end">
                                <x-primary-button>Filtrar</x-primary-button>
                                <a href="{{ route('my.report') }}" class="ms-2 text-sm text-gray-500 hover:underline whitespace-nowrap">Ver Hoy</a>
                            </div>
                             @error('end_date') <p class="text-xs text-red-600 col-span-full">{{ $message }}</p> @enderror
                        </div>
                    </form>
                </div>
            </div>

            {{-- RESUMEN FINANCIERO --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6 print:grid-cols-2 print:gap-4">
                {{-- Total a Entregar --}}
                <div class="bg-gray-800 text-white p-6 rounded-lg shadow-lg col-span-1 md:col-span-2 flex flex-col justify-center">
                    <p class="text-xs font-bold uppercase text-gray-400">Total Neto a Entregar</p>
                    <p class="text-4xl font-black">${{ number_format($grandTotal, 2) }}</p>
                    <button onclick="window.print()" class="mt-2 w-fit bg-white text-gray-800 px-3 py-1 rounded text-xs font-bold uppercase print:hidden hover:bg-gray-200">
                        🖨️ Imprimir Corte
                    </button>
                </div>

                {{-- Efectivo (Solo Membresías) --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-green-500 print:shadow-none print:border">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Efectivo (Membresías)</h3>
                        {{-- Usamos la variable methodTotals que pasamos desde el controlador --}}
                        <p class="text-2xl font-bold text-green-600">${{ number_format($methodTotals['Efectivo'] ?? 0, 2) }}</p>
                    </div>
                </div>

                {{-- Digital (Solo Membresías) --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-blue-500 print:shadow-none print:border">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Digital (Membresías)</h3>
                        <p class="text-2xl font-bold text-blue-600">
                            ${{ number_format(($methodTotals['Tarjeta'] ?? 0) + ($methodTotals['Transferencia'] ?? 0), 2) }}
                        </p>
                        <p class="text-xs text-gray-400">Tarjeta + Transferencia</p>
                    </div>
                </div>
            </div>

            {{-- DETALLE: MEMBRESÍAS (Con Referencia) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border print:shadow-none print:mb-4">
                <div class="p-6 print:p-2 text-gray-900">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Detalle: Mis Pagos de Membresías</h3>
                     <div class="overflow-x-auto">
                         <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-bold uppercase text-gray-500">Hora</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold uppercase text-gray-500">Miembro</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold uppercase text-gray-500">Plan</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold uppercase text-gray-500">Método</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold uppercase text-gray-500">Referencia</th> {{-- COLUMNA NUEVA --}}
                                    <th class="px-4 py-2 text-right text-xs font-bold uppercase text-gray-500">Monto</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($membershipPayments as $payment)
                                     <tr>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ $payment->created_at->format('h:i A') }}</td>
                                        <td class="px-4 py-2 text-sm font-bold">{{ $payment->member?->name ?? 'N/A' }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ $payment->subscription?->membershipType?->name ?? 'N/A' }}</td>
                                        
                                        {{-- Método de Pago --}}
                                        <td class="px-4 py-2 text-sm">
                                            <span class="px-2 py-1 rounded text-xs font-bold {{ ($payment->subscription->payment_method ?? '') == 'Efectivo' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ $payment->subscription->payment_method ?? 'Efectivo' }}
                                            </span>
                                        </td>

                                        {{-- Referencia --}}
                                        <td class="px-4 py-2 text-sm font-mono text-gray-600">
                                            {{ $payment->subscription->payment_reference ?? '--' }}
                                        </td>

                                        <td class="px-4 py-2 text-sm font-bold text-right">${{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                @empty
                                     <tr> <td colspan="6" class="px-4 py-4 text-center text-gray-500">No registraste pagos de membresías.</td> </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- DETALLE: PRODUCTOS --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border print:shadow-none print:mb-4">
                <div class="p-6 print:p-2 text-gray-900">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Detalle: Mis Ventas de Productos</h3>
                    <div class="overflow-x-auto">
                         <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-bold uppercase text-gray-500">Hora</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold uppercase text-gray-500">Productos</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold uppercase text-gray-500">Método</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold uppercase text-gray-500">Ref</th>
                                    <th class="px-4 py-2 text-right text-xs font-bold uppercase text-gray-500">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($productSales as $sale)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-500">#P{{ $sale->id }} <br> {{ $sale->created_at->format('h:i A') }}</td>
                                        <td class="px-4 py-2 text-sm">
                                            <ul class="list-disc list-inside">
                                                @foreach ($sale->products as $product) 
                                                    <li>{{ $product->pivot->quantity }}x {{ $product->name }}</li> 
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td class="px-4 py-2 text-sm">{{ ucfirst($sale->payment_method) }}</td>
                                        <td class="px-4 py-2 text-sm font-mono">{{ $sale->payment_reference ?? '--' }}</td>
                                        <td class="px-4 py-2 text-sm font-bold text-right">${{ number_format($sale->total_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr> <td colspan="5" class="px-4 py-4 text-center text-gray-500">No registraste ventas de productos.</td> </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- DETALLE: MOVIMIENTOS DE CAJA --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border print:shadow-none">
                 <div class="p-6 print:p-2 text-gray-900">
                     <h3 class="text-lg font-bold text-gray-900 mb-4 border-b pb-2">Detalle: Mis Movimientos de Caja</h3>
                     <div class="overflow-x-auto">
                         <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-bold uppercase text-gray-500">Hora</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold uppercase text-gray-500">Tipo</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold uppercase text-gray-500">Monto</th>
                                    <th class="px-4 py-2 text-left text-xs font-bold uppercase text-gray-500">Descripción</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($cashMovements as $movement)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ $movement->created_at->format('h:i A') }}</td>
                                        <td class="px-4 py-2 text-sm font-bold {{ $movement->type == 'entry' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $movement->type == 'entry' ? 'Entrada' : 'Salida' }}
                                        </td>
                                        <td class="px-4 py-2 text-sm font-bold {{ $movement->type == 'entry' ? 'text-green-600' : 'text-red-600' }}">
                                            ${{ number_format($movement->amount, 2) }}
                                        </td>
                                        <td class="px-4 py-2 text-sm">{{ $movement->description }}</td>
                                    </tr>
                                @empty
                                     <tr> <td colspan="4" class="px-4 py-4 text-center text-gray-500">No registraste movimientos de caja.</td> </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                 </div>
            </div>

            {{-- BLOQUE DE FIRMAS (SOLO VISIBLE AL IMPRIMIR) --}}
            <div class="hidden print:grid grid-cols-2 gap-10 mt-16 pt-8">
                <div class="text-center">
                    <div class="border-t border-black w-3/4 mx-auto pt-2">
                        <p class="font-bold text-sm uppercase">Firma del Cajero</p>
                        <p class="text-xs text-gray-600">{{ Auth::user()->name }}</p>
                    </div>
                </div>
                <div class="text-center">
                    <div class="border-t border-black w-3/4 mx-auto pt-2">
                        <p class="font-bold text-sm uppercase">Recibido (Administración)</p>
                    </div>
                </div>
            </div>
            
            <div class="hidden print:block text-center mt-8 text-xs text-gray-400">
                <p>Generado por BiggGym System - {{ now()->format('d/m/Y H:i') }}</p>
            </div>

        </div>
    </div>
</x-app-layout>