<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Historial de Ventas') }}
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Buscar Ventas</h3>
                    <form method="GET" action="{{ route('sales.index') }}">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            {{-- Buscar por ID --}}
                            <div>
                                <x-input-label for="search_id" :value="__('Buscar por ID de Venta (ej: 123)')" />
                                <x-text-input id="search_id" class="block mt-1 w-full" type="number" name="search_id" :value="request('search_id')" placeholder="123" />
                            </div>
                            {{-- Fecha Inicio --}}
                            <div>
                                <x-input-label for="start_date" :value="__('Fecha Inicio')" />
                                <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="$startDate->format('Y-m-d')" />
                            </div>
                            {{-- Fecha Fin --}}
                            <div>
                                <x-input-label for="end_date" :value="__('Fecha Fin')" />
                                <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="$endDate->format('Y-m-d')" />
                            </div>
                            {{-- Incluir Canceladas --}}
                            <div class="block mt-6">
                                <label for="include_canceled" class="inline-flex items-center">
                                    <input id="include_canceled" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="include_canceled" value="1" @checked(request('include_canceled') == '1')>
                                    <span class="ms-2 text-sm text-gray-600">{{ __('Incluir canceladas') }}</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex justify-end mt-4">
                            <a href="{{ route('sales.index') }}" class="text-sm text-gray-600 hover:text-gray-900 me-4">Limpiar filtros</a>
                            <x-primary-button>
                                {{ __('Buscar') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Venta / Fecha</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cajero</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Productos</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método/Ref.</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($sales as $sale)
                                    <tr class="{{ $sale->deleted_at ? 'bg-red-50 opacity-60' : '' }}"> {{-- Resaltar si está cancelada --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">ID: #{{ $sale->id }}</div>
                                            <div class="text-sm text-gray-500">{{ $sale->created_at->format('d/m/Y h:i A') }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $sale->user->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <ul class="list-disc list-inside">
                                                @foreach($sale->products as $product)
                                                    <li>{{ $product->pivot->quantity }}x {{ $product->name }}</li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${{ number_format($sale->total_amount, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div>{{ ucfirst($sale->payment_method) }}</div>
                                            <div class="text-xs">{{ $sale->payment_reference ?? '--' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if($sale->deleted_at)
                                                <span class="text-red-600 font-bold">CANCELADA</span>
                                            @else
                                                {{-- Ver Ticket (Reimprimir/Reenviar) --}}
                                                <a href="{{ route('pos.receipt', $sale->id) }}" class="text-indigo-600 hover:text-indigo-900" target="_blank">Ver Ticket</a>
                                                
                                                {{-- Cancelar Venta (Soft Delete) --}}
                                                <form method="POST" action="{{ route('sales.destroy', $sale->id) }}" class="inline-block ms-2" onsubmit="return confirm('¿Estás seguro de CANCELAR esta venta (ID: {{ $sale->id }})? El stock de los productos será devuelto.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                                        Cancelar
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            No se encontraron ventas con esos filtros.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $sales->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>