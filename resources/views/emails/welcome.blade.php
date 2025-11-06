<x-mail::message>
# ¡Bienvenido(a), {{ $memberName }}! 👋

Estamos muy emocionados de tenerte como parte de la familia de **{{ $gymName }}**.

Aquí tienes tu código de miembro para el check-in: **{{ $member->member_code }}**

¡Prepárate para alcanzar tus metas de fitness con nosotros! Si tienes alguna pregunta, no dudes en consultarnos en recepción.

<x-mail::button :url="config('app.url')">
Visita Nuestro Sitio (si aplica)
</x-mail::button>

¡Nos vemos en el gimnasio!<br>
El equipo de {{ $gymName }}
</x-mail::message>