<x-mail::message>
{{-- Saludo personalizado --}}
# Hola, {{ $memberName }}

{{-- Contenido del mensaje (sin cambios) --}}
{{ $messageContent }}

<br>
Saludos,<br>
{{-- Nombre del Gimnasio (Remitente) --}}
{{ $fromName ?? config('app.name') }}
</x-mail::message>