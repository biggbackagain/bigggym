<x-app-layout>
    <x-slot name="header">
        {{ __('Gestión de Productos') }}
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-[#000000] overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-white">
                    
                    <div class="flex justify-between items-center mb-4">
                        <form method="GET" action="{{ route('products.index') }}">
                            <div class="flex">
                                <x-text-input name="search" type="text" placeholder="Buscar por nombre o SKU..." value="{{ request('search') }}" />
                                <x-primary-button class="ms-2">Buscar</x-primary-button>
                            </div>
                        </form>

                        <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 bg-[#171A20] dark:bg-white text-white dark:text-[#171A20] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#393C41] dark:hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-[#171A20] dark:focus:ring-white focus:ring-offset-2 transition ease-in-out duration-150">
                            Nuevo Producto
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-[#333333]">
                            <thead class="bg-gray-50 dark:bg-[#111111]">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">SKU</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Precio</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Inventario</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-[#000000] divide-y divide-gray-200 dark:divide-[#333333]">
                                @forelse ($products as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900 dark:text-white">{{ $product->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $product->sku ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">${{ number_format($product->price, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $product->stock <= 5 ? 'text-red-600' : 'text-gray-700 dark:text-gray-300' }}">
                                            {{ $product->stock }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($product->is_active)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 dark:bg-green-900/30 text-green-800">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 dark:bg-[#1A1A1A] text-gray-800 dark:text-gray-200">
                                                    Inactivo
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('products.edit', $product->id) }}" class="text-[#171A20] dark:text-white underline underline-offset-4 hover:text-[#393C41] dark:hover:text-gray-300">Editar</a>
                                            <form method="POST" action="{{ route('products.destroy', $product->id) }}" class="inline-block ms-2" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este producto?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400">
                                            No se encontraron productos.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $products->appends(request()->query())->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>






