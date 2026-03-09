<x-app-layout>
    <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2 mb-2 text-blue-600 font-bold text-xs uppercase tracking-widest">
                <a href="{{ route('recaps.index') }}" class="hover:text-blue-800 flex items-center gap-1 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Semua Rekap
                </a>
                <span>/</span>
                <span>Detail Laporan</span>
            </div>
            <h2 class="text-3xl font-black text-slate-800 tracking-tight">Detail ATK {{ $label }}</h2>
            <p class="text-slate-500 font-medium">Breakdown penggunaan Alat Tulis Kantor secara mendetail.</p>
        </div>
        <div class="flex gap-3">
             <div class="bg-blue-600 text-white px-6 py-3 rounded-2xl shadow-lg shadow-blue-200 flex flex-col items-center">
                <span class="text-[10px] font-bold uppercase tracking-widest opacity-80">Total Pengeluaran</span>
                <span class="text-xl font-black">Rp {{ number_format($items->sum(fn($i) => $i->quantity * $i->item->price), 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Barang (Inventory)</th>
                        <th class="px-6 py-5 text-xs font-bold text-slate-500 uppercase tracking-wider">Requested By / Branch</th>
                        <th class="px-6 py-5 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Qty</th>
                        <th class="px-6 py-5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Subtotal</th>
                        <th class="px-6 py-5 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Aksi Inventory</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $item)
                    <tr class="hover:bg-slate-50 transition-colors group">
                        <td class="px-6 py-4 text-slate-400 font-bold text-sm">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-bold text-slate-700">{{ $item->request->request_date->translatedFormat('d M Y') }}</span>
                            <div class="text-[10px] text-slate-400 font-bold tracking-tight">{{ $item->request->code }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-800">{{ $item->item_name ?? ($item->item->name ?? '-') }}</span>
                                <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded-md font-bold self-start mt-1 uppercase">{{ $item->item->category ?? 'ATK' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="h-8 w-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs border border-blue-100">
                                    {{ substr($item->request->user->name, 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-slate-700">{{ $item->request->user->name }}</span>
                                    <span class="text-xs text-slate-400 font-medium">{{ $item->request->branch->name ?? 'Pusat' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center justify-center p-2 bg-blue-50 text-blue-600 rounded-xl font-black text-sm min-w-[40px]">
                                {{ $item->quantity }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="font-bold text-slate-800 whitespace-nowrap">Rp {{ number_format($item->quantity * $item->item->price, 0, ',', '.') }}</span>
                            <div class="text-[10px] text-slate-400 font-bold">@ Rp {{ number_format($item->item->price, 0, ',', '.') }}</div>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('items.edit', $item->item_id) }}" class="p-2 bg-amber-50 text-amber-600 hover:bg-amber-600 hover:text-white rounded-lg transition-all border border-amber-100" title="Edit Item Inventory">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <a href="{{ route('items.show', $item->item_id) }}" class="p-2 bg-slate-50 text-slate-600 hover:bg-slate-800 hover:text-white rounded-lg transition-all border border-slate-100" title="Lihat Stok Inventory">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <svg class="w-16 h-16 mb-4 opacity-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                <p class="font-bold">Tidak ada data item untuk periode ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
