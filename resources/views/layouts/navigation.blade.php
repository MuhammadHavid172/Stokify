<nav x-data="{ open: false }" class="bg-white border-b border-gray-200">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ auth()->user()->homeRoute() }}" class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-[#166534] text-white flex items-center justify-center font-black text-sm shadow-xs">
                            S
                        </div>
                        <div>
                            <span class="font-black text-base text-gray-900 tracking-tight">STOKIFY</span>
                            <span class="text-[10px] text-gray-500 font-semibold block -mt-1">{{ auth()->user()->kios->nama_kios ?? 'Kios' }}</span>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-6 sm:-my-px sm:ms-8 sm:flex items-center">
                    <a href="{{ auth()->user()->homeRoute() }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-700 hover:text-[#166534] hover:bg-[#DCFCE7]/50 border border-transparent transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <span>Kembali ke {{ auth()->user()->isAdmin() ? 'Dashboard Admin' : 'Layar Kasir' }}</span>
                    </a>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-1.5 border border-gray-200 text-xs font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-md bg-[#DCFCE7] text-[#166534] flex items-center justify-center text-[10px] font-bold">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </span>
                                <span>{{ Auth::user()->name }}</span>
                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-gray-100 border border-gray-200 capitalize font-medium">{{ Auth::user()->role }}</span>
                            </div>

                            <div class="ms-1.5">
                                <svg class="fill-current h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            Profil
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();" class="text-rose-600 hover:text-rose-700">
                                Keluar
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-t border-gray-200">
        <div class="pt-2 pb-3 space-y-1 px-3">
            <a href="{{ auth()->user()->homeRoute() }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-gray-700 hover:text-[#166534] hover:bg-[#DCFCE7]/50">
                &larr; Kembali ke {{ auth()->user()->isAdmin() ? 'Dashboard Admin' : 'Layar Kasir' }}
            </a>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-2 border-t border-gray-200 px-4">
            <div class="font-bold text-sm text-gray-900">{{ Auth::user()->name }}</div>
            <div class="font-medium text-xs text-gray-500">@<span>{{ Auth::user()->username }}</span> &bull; {{ Auth::user()->role }}</div>

            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}" class="block px-2 py-1.5 text-xs text-gray-700 hover:text-gray-900 font-medium">
                    Profil
                </a>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-2 py-1.5 text-xs text-rose-600 hover:text-rose-700 font-medium">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
