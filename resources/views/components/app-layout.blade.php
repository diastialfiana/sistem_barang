<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Procurement System') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="//unpkg.com/alpinejs" defer></script>
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }
        .nav-link.active {
            background-color: #EEF2FF;
            color: #4F46E5;
            border-right: 3px solid #4F46E5;
        }
        .nav-link:hover:not(.active) {
            background-color: #F8FAFC;
        }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased text-slate-600">
    <div class="min-h-screen flex transition-all duration-300" x-data="{ sidebarOpen: true }">
     
        <aside 
            class="bg-white border-r border-gray-200 fixed lg:static inset-y-0 left-0 z-30 w-72 transform transition-transform duration-300 ease-in-out overflow-y-auto"
            :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen, 'lg:translate-x-0': true}"
        >
            <div class="h-20 flex items-center px-8 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/jayatama.png') }}" alt="Logo" class="h-12 w-auto object-contain">
                    <div>
                        <span class="block text-sm font-extrabold text-slate-800 leading-tight">JAYATAMA</span>
                        <span class="block text-[0.65rem] font-bold text-blue-600 tracking-wider">SISTEM REQUEST BARANG</span>
                    </div>
                </div>
            </div>

            <nav class="mt-6 px-0 space-y-1">
                
                <p class="px-8 text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 mt-4">Menu Utama</p>

                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }} group flex items-center px-8 py-3.5 text-sm font-semibold text-slate-600 transition-colors">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>

                @can('create_requests')
                <a href="{{ route('requests.create') }}" class="nav-link {{ request()->routeIs('requests.create') ? 'active' : '' }} group flex items-center px-8 py-3.5 text-sm font-semibold text-slate-600 transition-colors">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('requests.create') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Buat Request Baru
                </a>
                @endcan

                <a href="{{ route('requests.index') }}" class="nav-link {{ request()->routeIs('requests.index') ? 'active' : '' }} group flex items-center px-8 py-3.5 text-sm font-semibold text-slate-600 transition-colors">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('requests.index') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    Daftar Request
                </a>
                
                @role('super_admin|admin_1')
                <p class="px-8 text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 mt-8">Master Data</p>
                <a href="{{ route('items.index') }}" class="nav-link {{ request()->routeIs('items.index') ? 'active' : '' }} group flex items-center px-8 py-3.5 text-sm font-semibold text-slate-600 transition-colors">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('items.index') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Inventory Barang
                </a>
                @endrole

                @role('super_admin')
                    <p class="px-8 text-xs font-bold text-slate-400 uppercase tracking-widest mb-3 mt-8">System Admin</p>
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }} group flex items-center px-8 py-3.5 text-sm font-semibold text-slate-600 transition-colors">
                        <svg class="mr-3 h-5 w-5 {{ request()->routeIs('users.index') ? 'text-blue-600' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Manajemen User
                    </a>
                @endrole

            </nav>

            <div class="absolute bottom-0 w-full border-t border-gray-100 p-6 bg-gray-50/50">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold border border-white shadow-sm">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-500 truncate">NIP: {{ Auth::user()->nip ?? '-' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors p-2 hover:bg-white rounded-lg" title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="flex-1 flex flex-col min-w-0 overflow-hidden bg-gray-50">
          
            <div class="lg:hidden p-4 bg-white border-b border-gray-200 flex justify-between items-center">
                <img src="{{ asset('images/jayatama.png') }}" alt="Logo" class="h-8 w-auto">
                <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-md hover:bg-gray-100 text-slate-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
            </div>

            <div class="flex-1 overflow-auto p-4 md:p-8">
                
             
                @if(session('success'))
                    <div class="mb-6 bg-white border-l-4 border-green-500 p-4 rounded-lg shadow-sm flex items-start gap-3" role="alert">
                        <div class="text-green-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <div>
                            <p class="text-sm text-slate-800 font-bold">Berhasil</p>
                            <p class="text-sm text-slate-600">{{ session('success') }}</p>
                        </div>
                    </div>
                @endif
    
                @if(session('error'))
                    <div class="mb-6 bg-white border-l-4 border-red-500 p-4 rounded-lg shadow-sm flex items-start gap-3" role="alert">
                        <div class="text-red-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                         <div>
                            <p class="text-sm text-slate-800 font-bold">Terjadi Kesalahan</p>
                            <p class="text-sm text-slate-600">{{ session('error') }}</p>
                        </div>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
