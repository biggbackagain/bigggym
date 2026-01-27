<x-mail::message>
# ¡Bienvenido(a), {{ $member->name }}! 👋

Estamos muy emocionados de tenerte como parte de la familia de **{{ $gymName }}**. 

Aquí tienes los detalles de tu inscripción y tu acceso:

<x-mail::panel>
**Código de Miembro:** {{ $member->member_code }}  
**Membresía:** {{ $subscription->membershipType->name }}  
**Vigencia hasta:** {{ $subscription->end_date->format('d/m/Y') }}
</x-mail::panel>

## Resumen de Pago
**Monto:** ${{ number_format($subscription->payment->amount, 2) }}  
**Método de Pago:** {{ $subscription->payment_method }}  
**Fecha:** {{ $subscription->start_date->format('d/m/Y') }}

<x-mail::button :url="config('app.url')">
Ir a mi Perfil
</x-mail::button>

¡Prepárate para alcanzar tus metas de fitness con nosotros! Si tienes alguna pregunta, no dudes en consultarnos en recepción.

¡Nos vemos en el gimnasio!<br>
El equipo de {{ $gymName }}
</x-mail::message>