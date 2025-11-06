<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Control de Acceso (Check-in)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <p class="text-center text-gray-600 mb-4">Ingrese el código del miembro para verificar su acceso.</p>

                    <form method="POST" action="{{ route('check-in.store') }}">
                        @csrf
                        <div class="flex">
                            <x-text-input id="member_code" class="block w-full text-lg" type="text" name="member_code" required autofocus autocomplete="off" />
                            <x-primary-button class="ms-4 !text-lg !px-6">
                                {{ __('Verificar') }}
                            </x-primary-button>
                        </div>
                        <x-input-error :messages="$errors->get('member_code')" class="mt-2" />
                    </form>

                </div>
            </div>

            @if (session('status'))
                @php
                    $isSuccess = session('status') === 'success';
                    $bgColor = $isSuccess ? 'bg-green-600' : 'bg-red-600';
                    $icon = $isSuccess
                        ? '<svg class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>'
                        : '<svg class="h-16 w-16 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>';
                    $photoPath = session('photo_path'); // El controlador ya pasa la ruta relativa
                @endphp

                <div class="mt-6 {{ $bgColor }} rounded-lg shadow-lg p-8 text-white">
                    <div class="flex items-center">

                        <div class="shrink-0">
                            {!! $icon !!}
                        </div>

                        <div class="ms-6">
                            <h3 class="text-3xl font-bold">{{ session('message') }}</h3>
                            @if (session('end_date'))
                                <p class="text-xl mt-1">Vence el: {{ session('end_date') }}</p>
                            @endif
                        </div>

                        @if ($photoPath)
                            <div class="ms-auto ps-6 shrink-0">
                                {{-- *** USA Storage::url() *** --}}
                                <img src="{{ Storage::url($photoPath) }}" alt="Foto de perfil" class="h-24 w-24 rounded-full object-cover border-4 border-white">
                            </div>
                        @endif

                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>