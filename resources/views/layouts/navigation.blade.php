<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 print:hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        @if(isset($globalSettings['gym_logo']) && $globalSettings['gym_logo'] && Storage::disk('public')->exists($globalSettings['gym_logo']))
                            <img src="{{ Storage::url($globalSettings['gym_logo']) }}" alt="Logo" class="block h-9 w-auto object-contain">
                        @else
                            <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                        @endif
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-center">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('check-in.index')" :active="request()->routeIs('check-in.index')">
                        {{ __('Check-in') }}
                    </x-nav-link>
                    <x-nav-link :href="route('members.index')" :active="request()->routeIs('members.*')">
                        {{ __('Miembros') }}
                    </x-nav-link>

                    <div class="relative">
                        <x-dropdown align="left" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-1 pt-1 border-b-2 {{ (request()->routeIs(['pos.*', 'products.*', 'inventory.*', 'sales.report', 'cash.*'])) ? 'border-indigo-400 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-sm font-medium leading-5 focus:outline-none transition duration-150 ease-in-out">
                                    <div>Ventas</div>
                                    <div class="ms-1"> <svg class="fill-current h-4 w-4" viewBox="0 0 20 20">...</svg> </div>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                {{-- Quitamos @can --}}
                                <x-dropdown-link :href="route('pos.index')" :active="request()->routeIs('pos.index')"> {{ __('Punto de Venta (POS)') }} </x-dropdown-link>
                                <x-dropdown-link :href="route('products.index')" :active="request()->routeIs('products.*')"> {{ __('Administrar Productos') }} </x-dropdown-link>
                                <x-dropdown-link :href="route('inventory.index')" :active="request()->routeIs('inventory.index')"> {{ __('Ajustar Inventario') }} </x-dropdown-link>
                                <x-dropdown-link :href="route('sales.report')" :active="request()->routeIs('sales.report')"> {{ __('Reporte de Caja') }} </x-dropdown-link>
                                <x-dropdown-link :href="route('cash.index')" :active="request()->routeIs('cash.index')"> {{ __('Movimientos Caja') }} </x-dropdown-link>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    {{-- Links Administrativos (Quitamos @can) --}}
                    <x-nav-link :href="route('admin.memberships.index')" :active="request()->routeIs('admin.memberships.*')"> {{ __('Tarifas') }} </x-nav-link>
                    <x-nav-link :href="route('mail.index')" :active="request()->routeIs('mail.index')"> {{ __('Enviar Correos') }} </x-nav-link>
                    <x-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.index')"> {{ __('Configuración') }} </x-nav-link>

                    {{-- Link Gestionar Usuarios ELIMINADO --}}
                    {{-- @can('manage-users') ... @endcan --}}
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                 <x-dropdown align="right" width="48">
                    <x-slot name="trigger"> <button> {{ Auth::user()->name }} <svg>...</svg> </button> </x-slot>
                    <x-slot name="content">
                         <x-dropdown-link :href="route('profile.edit')"> {{ __('Mi Perfil') }} </x-dropdown-link>
                         <form method="POST" action="{{ route('logout') }}"> @csrf <x-dropdown-link href="#" onclick="event.preventDefault(); this.closest('form').submit();"> {{ __('Cerrar Sesión') }} </x-dropdown-link> </form>
                    </x-slot>
                 </x-dropdown>
            </div>

            <div class="-me-2 flex items-center sm:hidden"> <button @click="open = ! open"> <svg>...</svg> </button> </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')"> {{ __('Dashboard') }} </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('check-in.index')" :active="request()->routeIs('check-in.index')"> {{ __('Check-in') }} </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('members.index')" :active="request()->routeIs('members.*')"> {{ __('Miembros') }} </x-responsive-nav-link>

            <div class="pt-2 pb-1 border-t border-gray-200">
                <div class="px-4"><div class="font-medium text-base text-gray-800">Ventas</div></div>
                <div class="mt-1 space-y-1">
                    <x-responsive-nav-link :href="route('pos.index')" :active="request()->routeIs('pos.index')"> {{ __('Punto de Venta (POS)') }} </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')"> {{ __('Administrar Productos') }} </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('inventory.index')" :active="request()->routeIs('inventory.index')"> {{ __('Ajustar Inventario') }} </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('sales.report')" :active="request()->routeIs('sales.report')"> {{ __('Reporte de Caja') }} </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('cash.index')" :active="request()->routeIs('cash.index')"> {{ __('Movimientos Caja') }} </x-responsive-nav-link>
                </div>
            </div>

             {{-- Links Administrativos (Móvil - Quitamos @can) --}}
             <x-responsive-nav-link :href="route('admin.memberships.index')" :active="request()->routeIs('admin.memberships.*')"> {{ __('Tarifas') }} </x-responsive-nav-link>
             <x-responsive-nav-link :href="route('mail.index')" :active="request()->routeIs('mail.index')"> {{ __('Enviar Correos') }} </x-responsive-nav-link>
             <x-responsive-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.index')"> {{ __('Configuración') }} </x-responsive-nav-link>

            {{-- Link Gestionar Usuarios (Móvil) ELIMINADO --}}
            {{-- @can('manage-users') ... @endcan --}}
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
             <div class="px-4"> /* ... Nombre y Email Usuario ... */ </div>
             <div class="mt-3 space-y-1"> /* ... Mi Perfil, Cerrar Sesión ... */ </div>
        </div>
    </div>
</nav>