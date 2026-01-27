<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Renovar Membresía:') }} {{ $member->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <form method="POST" action="{{ route('members.processRenewal', $member) }}">
                        @csrf

                        <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm font-bold text-gray-700 uppercase">Estado Actual:</p>
                            <p class="text-lg {{ $member->status == 'active' ? 'text-green-600' : 'text-red-600' }}">
                                {{ ucfirst($member->status) }} 
                                @if($member->latestSubscription) 
                                    (Vence: {{ $member->latestSubscription->end_date->format('d/m/Y') }})
                                @endif
                            </p>
                        </div>

                        {{-- TIPO DE MEMBRESÍA --}}
                        <div class="mt-4">
                            <x-input-label for="membership_type_id" :value="__('Seleccionar Nuevo Plan')" />
                            <select name="membership_type_id" id="membership_type_id" required class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                @foreach ($membershipTypes as $type)
                                    <option value="{{ $type->id }}">
                                        {{ $type->name }} - ${{ $member->is_student ? $type->price_student : $type->price_general }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- FORMA DE PAGO --}}
                        <div class="mt-4">
                            <x-input-label for="payment_method" :value="__('Forma de Pago')" />
                            <select name="payment_method" id="payment_method_renew" onchange="toggleReferenceRenew()" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta">Tarjeta (Débito/Crédito)</option>
                                <option value="Transferencia">Transferencia / SPEI</option>
                            </select>
                        </div>

                        {{-- REFERENCIA --}}
                        <div id="reference_container_renew" class="mt-4 hidden">
                            <x-input-label for="payment_reference" :value="__('Folio o Referencia de Pago')" />
                            <x-text-input id="payment_reference_renew" name="payment_reference" type="text" class="block mt-1 w-full" placeholder="Folio de operación" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-primary-button>
                                {{ __('Procesar Renovación') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleReferenceRenew() {
            const method = document.getElementById('payment_method_renew').value;
            const container = document.getElementById('reference_container_renew');
            if (method === 'Tarjeta' || method === 'Transferencia') {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
                document.getElementById('payment_reference_renew').value = '';
            }
        }
    </script>
</x-app-layout>