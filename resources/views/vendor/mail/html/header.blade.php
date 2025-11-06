<tr>
    <td class="header">
        {{-- Enlace a la URL principal de la aplicación --}}
        <a href="{{ config('app.url') }}" style="display: inline-block; color: #3d4852; font-size: 19px; font-weight: bold; text-decoration: none;">
            @php
                // Intenta obtener el nombre del gimnasio desde la caché global
                // Si no está, usa el nombre de la app definido en config/app.php
                $gymName = Illuminate\Support\Facades\Cache::get('global_settings')['gym_name'] ?? config('app.name');
            @endphp
            {{-- Muestra el nombre del gimnasio como texto --}}
            {{ $gymName }}
        </a>
    </td>
</tr>