<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight print:text-black">
            {{-- Título dinámico según el rango --}}
            @if($startDate->isSameDay($endDate))
                {{ __('Reporte de Caja del Día') }} ({{ $startDate->format('d/m/Y') }})
            @else
                {{ __('Reporte de Caja del') }} {{ $startDate->format('d/m/Y') }} {{ __('al') }} {{ $endDate->format('d/m/Y') }}
            @endif
        </h2>
    </x-slot>

    <div class="py-12 print:py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:max-w-full print:px-0">

            {{-- Filtros y Acciones (Ocultos al imprimir) --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 print:hidden">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        <form method="GET" action="{{ route('sales.report') }}" class="flex flex-wrap items-center gap-2">
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
                                <a href="{{ route('sales.report') }}" class="ms-2 text-sm text-gray-500 hover:underline whitespace-nowrap">Ver Hoy</a>
                            </div>
                        </form>

                        <div class="flex gap-2 self-end mt-2 sm:mt-0">
                            <form method="POST" action="{{ route('sales.report.email') }}" onsubmit="return confirm('¿Enviar reporte por correo?');">
                                @csrf
                                <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                                <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-blue-700">
                                    Enviar por Correo
                                </button>
                            </form>

                            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md font-semibold text-xs uppercase tracking-widest hover:bg-gray-700">
                                Imprimir / PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

             {{-- Encabezado Solo Impresión --}}
            <div class="hidden print:block mb-4 text-center">
                <h1 class="text-xl font-bold">Reporte de Caja - {{ $startDate->format('d/m/Y') }} @if(!$startDate->isSameDay($endDate)) al {{ $endDate->format('d/m/Y') }} @endif</h1>
                <p class="text-lg">{{ $globalSettings['gym_name'] ?? config('app.name') }}</p>
            </div>

            {{-- RESUMEN POR MÉTODO DE PAGO (SOLO MEMBRESÍAS) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 print:grid-cols-3 print:mb-4">
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded shadow-sm">
                    <p class="text-xs font-bold text-green-700 uppercase">Efectivo Membresías</p>
                    {{-- Usamos $methodTotals si está disponible, si no, calculamos en la vista --}}
                    <p class="text-xl font-bold text-green-900">${{ number_format($methodTotals['Efectivo'] ?? $membershipPayments->where('subscription.payment_method', 'Efectivo')->sum('amount'), 2) }}</p>
                </div>
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded shadow-sm">
                    <p class="text-xs font-bold text-blue-700 uppercase">Tarjeta Membresías</p>
                    <p class="text-xl font-bold text-blue-900">${{ number_format($methodTotals['Tarjeta'] ?? $membershipPayments->where('subscription.payment_method', 'Tarjeta')->sum('amount'), 2) }}</p>
                </div>
                <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded shadow-sm">
                    <p class="text-xs font-bold text-purple-700 uppercase">Transferencia Membresías</p>
                    <p class="text-xl font-bold text-purple-900">${{ number_format($methodTotals['Transferencia'] ?? $membershipPayments->where('subscription.payment_method', 'Transferencia')->sum('amount'), 2) }}</p>
                </div>
            </div>

            {{-- Tarjetas de Totales Generales --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6 print:grid-cols-4 print:gap-4">
                <div class="bg-white shadow-sm sm:rounded-lg border print:shadow-none">
                     <div class="p-6 print:p-2">
                        <h3 class="text-xs font-medium text-gray-500 uppercase">Ingreso Productos</h3>
                        <p class="text-2xl font-bold text-green-600">${{ number_format($totalProductSalesAmount, 2) }}</p>
                    </div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg border print:shadow-none">
                     <div class="p-6 print:p-2">
                        <h3 class="text-xs font-medium text-gray-500 uppercase">Ingreso Membresías</h3>
                        <p class="text-2xl font-bold text-blue-600">${{ number_format($totalMembershipPaymentsAmount, 2) }}</p>
                    </div>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg border print:shadow-none">
                    <div class="p-6 print:p-2">
                        <h3 class="text-xs font-medium text-gray-500 uppercase">Neto Mov. Caja</h3>
                        <p class="text-2xl font-bold {{ $netCashMovement >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            ${{ number_format($netCashMovement, 2) }}
                        </p>
                    </div>
                </div>
                 <div class="bg-white shadow-sm sm:rounded-lg border-2 border-indigo-600 print:shadow-none">
                    <div class="p-6 print:p-2">
                        <h3 class="text-xs font-medium text-gray-500 uppercase">Saldo Final Caja</h3>
                        <p class="text-2xl font-bold text-indigo-700">${{ number_format($grandTotal, 2) }}</p>
                    </div>
                </div>
            </div>

            {{-- DETALLE VENTAS PRODUCTOS --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border print:shadow-none print:mb-4">
                <div class="p-6 print:p-2">
                    <h3 class="text-lg font-medium mb-4 print:text-base print:mb-2">Detalle: Ventas de Productos</h3>
                    <div class="overflow-x-auto">
                         <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase print:px-1">ID/Hora</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase print:px-1">Productos</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase print:px-1">Método</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase print:px-1">Referencia</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase print:px-1 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($productSales as $sale)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-500 print:px-1">#P{{ $sale->id }} <br> {{ $sale->created_at->format('h:i A') }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 print:px-1">
                                            @foreach ($sale->products as $product)
                                                <div>({{ $product->pivot->quantity }}x) {{ $product->name }}</div>
                                            @endforeach
                                        </td>
                                        <td class="px-6 py-4 text-sm print:px-1">{{ ucfirst($sale->payment_method) }}</td>
                                        <td class="px-6 py-4 text-sm font-mono print:px-1">{{ $sale->payment_reference ?? '--' }}</td>
                                        <td class="px-6 py-4 text-sm font-bold text-right print:px-1">${{ number_format($sale->total_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr> <td colspan="5" class="px-6 py-4 text-center text-gray-500">Sin ventas.</td> </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- DETALLE PAGOS MEMBRESÍAS --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 border print:shadow-none print:mb-4">
                <div class="p-6 print:p-2">
                    <h3 class="text-lg font-medium mb-4 print:text-base print:mb-2">Detalle: Pagos de Membresías</h3>
                     <div class="overflow-x-auto">
                         <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase print:px-1">ID Pago</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase print:px-1">Hora</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase print:px-1">Miembro</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase print:px-1">Plan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase print:px-1">Método</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase print:px-1">Referencia</th> {{-- COLUMNA AGREGADA --}}
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase print:px-1">Monto</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($membershipPayments as $payment)
                                     <tr>
                                        <td class="px-6 py-4 text-sm text-gray-500 print:px-1">#M{{ $payment->id }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500 print:px-1">{{ $payment->created_at->format('h:i A') }}</td>
                                        <td class="px-6 py-4 text-sm print:px-1">
                                            {{ $payment->member?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm print:px-1">
                                            {{ $payment->subscription?->membershipType?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm print:px-1">
                                            {{ ucfirst($payment->subscription?->payment_method ?? 'Efectivo') }}
                                        </td>
                                        
                                        {{-- CELDA REFERENCIA AGREGADA --}}
                                        <td class="px-6 py-4 text-sm font-mono text-blue-700 print:px-1">
                                            {{ $payment->subscription?->payment_reference ?? '--' }}
                                        </td>

                                        <td class="px-6 py-4 text-sm font-bold text-right print:px-1">${{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                @empty
                                     <tr> <td colspan="7" class="px-6 py-4 text-center text-gray-500">Sin pagos.</td> </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- OTROS MOVIMIENTOS --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border print:shadow-none">
                 <div class="p-6 print:p-2">
                     <h3 class="text-lg font-medium mb-4 print:text-base print:mb-2">Otros Movimientos</h3>
                     <div class="overflow-x-auto">
                         <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase print:px-1">Hora</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase print:px-1">Tipo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase print:px-1">Monto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase print:px-1">Descripción</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($cashMovements as $movement)
                                    <tr>
                                        <td class="px-6 py-4 text-sm text-gray-500 print:px-1">{{ $movement->created_at->format('h:i A') }}</td>
                                        <td class="px-6 py-4 text-sm print:px-1 font-bold {{ $movement->type == 'entry' ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $movement->type == 'entry' ? 'Entrada' : 'Salida' }}
                                        </td>
                                        <td class="px-6 py-4 text-sm font-bold print:px-1">${{ number_format($movement->amount, 2) }}</td>
                                        <td class="px-6 py-4 text-sm print:px-1">{{ $movement->description }}</td>
                                    </tr>
                                @empty
                                     <tr> <td colspan="4" class="px-6 py-4 text-center text-gray-500">Sin movimientos.</td> </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                 </div>
            </div>
            
            <div class="hidden print:block text-center mt-8 text-xs text-gray-400">
                Generado por BiggGym System - {{ now()->format('d/m/Y H:i') }}
            </div>

        </div>
    </div>
</x-app-layout>