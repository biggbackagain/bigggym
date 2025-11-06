<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de Miembros') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="flex flex-col sm:flex-row justify-between items-center mb-4 gap-4">

                        <div class="flex space-x-2">
                            @php
                                $baseClasses = 'px-4 py-2 rounded-md text-sm font-medium';
                                $activeClasses = 'bg-indigo-600 text-white';
                                $inactiveClasses = 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50';
                            @endphp

                            <a href="{{ route('members.index', ['search' => request('search')]) }}"
                               class="{{ $baseClasses }} {{ !request('status') ? $activeClasses : $inactiveClasses }}">
                                Todos
                            </a>
                            <a href="{{ route('members.index', ['status' => 'active', 'search' => request('search')]) }}"
                               class="{{ $baseClasses }} {{ request('status') == 'active' ? $activeClasses : $inactiveClasses }}">
                                Activos
                            </a>
                            <a href="{{ route('members.index', ['status' => 'inactive', 'search' => request('search')]) }}"
                               class="{{ $baseClasses }} {{ request('status') == 'inactive' ? $activeClasses : $inactiveClasses }}">
                                Inactivos
                            </a>
                        </div>

                        <form method="GET" action="{{ route('members.index') }}" class="flex">
                            @if (request('status'))
                                <input type="hidden" name="status" value="{{ request('status') }}">
                            @endif
                            <x-text-input name="search" type="text" placeholder="Buscar por nombre o código..." value="{{ request('search') }}" />
                            <x-primary-button class="ms-2">Buscar</x-primary-button>
                        </form>
                    </div>

                    <div class="mb-4 text-right">
                        <a href="{{ route('members.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Registrar Nuevo Miembro
                        </a>
                    </div>


                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estatus</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Membresía Inicia</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Membresía Vence</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Acciones</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($members as $member)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    @if ($member->profile_photo_path)
                                                        {{-- *** USA Storage::url() *** --}}
                                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ Storage::url($member->profile_photo_path) }}" alt="Foto de perfil">
                                                    @else
                                                        <span class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                                            {{ strtoupper(substr($member->name, 0, 1)) }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="ms-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $member->member_code }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($member->status == 'active')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Vencido
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $member->latestSubscription?->start_date ? \Carbon\Carbon::parse($member->latestSubscription->start_date)->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $member->latestSubscription?->end_date ? \Carbon\Carbon::parse($member->latestSubscription->end_date)->format('d/m/Y') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('members.edit', $member->id) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>

                                            <form method="POST" action="{{ route('members.destroy', $member->id) }}" class="inline-block ms-2" onsubmit="return confirm('¿Estás seguro de que quieres eliminar a {{ $member->name }}? Esta acción no se puede deshacer.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            No se encontraron miembros.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $members->appends(request()->query())->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>