<x-app-layout>
    <div x-data="{ 
            modalOpen: false, 
            isEdit: false, 
            formAction: '{{ route('items.store') }}', 
            formMethod: 'POST',
            itemName: '',
            itemCategory: '',
            itemUnit: '',
            itemStock: 0,
            
            detectCategory(name) {
                const nameLower = name.toLowerCase();
                
                const alatTulis = ['kertas', 'pulpen', 'pensil', 'spidol', 'penghapus', 'penggaris', 
                                   'stapler', 'lem', 'gunting', 'cutter', 'amplop', 'map', 'folder',
                                   'binder', 'clip', 'tinta', 'tipe-x', 'correction', 'highlighter',
                                   'marker', 'ballpoint', 'notes', 'sticky', 'post-it'];
                
                for (let keyword of alatTulis) {
                    if (nameLower.includes(keyword)) return 'Alat Tulis Kantor';
                }
                
                const elektronik = ['laptop', 'komputer', 'monitor', 'keyboard', 'mouse', 'printer',
                                    'scanner', 'proyektor', 'kabel', 'charger', 'adaptor', 'speaker',
                                    'headset', 'webcam', 'harddisk', 'flashdisk', 'usb', 'hdmi'];
                
                for (let keyword of elektronik) {
                    if (nameLower.includes(keyword)) return 'Elektronik';
                }
                
                const peralatanKantor = ['meja', 'kursi', 'lemari', 'rak', 'papan', 'whiteboard',
                                         'ac', 'kipas', 'lampu', 'kunci', 'gembok'];
                
                for (let keyword of peralatanKantor) {
                    if (nameLower.includes(keyword)) return 'Peralatan Kantor';
                }
                
                const kebersihan = ['sapu', 'pel', 'kain', 'lap', 'detergen', 'sabun', 'pembersih',
                                    'tissue', 'tisu', 'sampah', 'kantong', 'plastik'];
                
                for (let keyword of kebersihan) {
                    if (nameLower.includes(keyword)) return 'Perlengkapan Kebersihan';
                }
                
                return 'UNCATEGORIZED';
            },
            
            openAddModal() {
                this.isEdit = false;
                this.formAction = '{{ route('items.store') }}';
                this.itemName = '';
                this.itemCategory = '';
                this.itemUnit = '';
                this.itemStock = 0;
                this.modalOpen = true;
            },
            
            openEditModal(item) {
                this.isEdit = true;
                this.formAction = '/items/' + item.id;
                this.itemName = item.name;
                this.itemCategory = item.category || '';
                this.itemUnit = item.unit;
                this.itemStock = item.stock;
                this.modalOpen = true;
            }
         }">

        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">Data <span class="text-blue-600">Barang</span></h1>
                <p class="text-slate-500 font-medium">Kelola inventaris dan stok barang perusahaan.</p>
            </div>
            
            <div class="flex gap-3 w-full md:w-auto">
                <form action="{{ route('items.index') }}" method="GET" class="relative flex-1 md:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari..." 
                        class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:border-blue-500 focus:ring-0 shadow-sm transition-colors text-sm font-semibold">
                    <svg class="w-5 h-5 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </form>

                <div class="flex gap-2">
                    <a href="{{ route('items.export.pdf', request()->all()) }}" target="_blank" class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-bold shadow-sm flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <span class="hidden md:inline">PDF</span>
                    </a>
                    <a href="{{ route('items.export.excel', request()->all()) }}" target="_blank" class="px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold shadow-sm flex items-center gap-2">
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                         <span class="hidden md:inline">Excel</span>
                    </a>
                    <button @click="openAddModal()" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold shadow-lg shadow-slate-900/10 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span class="hidden md:inline">Tambah</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters: Area & Periode -->
        <form method="GET" action="{{ route('items.index') }}" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 mb-6 flex flex-col md:flex-row gap-4 items-center">
            <!-- Maintain Search query if exists -->
            @if(request('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif

            <div class="flex items-center gap-2">
                <label class="font-bold text-slate-700">Area:</label>
                <select name="area" class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm font-semibold" onchange="this.form.submit()">
                    <option value="">Semua Area</option>
                    <option value="Gudang Pusat" {{ request('area') == 'Gudang Pusat' ? 'selected' : '' }}>Gudang Pusat</option>
                    <option value="Cabang A" {{ request('area') == 'Cabang A' ? 'selected' : '' }}>Cabang A</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="font-bold text-slate-700">Periode:</label>
                <input type="month" name="period" class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm font-semibold" 
                    value="{{ request('period', date('Y-m')) }}" onchange="this.form.submit()">
            </div>
        </form>

        <!-- Data Grid -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Nama Barang</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Satuan</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Stock (Awal)</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Keluar</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Sisa</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-center">Request</th>
                            <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($items as $item)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-slate-500 font-bold">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-700">{{ $item->name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-1 rounded">{{ $item->unit }}</span>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-slate-700">
                                {{ $item->stock + ($item->total_keluar ?? 0) }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-red-600 bg-red-50 rounded-lg">
                                {{ $item->total_keluar ?? 0 }}
                            </td>
                             <td class="px-6 py-4 text-center">
                                @if($item->stock <= 5)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-red-50 text-red-600 border border-red-100">
                                        {{ $item->stock }}
                                    </span>
                                @else
                                    <span class="font-bold text-slate-700">{{ $item->stock }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-blue-600 bg-blue-50 rounded-lg">
                                {{ $item->total_request ?? 0 }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end items-center gap-2">
                                    <button @click="openEditModal({{ $item }})" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <form action="{{ route('items.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus item ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                Tidak ada data barang ditemukan.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
                {{ $items->links() }}
            </div>
        </div>

        <!-- Clean Modal -->
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                
                <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900 bg-opacity-40 transition-opacity" aria-hidden="true" @click="modalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="modalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative">
                    
                    <div class="bg-white px-8 py-6 border-b border-slate-100">
                        <h3 class="text-xl font-bold text-slate-900" x-text="isEdit ? 'Edit Barang' : 'Tambah Barang'"></h3>
                    </div>

                    <form :action="formAction" method="POST" class="p-8 space-y-5">
                        @csrf
                        <input type="hidden" name="_method" :value="isEdit ? 'PUT' : 'POST'">
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nama Barang</label>
                            <input type="text" name="name" x-model="itemName" 
                                   @input="itemCategory = detectCategory(itemName)"
                                   required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-semibold text-slate-800 transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Kategori</label>
                            <input type="text" name="category" x-model="itemCategory" readonly 
                                   class="w-full bg-slate-100 border border-slate-200 rounded-xl px-4 py-3 font-semibold text-slate-800 text-center cursor-not-allowed">
                            <p class="text-xs text-slate-500 mt-1 text-center">Otomatis terisi berdasarkan nama barang</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Satuan</label>
                                <select name="unit" x-model="itemUnit" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-semibold text-slate-800 transition-all">
                                    <option value="">Pilih</option>
                                    <option value="Pcs">Pcs</option>
                                    <option value="Box">Box</option>
                                    <option value="Rim">Rim</option>
                                    <option value="Pack">Pack</option>
                                    <option value="Unit">Unit</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Stok Awal</label>
                                <input type="number" name="stock" x-model="itemStock" required min="0" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-semibold text-slate-800 transition-all">
                            </div>
                        </div>

                        <div class="mt-8 flex gap-3 pt-4">
                            <button type="button" @click="modalOpen = false" class="flex-1 py-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold rounded-xl transition-colors">Batal</button>
                            <button type="submit" class="flex-1 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-lg shadow-slate-900/10 transition-all">Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
