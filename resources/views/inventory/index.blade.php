<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ajuste de Inventario') }}
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                <div class="p-6">
                    <form method="GET" action="{{ route('inventory.index') }}">
                        <div class="flex">
                            <x-text-input name="search" type="text" class="w-full" placeholder="Buscar producto por nombre o SKU..." value="{{ request('search') }}" />
                            <x-primary-button class="ms-2">Buscar</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <form method="POST" action="{{ route('inventory.update') }}">
                @csrf
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class_alias="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                    <th class_alias="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Actual</th>
                                    <th class_alias="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" style="width: 150px;">Ajuste (Sumar/Restar)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($products as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-gray-900">{{ $product->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $product->sku }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $product->stock <= 5 ? 'text-red-600' : 'text-gray-700' }}">
                                            {{ $product->stock }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="hidden" name="adjustments[{{ $loop->index }}][id]" value="{{ $product->id }}">
                                            <x-text-input 
                                                type="number" 
                                                name="adjustments[{{ $loop->index }}][quantity]" 
                                                class="w-full" 
                                                placeholder="0" 
                                                value="0"
                                            />
                                            <p class="text-xs text-gray-400 mt-1">Ej: 10 (suma) o -3 (resta)</p>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            No se encontraron productos.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if($products->isNotEmpty())
                <div class="flex justify-end mt-4">
                    <x-primary-button class="!text-lg !px-6">
                        {{ __('Aplicar Ajustes de Inventario') }}
                    </x-primary-button>
                </div>
                @endif
            </form>

            <div class="mt-4">
                {{ $products->appends(request()->query())->links() }}
            </div>

        </div>
    </div>
</x-app-layout>