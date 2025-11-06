<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Punto de Venta (POS)') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="posData()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Mensajes de Éxito/Error --}}
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
            {{-- Mostrar Errores de Validación --}}
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                    <p><strong>Por favor corrige los siguientes errores:</strong></p>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div class="md:col-span-2">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900">
                            {{-- Búsqueda de Productos --}}
                            <form method="GET" action="{{ route('pos.index') }}">
                                <div class="flex">
                                    <x-text-input name="search" type="text" class="w-full" placeholder="Buscar producto por nombre o SKU..." value="{{ request('search') }}" />
                                    <x-primary-button class="ms-2">Buscar</x-primary-button>
                                </div>
                            </form>

                            {{-- Grid de Productos Clickeables --}}
                            <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                                @forelse ($products as $product)
                                    {{-- Botón para añadir al carrito --}}
                                    <button @click="addToCart({{ $product }})"
                                            :disabled="{{ $product->stock }} <= 0"
                                            class="border border-gray-200 rounded-lg p-3 text-center hover:bg-indigo-50 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                                        <div class="font-semibold">{{ $product->name }}</div>
                                        <div class="text-sm text-gray-600">${{ number_format($product->price, 2) }}</div>
                                        <div class="text-xs {{ $product->stock <= 5 ? 'text-red-500 font-bold' : 'text-blue-500' }}">Stock: {{ $product->stock }}</div>
                                    </button>
                                @empty
                                    <p class="text-gray-500 col-span-full">No se encontraron productos activos o con stock.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-1">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg sticky top-6">
                        <div class="p-6 text-gray-900">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Carrito de Venta</h3>

                            {{-- Formulario que envía el carrito y datos de pago --}}
                            <form method="POST" action="{{ route('pos.store') }}" @submit="isProcessing = true; clearCartOnSubmit()"> {{-- Limpiar carrito al enviar con éxito --}}
                                @csrf
                                <div class="space-y-3 min-h-[100px]"> {{-- Altura mínima para que no salte --}}
                                    {{-- Mensaje si el carrito está vacío --}}
                                    <template x-if="cart.length === 0">
                                        <p class="text-gray-500 text-center pt-4">Agrega productos...</p>
                                    </template>

                                    {{-- Itera sobre los items del carrito (manejado por Alpine.js) --}}
                                    <template x-for="(item, index) in cart" :key="item.id">
                                        <div class="flex justify-between items-center border-b pb-2">
                                            {{-- Inputs ocultos para enviar ID y cantidad al backend --}}
                                            <input type="hidden" :name="'cart[' + index + '][id]'" :value="item.id">

                                            {{-- Muestra nombre y precio del item --}}
                                            <div>
                                                <div class="font-medium" x-text="item.name"></div>
                                                <div class="text-sm text-gray-600" x-text="'$' + parseFloat(item.price).toFixed(2)"></div>
                                            </div>
                                            {{-- Controles de cantidad y botón eliminar --}}
                                            <div class="flex items-center">
                                                {{-- Input numérico para la cantidad --}}
                                                <input type="number" :name="'cart[' + index + '][quantity]'"
                                                       x-model.number="item.quantity"
                                                       @input="updateQuantity(item.id, $event.target.value)" {{-- Llama a función JS al cambiar --}}
                                                       class="w-16 text-center border-gray-300 rounded-md shadow-sm text-sm py-1"
                                                       min="1" :max="item.maxStock"> {{-- Limita cantidad al stock --}}

                                                {{-- Botón para eliminar item del carrito --}}
                                                <button type="button" @click.prevent="removeFromCart(item.id)" class="ms-2 text-red-500 hover:text-red-700">
                                                    {{-- Icono SVG de X para eliminar --}}
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                {{-- Sección Método de Pago --}}
                                <div class="mt-4 border-t pt-4">
                                    {{-- Select para Método de Pago --}}
                                    <div>
                                        <x-input-label for="payment_method" :value="__('Método de Pago')" />
                                        <select name="payment_method" id="payment_method" x-model="paymentMethod" required class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="cash">Efectivo</option>
                                            <option value="transfer">Transferencia</option>
                                            <option value="card">Tarjeta (Crédito/Débito)</option>
                                        </select>
                                        <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                                    </div>
                                    {{-- Campo de Referencia (se muestra solo si no es efectivo) --}}
                                    <div x-show="paymentMethod === 'transfer' || paymentMethod === 'card'" x-transition class="mt-4">
                                        <x-input-label for="payment_reference" :value="__('Referencia (Opcional)')" />
                                        <x-text-input id="payment_reference" class="block mt-1 w-full" type="text" name="payment_reference" x-model="paymentReference" placeholder="Ej: #Transf, Últimos 4 dígitos..." />
                                        <x-input-error :messages="$errors->get('payment_reference')" class="mt-2" />
                                    </div>
                                </div>

                                <div class="border-t border-gray-200 mt-4 pt-4">
                                    <div class="flex justify-between items-center text-lg font-bold">
                                        <span>Total:</span>
                                        <span x-text="'$' + cartTotal.toFixed(2)">$0.00</span>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <x-primary-button
                                        type="submit"
                                        class="w-full !justify-center !text-lg !py-3"
                                        ::disabled="cart.length === 0 || isProcessing" {{-- Deshabilitado si carrito vacío o procesando --}}
                                        x-text="isProcessing ? 'Procesando...' : 'Registrar Venta'"> {{-- Texto dinámico --}}
                                        Registrar Venta
                                    </x-primary-button>
                                </div>
                            </form>
                            {{-- Botón Opcional para Limpiar Carrito Manualmente --}}
                            <div class="mt-2 text-center">
                                <button type="button" @click="clearCart()" class="text-sm text-red-600 hover:underline" x-show="cart.length > 0">
                                    Vaciar Carrito
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function posData() {
            return {
                // Carga inicial del carrito desde localStorage o vacío
                cart: JSON.parse(localStorage.getItem('posCart') || '[]'),
                cartTotal: 0.00,
                isProcessing: false,
                paymentMethod: localStorage.getItem('posPaymentMethod') || 'cash', // Cargar método de pago
                paymentReference: localStorage.getItem('posPaymentReference') || '', // Cargar referencia

                // Función para guardar el carrito y datos de pago en localStorage
                saveState() {
                    localStorage.setItem('posCart', JSON.stringify(this.cart));
                    localStorage.setItem('posPaymentMethod', this.paymentMethod);
                    localStorage.setItem('posPaymentReference', this.paymentReference);
                    this.calculateTotal(); // Recalcula total cada vez que se guarda
                },

                addToCart(product) {
                    if (product.stock <= 0) return;
                    let found = this.cart.find(item => item.id === product.id);
                    if (found) {
                        if (found.quantity < product.stock) found.quantity++;
                    } else {
                        this.cart.push({
                            id: product.id, name: product.name, price: parseFloat(product.price),
                            quantity: 1, maxStock: product.stock
                        });
                    }
                    this.saveState(); // Guarda estado
                },
                removeFromCart(id) {
                    this.cart = this.cart.filter(item => item.id !== id);
                    this.saveState(); // Guarda estado
                },
                updateQuantity(id, newQuantity) {
                    let item = this.cart.find(item => item.id === id);
                    if (!item) return;
                    let quantity = parseInt(newQuantity);
                    if (isNaN(quantity) || quantity < 1) item.quantity = 1;
                    else if (quantity > item.maxStock) item.quantity = item.maxStock;
                    else item.quantity = quantity;
                    this.$nextTick(() => {
                         // Actualizar visualmente el input si Alpine corrigió el valor
                         const inputElement = this.$el.querySelector(`input[name='cart[${this.cart.findIndex(i => i.id === id)}][quantity]']`);
                         if(inputElement && parseInt(inputElement.value) !== item.quantity) {
                             inputElement.value = item.quantity;
                         }
                    });
                    this.saveState(); // Guarda estado
                },
                calculateTotal() {
                    this.cartTotal = this.cart.reduce((total, item) => {
                        const quantity = Number(item.quantity) || 0;
                        const price = Number(item.price) || 0;
                        return total + (price * quantity);
                    }, 0);
                },
                // Función para limpiar el carrito y estado de pago
                clearCart() {
                    this.cart = [];
                    this.paymentMethod = 'cash'; // Resetear método
                    this.paymentReference = ''; // Resetear referencia
                    this.saveState(); // Guardar estado vacío
                },
                // Limpia localStorage DESPUÉS de enviar el formulario
                clearCartOnSubmit() {
                    // Esta función se llama en @submit, que ocurre ANTES del redirect.
                    // Limpiamos localStorage aquí para que si el envío falla, no se pierda el carrito.
                    // Si el envío tiene éxito, el redirect recargará la página y leerá el estado vacío.
                    localStorage.removeItem('posCart');
                    localStorage.removeItem('posPaymentMethod');
                    localStorage.removeItem('posPaymentReference');
                    // 'isProcessing' se mantendrá true hasta que la página recargue
                },
                init() {
                    this.isProcessing = false; // Resetear al cargar
                    this.calculateTotal(); // Calcular total inicial
                    // Limpiar referencia si se cambia a efectivo y guardar estado
                    this.$watch('paymentMethod', value => {
                        if (value === 'cash') {
                            this.paymentReference = '';
                        }
                        this.saveState(); // Guardar cambio de método
                    });
                    // Guardar estado si cambia la referencia
                     this.$watch('paymentReference', value => {
                        this.saveState();
                    });
                }
            }
        }
    </script>
</x-app-layout>