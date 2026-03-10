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

                    @if (session('print_receipt'))
                        <div class="mb-6 p-6 bg-blue-50 border-l-4 border-blue-500 text-blue-700 shadow-md rounded-r-lg" id="receipt-alert">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="py-1 mr-4">
                                        <svg class="fill-current h-10 w-10 text-blue-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-xl">¡Registro Exitoso!</p>
                                        <p class="text-lg mt-1">
                                            Bienvenido(a), <strong>{{ session('new_member_name') }}</strong>. 
                                            Código de acceso: <span class="bg-blue-600 text-white px-3 py-1 rounded-full font-mono text-2xl ml-2 shadow-sm">{{ session('print_receipt') }}</span>
                                        </p>
                                    </div>
                                </div>
                                <button onclick="document.getElementById('receipt-alert').remove()" class="text-blue-500 hover:text-blue-700 focus:outline-none">
                                    <span class="text-3xl">&times;</span>
                                </button>
                            </div>
                        </div>
                    @elseif (session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg shadow-sm" id="success-alert">
                            <div class="flex justify-between items-center">
                                <span>{{ session('success') }}</span>
                                <button onclick="document.getElementById('success-alert').remove()" class="text-green-700 hover:text-green-900 font-bold">&times;</button>
                            </div>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg shadow-sm" id="error-alert">
                            <div class="flex justify-between items-center">
                                <span>{{ session('error') }}</span>
                                <button onclick="document.getElementById('error-alert').remove()" class="text-red-700 hover:text-red-900 font-bold">&times;</button>
                            </div>
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
                                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ Storage::url($member->profile_photo_path) }}" alt="Foto de perfil">
                                                    @else
                                                        <span class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold">
                                                            {{ strtoupper(substr($member->name, 0, 1)) }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="ms-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $member->name }}</div>
                                                    <div class="text-sm text-gray-500 font-mono">{{ $member->member_code }}</div>
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
                                            <a href="{{ route('members.edit', $member->id) }}" class="text-indigo-600 hover:text-indigo-900 font-bold">Editar</a>

                                            <form method="POST" action="{{ route('members.destroy', $member->id) }}" class="inline-block ms-4" onsubmit="return confirm('¿Estás seguro de que quieres eliminar a {{ $member->name }}? Esta acción no se puede deshacer.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 font-bold">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            No se encontraron miembros registrados.
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