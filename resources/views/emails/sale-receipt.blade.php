<x-mail::message>
# ¡Gracias por tu compra!

Hola,
Aquí tienes un resumen de tu compra en **{{ $gymName }}** realizada el {{ $sale->created_at->format('d/m/Y h:i A') }}.

<x-mail::table>
| Producto | Cantidad | Precio Unitario | Total |
| :--- | :---: | :---: | ---: |
@foreach ($sale->products as $product)
| {{ $product->name }} | {{ $product->pivot->quantity }} | ${{ number_format($product->pivot->price_at_sale, 2) }} | ${{ number_format($product->pivot->price_at_sale * $product->pivot->quantity, 2) }} |
@endforeach
</x-mail::table>

<p style="text-align: right; font-weight: bold; font-size: 1.1em;">
    Total Pagado: ${{ number_format($sale->total_amount, 2) }}
</p>
<p style="text-align: right; font-size: 0.9em;">
    Método de Pago: {{ ucfirst($sale->payment_method) }}
    @if($sale->payment_reference)
    <br>
    Referencia: {{ $sale->payment_reference }}
    @endif
</p>


Gracias,<br>
El equipo de {{ $gymName }}
</x-mail::message>