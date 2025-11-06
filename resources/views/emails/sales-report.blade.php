<x-mail::message>
{{-- Título del Correo --}}
# Reporte de Caja - {{ $startDate->isSameDay($endDate) ? $startDate->format('d/m/Y') : $startDate->format('d/m/Y') . ' al ' . $endDate->format('d/m/Y') }}
## {{ $gymName }}

<x-mail::panel> {{-- Panel para Resumen --}}
## Resumen General

| Concepto             | Total       | Notas                                       |
| :------------------- | :---------- | :------------------------------------------ |
| Ingreso Productos    | ${{ number_format($totalProductSalesAmount, 2) }} | {{ $totalProductSalesCount }} ventas       |
| Ingreso Membresías | ${{ number_format($totalMembershipPaymentsAmount, 2) }} | {{ $totalMembershipPaymentsCount }} pagos |
| Neto Mov. Caja     | ${{ number_format($netCashMovement, 2) }}     | (+${{ number_format($totalCashEntries, 2) }} / -${{ number_format($totalCashExits, 2) }}) |
| **Saldo Final Caja** | **${{ number_format($grandTotal, 2) }}** |                                             |
</x-mail::panel>

---

## Detalle: Ventas de Productos

@if($productSales->isNotEmpty())
<x-mail::table>
| Hora    | Productos (Cant x Nombre @ Precio) | Total Venta |
| :------ | :--------------------------------- | :---------- |
@foreach ($productSales as $sale)
| {{ $sale->created_at->format('h:i A') }} | @foreach ($sale->products as $product) ({{ $product->pivot->quantity }}x) {{ $product->name }} @ ${{ number_format($product->pivot->price_at_sale, 2) }}<br> @endforeach | ${{ number_format($sale->total_amount, 2) }} |
@endforeach
</x-mail::table>
@else
No se registraron ventas de productos en este período.
@endif

---

## Detalle: Pagos de Membresías

@if($membershipPayments->isNotEmpty())
<x-mail::table>
| Hora    | Miembro                      | Plan                                        | Monto   |
| :------ | :--------------------------- | :------------------------------------------ | :------ |
@foreach ($membershipPayments as $payment)
| {{ $payment->created_at->format('h:i A') }} | {{ $payment->member?->name ?? 'N/A' }} <small>({{ $payment->member?->member_code ?? 'N/A' }})</small> | {{ $payment->subscription?->membershipType?->name ?? 'N/A' }} | ${{ number_format($payment->amount, 2) }} |
@endforeach
</x-mail::table>
@else
No se registraron pagos de membresías en este período.
@endif

---

## Detalle: Otros Movimientos de Caja

@if($cashMovements->isNotEmpty())
<x-mail::table>
| Hora    | Tipo    | Monto   | Descripción                     | Registrado por              |
| :------ | :------ | :------ | :------------------------------ | :-------------------------- |
@foreach ($cashMovements as $movement)
| {{ $movement->created_at->format('h:i A') }} | <span style="color:{{ $movement->type == 'entry' ? 'green' : 'red' }}; font-weight: bold;">{{ $movement->type == 'entry' ? 'Entrada' : 'Salida' }}</span> | ${{ number_format($movement->amount, 2) }} | {{ $movement->description }}            | {{ $movement->user?->name ?? 'N/A' }} |
@endforeach
</x-mail::table>
@else
No se registraron otros movimientos de caja en este período.
@endif


<br>
Saludos,<br>
El equipo de {{ $gymName }}
</x-mail::message>