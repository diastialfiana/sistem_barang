<x-app-layout>
    <div class="mb-8 flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Rekap Bulanan ATK</h2>
            <p class="text-slate-500 font-medium">Ringkasan penggunaan Alat Tulis Kantor per bulan.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($months as $item)
            @php
                $date = \Carbon\Carbon::createFromDate($item->year, $item->month, 1);
                $monthLabel = $date->translatedFormat('F Y');
            @endphp
            <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 hover:shadow-lg transition-all group">
                <div class="flex items-center gap-4 mb-6">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">{{ $monthLabel }}</h3>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Periode Laporan</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl">
                        <span class="text-sm font-semibold text-slate-500">Total Harga</span>
                        <span class="text-lg font-black text-slate-800">Rp {{ number_format($item->total_price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl">
                        <span class="text-sm font-semibold text-slate-500">Banyak Barang</span>
                        <span class="text-lg font-black text-blue-600">{{ number_format($item->total_qty, 0, ',', '.') }} <span class="text-xs text-slate-400 font-bold uppercase ml-1">Items</span></span>
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-slate-50">
                    <a href="{{ route('recaps.show', ['year' => $item->year, 'month' => $item->month]) }}" class="w-full py-3 bg-slate-900 text-white rounded-xl font-bold flex items-center justify-center gap-2 hover:bg-blue-600 transition-colors shadow-lg shadow-slate-200">
                        Lihat Detail
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 bg-white rounded-3xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center text-slate-400">
                <svg class="w-16 h-16 mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <p class="font-bold">Belum ada data rekap tersedia.</p>
            </div>
        @endforelse
    </div>
</x-app-layout>
