<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Nuevo Miembro') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form method="POST" action="{{ route('members.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Nombre Completo')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="phone" :value="__('Teléfono')" />
                            <x-text-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="email" :value="__('Email (Opcional)')" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="profile_photo" :value="__('Foto de Perfil (Opcional)')" />
                            <input id="profile_photo" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm" type="file" name="profile_photo" />
                            <x-input-error :messages="$errors->get('profile_photo')" class="mt-2" />
                        </div>

                        <div class="block mt-4">
                            <label for="is_student" class="inline-flex items-center">
                                <input id="is_student" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_student" value="1">
                                <span class="ms-2 text-sm text-gray-600">{{ __('¿Es estudiante? (Aplica descuento)') }}</span>
                            </label>
                        </div>

                        <hr class="my-6">

                        {{-- SECCIÓN DE MEMBRESÍA Y PAGO --}}
                        <div class="mt-4">
                            <x-input-label for="membership_type_id" :value="__('Asignar Membresía (Opcional)')" />
                            <select name="membership_type_id" id="membership_type_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- No asignar membresía ahora --</option>
                                @foreach ($membershipTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- FORMA DE PAGO --}}
                        <div class="mt-4">
                            <x-input-label for="payment_method" :value="__('Forma de Pago')" />
                            <select name="payment_method" id="payment_method" onchange="toggleReferenceField()" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta">Tarjeta (Débito/Crédito)</option>
                                <option value="Transferencia">Transferencia / SPEI</option>
                            </select>
                        </div>

                        {{-- REFERENCIA (Dinámica) --}}
                        <div id="reference_container" class="mt-4 hidden">
                            <x-input-label for="payment_reference" :value="__('Referencia / Folio / Últimos 4 dígitos')" />
                            <x-text-input id="payment_reference" name="payment_reference" type="text" class="block mt-1 w-full" placeholder="Ej: Folio 8821 o Tarjeta 4421" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Guardar Miembro') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleReferenceField() {
            const method = document.getElementById('payment_method').value;
            const container = document.getElementById('reference_container');
            if (method === 'Tarjeta' || method === 'Transferencia') {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
                document.getElementById('payment_reference').value = '';
            }
        }
    </script>
</x-app-layout>