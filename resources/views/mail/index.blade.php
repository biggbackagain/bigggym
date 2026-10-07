<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Enviar Correos Masivos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-4 p-4 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-[#000000] overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-white">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Redactar Mensaje</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">El correo se enviará a los miembros que tengan un email registrado. Esto puede tardar varios minutos si la lista es larga.</p>
                    
                    <form method="POST" action="{{ route('mail.send') }}">
                        @csrf

                        <div>
                            <x-input-label for="target" :value="__('Enviar a:')" />
                            <select name="target" id="target" class="block mt-1 w-full border-gray-300 dark:border-[#444444] focus:border-[#171A20] dark:focus:border-white focus:ring-[#171A20] dark:focus:ring-white rounded-md shadow-sm">
                                <option value="all">Todos los Miembros</option>
                                <option value="active">Solo Miembros Activos</option>
                                <option value="inactive">Solo Miembros Inactivos (Vencidos)</option>
                            </select>
                            <x-input-error :messages="$errors->get('target')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="subject" :value="__('Asunto')" />
                            <x-text-input id="subject" class="block mt-1 w-full" type="text" name="subject" :value="old('subject')" required />
                            <x-input-error :messages="$errors->get('subject')" class="mt-2" />
                        </div>
                        
                        <div class="mt-4">
                            <x-input-label for="message" :value="__('Mensaje')" />
                            <textarea id="message" name="message" rows="10" class="block mt-1 w-full border-gray-300 dark:border-[#444444] focus:border-[#171A20] dark:focus:border-white focus:ring-[#171A20] dark:focus:ring-white rounded-md shadow-sm">{{ old('message') }}</textarea>
                            <x-input-error :messages="$errors->get('message')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button onclick="return confirm('¿Estás seguro de enviar este correo a todos los miembros seleccionados? Esta acción no se puede deshacer.');">
                                {{ __('Enviar Campaña') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>






