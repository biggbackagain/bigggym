<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Renovar Suscripción para: {{ $member->name }} ({{ $member->member_code }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Display current status --}}
                    <div class="mb-4 p-4 border rounded-md {{ $member->status == 'active' ? 'border-green-300 bg-green-50' : 'border-red-300 bg-red-50' }}">
                        <p class="font-medium">Estado Actual:
                            @if($member->status == 'active')
                                <span class="font-bold text-green-700">Activo</span>
                                @if($member->latestSubscription)
                                    (Vence: {{ \Carbon\Carbon::parse($member->latestSubscription->end_date)->format('d/m/Y') }})
                                @endif
                            @else
                                <span class="font-bold text-red-700">Vencido</span>
                                @if($member->latestSubscription)
                                    (Venció: {{ \Carbon\Carbon::parse($member->latestSubscription->end_date)->format('d/m/Y') }}) {{-- Show expired date --}}
                                @endif
                            @endif
                        </p>
                        <p class="text-sm text-gray-600">Tipo de miembro: {{ $member->is_student ? 'Estudiante' : 'General' }}</p>
                    </div>

                    <form method="POST" action="{{ route('members.processRenewal', $member->id) }}">
                        @csrf

                        <div>
                            <x-input-label for="membership_type_id" :value="__('Seleccionar Nuevo Plan')" />
                            <select name="membership_type_id" id="membership_type_id" required class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Elige un plan --</option>
                                @foreach ($membershipTypes as $type)
                                    <option value="{{ $type->id }}">
                                        {{ $type->name }} - ${{ $member->is_student ? number_format($type->price_student, 2) : number_format($type->price_general, 2) }}
                                        ({{ $type->duration_days }} días)
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('membership_type_id')" class="mt-2" />
                            <p class="mt-1 text-sm text-gray-500">El precio mostrado ya considera si el miembro es estudiante.</p>
                        </div>


                        <div class="flex items-center justify-end mt-6">
                             <a href="{{ route('members.edit', $member->id) }}" class="text-gray-600 hover:text-gray-900 me-4">Cancelar</a>
                            <x-primary-button class="ms-4">
                                {{ __('Registrar Renovación') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>