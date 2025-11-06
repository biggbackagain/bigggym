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

    <div class="py-12 print:py-4"> {{-- Reducir padding en impresión --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 print:max-w-full print:px-0">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4 print:hidden"> {{-- Ocultar en impresión --}}
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                        {{-- Filtro de Rango de Fechas --}}
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
                             @error('end_date')
                                <p class="text-xs text-red-600 col-span-full">{{ $message }}</p>
                            @enderror
                            @error('start_date')
                                <p class="text-xs text-red-600 col-span-full">{{ $message }}</p>
                             @enderror
                        </form>

                        <div class="flex gap-2 self-end mt-2 sm:mt-0"> {{-- Contenedor para botones --}}
                            {{-- Botón Enviar por Correo --}}
                            <form method="POST" action="{{ route('sales.report.email') }}" onsubmit="return confirm('¿Enviar este reporte por correo a {{ $globalSettings['report_recipient_email'] ?? 'la dirección configurada' }}?');">
                                @csrf
                                <input type="hidden" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
                                <input type="hidden" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-50 transition ease-in-out duration-150"
                                    @empty($globalSettings['report_recipient_email'])
                                        disabled
                                        title="Configura un correo destino en Ajustes para habilitar esta opción."
                                    @endempty>
                                    <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">...</svg>
                                    Enviar por Correo
                                </button>
                            </form>

                            {{-- Botón Imprimir --}}
                            <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">...</svg>
                                Imprimir / PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>

             {{-- Título Visible Solo en Impresión --}}
            <div class="hidden print:block mb-4 text-center">
                <h1 class="text-xl font-bold">
                     @if($startDate->isSameDay($endDate))
                        Reporte de Caja - {{ $startDate->format('d/m/Y') }}
                    @else
                        Reporte de Caja - {{ $startDate->format('d/m/Y') }} al {{ $endDate->format('d/m/Y') }}
                    @endif
                </h1>
                @isset($globalSettings['gym_name'])
                 <p class="text-lg">{{ $globalSettings['gym_name'] }}</p>
                @endisset
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6 print:grid-cols-4 print:gap-4">
                {{-- Total Productos --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg print:border print:border-gray-300 print:shadow-none">
                     <div class="p-6 print:p-2 text-gray-900">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider print:text-xs">Ingreso Productos</h3>
                        <p class="text-3xl font-bold text-green-600 print:text-xl">${{ number_format($totalProductSalesAmount, 2) }}</p>
                        <p class="text-xs text-gray-500 print:text-xxs">({{ $totalProductSalesCount }} ventas)</p>
                    </div>
                </div>
                 {{-- Total Membresías --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg print:border print:border-gray-300 print:shadow-none">
                     <div class="p-6 print:p-2 text-gray-900">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider print:text-xs">Ingreso Membresías</h3>
                        <p class="text-3xl font-bold text-blue-600 print:text-xl">${{ number_format($totalMembershipPaymentsAmount, 2) }}</p>
                         <p class="text-xs text-gray-500 print:text-xxs">({{ $totalMembershipPaymentsCount }} pagos)</p>
                    </div>
                </div>
                {{-- Neto Movimientos Caja --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg print:border print:border-gray-300 print:shadow-none">
                    <div class="p-6 print:p-2 text-gray-900">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider print:text-xs">Neto Mov. Caja</h3>
                        <p class="text-3xl font-bold {{ $netCashMovement >= 0 ? 'text-green-600' : 'text-red-600' }} print:text-xl">
                            ${{ number_format($netCashMovement, 2) }}
                        </p>
                        <p class="text-xs text-gray-500 print:text-xxs">
                           (+) ${{ number_format($totalCashEntries, 2) }} / (-) ${{ number_format($totalCashExits, 2) }}
                        </p>
                    </div>
                </div>
                {{-- TOTAL GENERAL DE CAJA --}}
                 <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-2 border-indigo-600 print:border print:border-gray-300 print:shadow-none">
                    <div class="p-6 print:p-2 text-gray-900">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider print:text-xs">Saldo Final en Caja</h3>
                        <p class="text-3xl font-bold text-indigo-700 print:text-xl">${{ number_format($grandTotal, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 print:shadow-none print:border print:border-gray-300 print:mb-4">
                <div class="p-6 print:p-2 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 print:text-base print:mb-2">
                        Detalle: Ventas de Productos
                    </h3>
                    <div class="overflow-x-auto">
                         <table class="min-w-full divide-y divide-gray-200 print:divide-none">
                            <thead class="bg-gray-50 print:bg-transparent">
                                <tr>
                                    {{-- Encabezados con Método Pago y Referencia --}}
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-1 print:py-1">ID/Hora</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-1 print:py-1">Productos</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-1 print:py-1">Método Pago</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-1 print:py-1">Referencia</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-1 print:py-1">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 print:divide-none">
                                @forelse ($productSales as $sale)
                                    <tr class="print:border-b print:border-gray-200">
                                        {{-- ID y Hora --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 print:px-1 print:py-1">
                                            <div>#P{{ $sale->id }}</div>
                                            <div>{{ $sale->created_at->format('h:i A') }}</div>
                                        </td>
                                        {{-- Productos --}}
                                        <td class="px-6 py-4 text-sm text-gray-900 print:px-1 print:py-1 print:whitespace-normal">
                                            <ul class="list-none print:pl-0">
                                                @foreach ($sale->products as $product)
                                                    <li>({{ $product->pivot->quantity }}x) {{ $product->name }} <span class="text-xs text-gray-500">(${{ number_format($product->pivot->price_at_sale, 2) }} c/u)</span></li>
                                                @endforeach
                                            </ul>
                                        </td>
                                         {{-- Método Pago y Referencia --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 print:px-1 print:py-1">
                                            {{ ucfirst($sale->payment_method) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 print:px-1 print:py-1">
                                            {{ $sale->payment_reference ?? '--' }}
                                        </td>
                                        {{-- Total --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 print:px-1 print:py-1">${{ number_format($sale->total_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr> <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500 print:px-1 print:py-1">No se registraron ventas de productos.</td> </tr> {{-- colspan="5" --}}
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 print:shadow-none print:border print:border-gray-300 print:mb-4">
                <div class="p-6 print:p-2 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4 print:text-base print:mb-2">
                        Detalle: Pagos de Membresías
                    </h3>
                     <div class="overflow-x-auto">
                         <table class="min-w-full divide-y divide-gray-200 print:divide-none">
                            <thead class="bg-gray-50 print:bg-transparent">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-1 print:py-1">ID Pago</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-1 print:py-1">Hora</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-1 print:py-1">Miembro</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-1 print:py-1">Plan</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-1 print:py-1">Monto</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 print:divide-none">
                                @forelse ($membershipPayments as $payment)
                                     <tr class="print:border-b print:border-gray-200">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 print:px-1 print:py-1">#M{{ $payment->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 print:px-1 print:py-1">{{ $payment->created_at->format('h:i A') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 print:px-1 print:py-1">
                                            {{ $payment->member?->name ?? 'N/A' }} <span class="text-xs text-gray-500">({{ $payment->member?->member_code ?? 'N/A' }})</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 print:px-1 print:py-1">
                                            {{ $payment->subscription?->membershipType?->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 print:px-1 print:py-1">${{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                @empty
                                     <tr> <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500 print:px-1 print:py-1">No se registraron pagos de membresías.</td> </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg print:shadow-none print:border print:border-gray-300">
                 <div class="p-6 print:p-2 text-gray-900">
                     <h3 class="text-lg font-medium text-gray-900 mb-4 print:text-base print:mb-2">
                        Detalle: Otros Movimientos de Caja
                    </h3>
                     <div class="overflow-x-auto">
                         <table class="min-w-full divide-y divide-gray-200 print:divide-none">
                            <thead class="bg-gray-50 print:bg-transparent">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-1 print:py-1">Hora</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-1 print:py-1">Tipo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-1 print:py-1">Monto</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-1 print:py-1">Descripción</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:px-1 print:py-1">Registrado por</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 print:divide-none">
                                @forelse ($cashMovements as $movement)
                                    <tr class="print:border-b print:border-gray-200">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 print:px-1 print:py-1">{{ $movement->created_at->format('h:i A') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm print:px-1 print:py-1">
                                            @if($movement->type == 'entry')
                                                <span class="font-semibold text-green-600">Entrada</span>
                                            @else
                                                <span class="font-semibold text-red-600">Salida</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $movement->type == 'entry' ? 'text-green-600' : 'text-red-600' }} print:px-1 print:py-1">
                                            ${{ number_format($movement->amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 print:px-1 print:py-1">{{ $movement->description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 print:px-1 print:py-1">{{ $movement->user?->name ?? 'N/A' }}</td>
                                    </tr>
                                @empty
                                     <tr> <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500 print:px-1 print:py-1">No se registraron otros movimientos de caja.</td> </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                 </div>
            </div>

        </div>
    </div>
</x-app-layout>