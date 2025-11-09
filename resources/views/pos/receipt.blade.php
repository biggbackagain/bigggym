<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Venta #{{ $sale->id }}</title>
    
    {{-- Cargar el CSS de la app (Tailwind) --}}
    @vite(['resources/css/app.css'])

    <style>
        /* Estilos específicos para la impresión de ticket */
        @media print {
            /* Ocultar la barra de navegación, botones y formulario de email */
            .no-print {
                display: none !important;
            }
            /* Resetear márgenes y padding de la página */
            body, html {
                margin: 0;
                padding: 0;
                background-color: #fff;
                font-family: 'Courier New', Courier, monospace; /* Fuente de ticket */
                font-size: 10pt; /* Tamaño pequeño */
            }
            /* Definir el ancho del ticket */
            .ticket-container {
                width: 80mm; /* Ancho estándar de ticket */
                margin: 0;
                padding: 5px;
                box-shadow: none;
                border: none;
            }
            /* Asegurar que el fondo blanco se imprima */
            @page {
                size: 80mm auto; /* Ancho 80mm, altura automática */
                margin: 0;
            }
        }
    </style>
</head>
<body class="bg-gray-100">

    {{-- BARRA DE NAVEGACIÓN (Solo visible en pantalla) --}}
    <div class="no-print">
        @include('layouts.navigation')
    </div>

    {{-- CONTENEDOR PRINCIPAL --}}
    <div class="ticket-container max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl my-8 p-8 print:my-0 print:p-2 print:shadow-none">
        
        {{-- Mensajes de Éxito/Error (para el envío de email) --}}
        @if (session('success'))
            <div class="no-print mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="no-print mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        {{-- INICIO DEL TICKET --}}
        <div class="text-center print:text-xs">
            <h1 class="text-xl font-bold">{{ $globalSettings['gym_name'] ?? 'Gimnasio' }}</h1>
            <p>Comprobante de Venta</p>
            <p class="text-sm">Fecha: {{ $sale->created_at->format('d/m/Y h:i A') }}</p>
            <p class="text-sm">Venta ID: #{{ $sale->id }}</p>
            <p class="text-sm">Cajero: {{ $sale->user->name ?? Auth::user()->name }}</p>
        </div>

        <hr class="my-4 border-dashed">

        {{-- Items --}}
        <div class="print:text-xs">
            <table class="w-full text-left">
                <thead>
                    <tr>
                        <th class="font-bold">Prod.</th>
                        <th class="font-bold text-center">Cant.</th>
                        <th class="font-bold text-right">Precio</th>
                        <th class="font-bold text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->products as $product)
                    <tr>
                        <td>{{ $product->name }}</td>
                        <td class="text-center">{{ $product->pivot->quantity }}</td>
                        <td class="text-right">${{ number_format($product->pivot->price_at_sale, 2) }}</td>
                        <td class="text-right">${{ number_format($product->pivot->price_at_sale * $product->pivot->quantity, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <hr class="my-4 border-dashed">

        {{-- Totales --}}
        <div class="space-y-1 text-right font-bold print:text-sm">
            <div class="text-xl">
                <span>TOTAL:</span>
                <span>${{ number_format($sale->total_amount, 2) }}</span>
            </div>
             <div class="text-sm text-gray-600 font-normal">
                <span>Método de Pago:</span>
                <span>
                    @if($sale->payment_method === 'cash') Efectivo
                    @elseif($sale->payment_method === 'transfer') Transferencia
                    @elseif($sale->payment_method === 'card') Crédito/Débito
                    @endif
                </span>
            </div>
            @if($sale->payment_reference)
            <div class="text-sm text-gray-600 font-normal">
                <span>Referencia:</span>
                <span>{{ $sale->payment_reference }}</span>
            </div>
            @endif
        </div>

        <div class="text-center mt-6 text-sm print:text-xs">
            <p>¡Gracias por tu compra!</p>
        </div>
        {{-- FIN DEL TICKET --}}

    </div>

    {{-- BOTONES Y FORMULARIO (Solo visible en pantalla) --}}
    <div class="no-print max-w-md mx-auto sm:px-6 lg:px-8 space-y-4">
        
        {{-- Botón Imprimir --}}
        <button onclick="window.print()" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Imprimir Ticket
        </button>

        {{-- Formulario Email --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <form method="POST" action="{{ route('pos.receipt.email', $sale->id) }}">
                @csrf
                <x-input-label for="email" :value="__('O enviar comprobante por correo:')" />
                <div class="flex mt-1 gap-2">
                    <x-text-input id="email" class="block w-full" type="email" name="email" :value="old('email')" placeholder="correo@ejemplo.com" required />
                    <x-primary-button>
                        {{ __('Enviar') }}
                    </x-primary-button>
                </div>
                 <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </form>
        </div>

        {{-- Botón Volver al POS --}}
        <a href="{{ route('pos.index') }}" class="w-full inline-block text-center px-4 py-2 text-sm text-gray-700 hover:text-black">
            &larr; Volver al Punto de Venta
        </a>
    </div>

</body>
</html>