<x-app-layout>
    <div class="space-y-8">
        
        <!-- Welcome Hero Section -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl shadow-xl p-8 relative overflow-hidden text-white">
            <div class="absolute right-0 top-0 w-64 h-64 bg-white/10 rounded-full mix-blend-overlay filter blur-3xl opacity-50 transform translate-x-1/2 -translate-y-1/2"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <h2 class="text-3xl font-bold mb-2">
                        Dashboard Operasional
                    </h2>
                    <p class="text-blue-100 text-lg">
                        Halo, <span class="font-bold border-b border-blue-400 pb-0.5">{{ Auth::user()->name }}</span>
                    </p>
                     <div class="mt-4 flex flex-col sm:flex-row gap-4 text-sm text-blue-200">
                         <div class="flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-lg border border-white/10">
                            <svg class="w-4 h-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                            <span>Terakhir Login: <span class="font-semibold text-white">{{ Auth::user()->previous_login_at ? \Carbon\Carbon::parse(Auth::user()->previous_login_at)->translatedFormat('l, d F Y H:i:s') : '-' }}</span></span>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <div class="inline-flex items-center gap-3 bg-white/20 backdrop-blur-sm px-4 py-2 rounded-xl border border-white/20 shadow-lg">
                            <div class="p-1.5 bg-white/20 rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-[10px] text-blue-100 uppercase tracking-wider font-bold">Waktu Jakarta (WIB)</p>
                                <p id="liveClock" class="text-xl font-mono font-bold text-white tracking-wide">Loading...</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                @can('create_requests')
                <a href="{{ route('requests.create') }}" class="px-6 py-3 bg-white text-blue-600 hover:bg-slate-50 rounded-xl font-bold shadow-lg transition-all transform hover:-translate-y-0.5 flex items-center gap-2 group">
                    <div class="bg-blue-100 p-1.5 rounded-full group-hover:bg-blue-200 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    <span>Buat Request</span>
                </a>
                @endcan
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($stats as $stat)
            <div class="bg-white rounded-2xl p-6 shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-slate-100 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">{{ $stat['label'] }}</p>
                        <h3 class="text-3xl font-black text-slate-800 tracking-tight">{{ $stat['value'] }}</h3>
                    </div>
                    <div class="p-3 bg-{{ $stat['color'] }}-50 text-{{ $stat['color'] }}-600 rounded-2xl shadow-sm">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"></path></svg>
                    </div>
                </div>
                <!-- Mini Progress/Decor -->
                <div class="mt-4 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-{{ $stat['color'] }}-500 h-1.5 rounded-full" style="width: 70%"></div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Item Keluar Chart -->
            <div class="xl:col-span-1 bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-slate-100 p-6">
                <h3 class="font-bold text-slate-800 text-lg mb-6 flex items-center gap-2">
                    <span class="p-1.5 bg-emerald-100 text-emerald-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    </span>
                    Top Item Keluar
                </h3>
                
                @if($topItems->count() > 0)
                    <div class="relative h-64">
                         <canvas id="itemOutChart"></canvas>
                    </div>
                    <div class="mt-4 space-y-3">
                        @foreach($topItems as $index => $item)
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-400">#{{ $index + 1 }}</span>
                                <span class="font-medium text-slate-700 truncate max-w-[150px]">{{ $item->item_name }}</span>
                            </div>
                            <span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">{{ $item->total_qty }}</span>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="h-64 flex flex-col items-center justify-center text-slate-400">
                        <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                        <p>Belum ada data item keluar</p>
                    </div>
                @endif
            </div>

            <!-- Recent Activity List -->
            <div class="xl:col-span-2 bg-white rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-slate-100 overflow-hidden">
                <div class="p-6 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                        <span class="p-1.5 bg-blue-100 text-blue-600 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </span>
                        Aktivitas Terkini
                    </h3>
                    <a href="{{ route('requests.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1 group">
                        Lihat Semua 
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                    </a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse($recentRequests as $req)
                    <div class="p-5 hover:bg-slate-50 transition-colors group">
                        <div class="flex items-center gap-4">
                             <div class="flex-shrink-0 h-12 w-12 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold text-sm border border-orange-100 group-hover:scale-105 transition-transform">
                                {{ substr($req->code, -3) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h4 class="text-sm font-bold text-slate-800 truncate">{{ $req->code }}</h4>
                                    <span class="text-xs text-slate-400">•</span>
                                    <p class="text-xs text-slate-500 truncate">{{ $req->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    <span class="font-medium">{{ $req->user->name }}</span>
                                    @if($req->user->branch)
                                    <span class="text-slate-300">|</span>
                                    <span class="text-slate-500">{{ $req->user->branch->name }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                 @php
                                    $statusColor = match($req->status) {
                                        'approved' => 'emerald',
                                        'rejected' => 'red',
                                        'draft' => 'slate',
                                        default => 'amber'
                                    };
                                    $statusLabel = match($req->status) {
                                        'pending_spv' => 'Wait SPV',
                                        'pending_ka' => 'Wait KA',
                                        'pending_ga' => 'Wait GA',
                                        default => ucfirst($req->status)
                                    };
                                    $statusIcon = match($req->status) {
                                        'approved' => 'M5 13l4 4L19 7',
                                        'rejected' => 'M6 18L18 6M6 6l12 12',
                                        'draft' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                                        default => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-{{ $statusColor }}-50 text-{{ $statusColor }}-700 border border-{{ $statusColor }}-100 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $statusIcon }}"></path></svg>
                                    {{ $statusLabel }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-12 text-center">
                        <div class="inline-flex bg-slate-50 p-4 rounded-full mb-4">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </div>
                        <h4 class="text-slate-800 font-bold mb-1">Belum ada aktivitas</h4>
                        <p class="text-slate-500 text-sm">Permintaan barang terbaru akan muncul di sini.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Live Clock Script
            function updateClock() {
                const now = new Date();
                const options = { 
                    timeZone: 'Asia/Jakarta', 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric', 
                    hour: '2-digit', 
                    minute: '2-digit', 
                    second: '2-digit', 
                    hour12: false 
                };
                const formatter = new Intl.DateTimeFormat('id-ID', options);
                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                
                // Adjust to Jakarta Time
                const jakartaTime = new Date(now.toLocaleString("en-US", {timeZone: "Asia/Jakarta"}));
                
                const dayName = days[jakartaTime.getDay()];
                const day = jakartaTime.getDate();
                const month = months[jakartaTime.getMonth()];
                const year = jakartaTime.getFullYear();
                const hours = String(jakartaTime.getHours()).padStart(2, '0');
                const minutes = String(jakartaTime.getMinutes()).padStart(2, '0');
                const seconds = String(jakartaTime.getSeconds()).padStart(2, '0');
                
                const formattedTime = `${hours}:${minutes}:${seconds}`;
                const formattedDate = `${dayName}, ${day} ${month} ${year}`;
                
                const clockEl = document.getElementById('liveClock');
                if(clockEl) {
                   clockEl.innerHTML = `${formattedDate} <span class="mx-2">•</span> ${formattedTime}`;
                }
            }
            
            setInterval(updateClock, 1000);
            updateClock();

            @if(isset($topItems) && $topItems->count() > 0)
                const ctx = document.getElementById('itemOutChart').getContext('2d');
                
                // Extract data from Blade
                const labels = @json($topItems->pluck('item_name'));
                const data = @json($topItems->pluck('total_qty'));
                
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: [
                                '#10b981', // emerald-500
                                '#3b82f6', // blue-500
                                '#f59e0b', // amber-500
                                '#8b5cf6', // violet-500
                                '#ef4444', // red-500
                            ],
                            borderWidth: 0,
                            hoverOffset: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            @endif
        });
    </script>
</x-app-layout>
