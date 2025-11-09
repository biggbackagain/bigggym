<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{-- Título dinámico según el rango --}}
            @if($startDate->isSameDay($endDate))
                {{ __('Mi Corte del Día') }} ({{ $startDate->format('d/m/Y') }})
            @else
                {{ __('Mi Corte del') }} {{ $startDate->format('d/m/Y') }} {{ __('al') }} {{ $endDate->format('d/m/Y') }}
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
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
                        </form>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900"><h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Ingreso Productos</h3><p class="text-3xl font-bold text-green-600">${{ number_format($totalProductSalesAmount, 2) }}</p></div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900"><h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Ingreso Membresías</h3><p class="text-3xl font-bold text-blue-600">${{ number_format($totalMembershipPaymentsAmount, 2) }}</p></div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900"><h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Neto Mov. Caja</h3><p class="text-3xl font-bold {{ $netCashMovement >= 0 ? 'text-green-600' : 'text-red-600' }}">${{ number_format($netCashMovement, 2) }}</p></div>
                </div>
                 <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-2 border-indigo-600">
                    <div class="p-6 text-gray-900"><h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Mi Saldo Final</h3><p class="text-3xl font-bold text-indigo-700">${{ number_format($grandTotal, 2) }}</p></div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Detalle: Mis Ventas de Productos</h3>
                    <div class="overflow-x-auto">
                         <table class="min-w-full divide-y divide-gray-200">
                            {{-- ... (encabezados: ID/Hora, Productos, Método Pago, Ref., Total) ... --}}
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($productSales as $sale)
                                    <tr>
                                        <td class="px-6 py-4"><div>#P{{ $sale->id }}</div><div>{{ $sale->created_at->format('h:i A') }}</div></td>
                                        <td class="px-6 py-4"><ul>@foreach ($sale->products as $product) <li>({{ $product->pivot->quantity }}x) {{ $product->name }}</li> @endforeach</ul></td>
                                        <td class="px-6 py-4">{{ ucfirst($sale->payment_method) }}</td>
                                        <td class="px-6 py-4">{{ $sale->payment_reference ?? '--' }}</td>
                                        <td class="px-6 py-4">${{ number_format($sale->total_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr> <td colspan="5" class="px-6 py-4 text-center text-gray-500">No registraste ventas de productos en este rango.</td> </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Detalle: Mis Pagos de Membresías</h3>
                     <div class="overflow-x-auto">
                         <table class="min-w-full divide-y divide-gray-200">
                            {{-- ... (encabezados: ID Pago, Hora, Miembro, Plan, Monto) ... --}}
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($membershipPayments as $payment)
                                     <tr>
                                        <td class="px-6 py-4">#M{{ $payment->id }}</td>
                                        <td class="px-6 py-4">{{ $payment->created_at->format('h:i A') }}</td>
                                        <td class="px-6 py-4">{{ $payment->member?->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">{{ $payment->subscription?->membershipType?->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4">${{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                @empty
                                     <tr> <td colspan="5" class="px-6 py-4 text-center text-gray-500">No registraste pagos de membresías en este rango.</td> </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                 <div class="p-6 text-gray-900">
                     <h3 class="text-lg font-medium text-gray-900 mb-4">Detalle: Mis Movimientos de Caja</h3>
                     <div class="overflow-x-auto">
                         <table class="min-w-full divide-y divide-gray-200">
                            {{-- ... (encabezados: Hora, Tipo, Monto, Descripción) ... --}}
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($cashMovements as $movement)
                                    <tr>
                                        <td class="px-6 py-4">{{ $movement->created_at->format('h:i A') }}</td>
                                        <td class="px-6 py-4"><span class="{{ $movement->type == 'entry' ? 'text-green-600' : 'text-red-600' }}">{{ $movement->type == 'entry' ? 'Entrada' : 'Salida' }}</span></td>
                                        <td class="px-6 py-4 {{ $movement->type == 'entry' ? 'text-green-600' : 'text-red-600' }}">${{ number_format($movement->amount, 2) }}</td>
                                        <td class="px-6 py-4">{{ $movement->description }}</td>
                                    </tr>
                                @empty
                                     <tr> <td colspan="4" class="px-6 py-4 text-center text-gray-500">No registraste movimientos de caja en este rango.</td> </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                 </div>
            </div>
        </div>
    </div>
</x-app-layout>