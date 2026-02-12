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
            
            // Import states
            importModalOpen: false,
            previewModalOpen: false,
            resultModalOpen: false,
            importData: null,
            selectedItems: [],
            importResult: null,
            isUploading: false,
            
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
            },
            
            // Import functions
            async previewImportFile(event) {
                const file = event.target.files[0];
                if (!file) return;
                
                this.isUploading = true;
                const formData = new FormData();
                formData.append('file', file);
                
                try {
                    const response = await fetch('{{ route('items.import.preview') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.importData = result.data;
                        this.selectedItems = result.data.new_items.map((_, index) => index); // Select all new items by default
                        this.importModalOpen = false;
                        this.previewModalOpen = true;
                    } else {
                        alert(result.message + '\n\n' + (result.errors ? result.errors.join('\n') : ''));
                    }
                } catch (error) {
                    alert('Terjadi kesalahan: ' + error.message);
                } finally {
                    this.isUploading = false;
                    event.target.value = ''; // Reset file input
                }
            },
            
            toggleItemSelection(index) {
                const idx = this.selectedItems.indexOf(index);
                if (idx > -1) {
                    this.selectedItems.splice(idx, 1);
                } else {
                    this.selectedItems.push(index);
                }
            },
            
            selectAllNew() {
                this.selectedItems = this.importData.new_items.map((_, index) => index);
            },
            
            selectAll() {
                this.selectedItems = this.importData.all_items.map((_, index) => index);
            },
            
            async processImport(includeDuplicates = false) {
                const itemsToImport = includeDuplicates 
                    ? this.importData.all_items.filter((_, index) => this.selectedItems.includes(index))
                    : this.importData.new_items.filter((_, index) => this.selectedItems.includes(index));
                
                if (itemsToImport.length === 0) {
                    alert('Pilih minimal satu item untuk diimport.');
                    return;
                }
                
                try {
                    const response = await fetch('{{ route('items.import.process') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            items: itemsToImport,
                            force_duplicate: includeDuplicates
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        this.importResult = result;
                        this.previewModalOpen = false;
                        this.resultModalOpen = true;
                    } else {
                        alert('Gagal import: ' + result.message);
                    }
                } catch (error) {
                    alert('Terjadi kesalahan: ' + error.message);
                }
            },
            
            finishImport() {
                this.resultModalOpen = false;
                window.location.reload();
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
                    <button @click="importModalOpen = true" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl font-bold shadow-sm flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        <span class="hidden md:inline">Import</span>
                    </button>
                    <button @click="openAddModal()" class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold shadow-lg shadow-slate-900/10 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span class="hidden md:inline">Tambah</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-xl mb-6 flex items-center gap-3">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-xl mb-6 flex items-start gap-3">
                <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-semibold">{{ session('error') }}</span>
            </div>
        @endif

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

        <!-- Import Upload Modal -->
        <div x-show="importModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="importModalOpen" x-transition class="fixed inset-0 bg-slate-900 bg-opacity-40" @click="importModalOpen = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div x-show="importModalOpen" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <div class="bg-white px-8 py-6 border-b border-slate-100">
                        <h3 class="text-xl font-bold text-slate-900">Import Data Barang dari Excel</h3>
                    </div>
                    
                    <div class="p-8 space-y-5">
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                            <p class="text-sm text-blue-800 font-semibold mb-2">✅ Mudah! Pakai Excel sendiri:</p>
                            <ul class="text-sm text-blue-700 space-y-1 ml-4 list-disc">
                                <li><strong>Nama Barang</strong>: boleh pakai header "Nama", "Barang", "Nama Barang", "Name", dll</li>
                                <li><strong>Satuan</strong>: harus Pcs, Box, Rim, Pack, atau Unit</li>
                                <li><strong>Stok</strong>: boleh pakai header "Stok", "Stock", "Qty", dll (angka)</li>
                                <li class="text-blue-900 font-bold mt-2">Kategori otomatis terdeteksi!</li>
                            </ul>
                            <p class="text-xs text-blue-600 mt-2 italic">💡 Tidak wajib pakai template. Yang penting ada 3 kolom di atas.</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Pilih File Excel</label>
                            <input type="file" 
                                   accept=".xlsx,.xls" 
                                   @change="previewImportFile($event)"
                                   :disabled="isUploading"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 font-semibold text-slate-800 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-blue-600 file:text-white file:font-bold hover:file:bg-blue-700">
                            <p class="text-xs text-slate-500 mt-2">Format: .xlsx atau .xls | Maksimal 2MB</p>
                        </div>
                        
                        <div class="flex items-center justify-center" x-show="isUploading">
                            <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="ml-3 text-slate-600 font-semibold">Memproses file...</span>
                        </div>
                        
                        <div class="mt-8 flex gap-3 pt-4">
                            <button type="button" @click="importModalOpen = false" class="flex-1 py-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold rounded-xl transition-colors">Tutup</button>
                            <a href="{{ route('items.import.template') }}" class="flex-1 py-3 bg-slate-600 hover:bg-slate-700 text-white font-bold rounded-xl text-center transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Template (Opsional)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Import Modal -->
        <div x-show="previewModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="previewModalOpen" x-transition class="fixed inset-0 bg-slate-900 bg-opacity-40"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div x-show="previewModalOpen" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full">
                    <div class="bg-white px-8 py-6 border-b border-slate-100">
                        <h3 class="text-xl font-bold text-slate-900">Preview Data Import</h3>
                        <div class="mt-3 flex gap-4 text-sm" x-show="importData">
                            <span class="text-slate-600">Total: <strong x-text="importData?.total || 0"></strong></span>
                            <span class="text-green-600">Baru: <strong x-text="importData?.new_count || 0"></strong></span>
                            <span class="text-orange-600">Duplikat: <strong x-text="importData?.duplicate_count || 0"></strong></span>
                        </div>
                    </div>
                    
                    <div class="p-8 max-h-96 overflow-y-auto">
                        <template x-if="importData && importData.duplicate_count > 0">
                            <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-4">
                                <p class="text-sm text-orange-800 font-semibold">⚠️ Ditemukan <span x-text="importData.duplicate_count"></span> data duplikat!</p>
                                <p class="text-xs text-orange-700 mt-1">Data yang ditandai merah sudah ada dalam sistem. Anda dapat memilih untuk tetap mengimport atau melewatinya.</p>
                            </div>
                        </template>
                        
                        <div class="mb-4 flex gap-2">
                            <button @click="selectAllNew()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg">Pilih Semua Baru</button>
                            <button @click="selectAll()" class="px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white text-sm font-bold rounded-lg">Pilih Semua</button>
                        </div>
                        
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 border-b-2 border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-bold text-slate-600">Pilih</th>
                                    <th class="px-4 py-3 text-left font-bold text-slate-600">Nama Barang</th>
                                    <th class="px-4 py-3 text-left font-bold text-slate-600">Satuan</th>
                                    <th class="px-4 py-3 text-left font-bold text-slate-600">Stok</th>
                                    <th class="px-4 py-3 text-left font-bold text-slate-600">Kategori</th>
                                    <th class="px-4 py-3 text-left font-bold text-slate-600">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in importData?.all_items || []" :key="index">
                                    <tr :class="item.is_duplicate ? 'bg-red-50' : 'bg-white'" class="border-b border-slate-100">
                                        <td class="px-4 py-3">
                                            <input type="checkbox" 
                                                   :checked="selectedItems.includes(index)" 
                                                   @change="toggleItemSelection(index)"
                                                   class="w-4 h-4 text-blue-600 rounded">
                                        </td>
                                        <td class="px-4 py-3 font-semibold" x-text="item.name"></td>
                                        <td class="px-4 py-3" x-text="item.unit"></td>
                                        <td class="px-4 py-3" x-text="item.stock"></td>
                                        <td class="px-4 py-3">
                                            <span class="text-xs bg-slate-100 px-2 py-1 rounded font-semibold" x-text="item.category"></span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <template x-if="item.is_duplicate">
                                                <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded font-bold">Duplikat</span>
                                            </template>
                                            <template x-if="!item.is_duplicate">
                                                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded font-bold">Baru</span>
                                            </template>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="bg-slate-50 px-8 py-4 border-t border-slate-200 flex gap-3">
                        <button @click="previewModalOpen = false; importModalOpen = true" class="flex-1 py-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold rounded-xl">Kembali</button>
                        <button @click="processImport(false)" class="flex-1 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl">Import Data Baru</button>
                        <button @click="processImport(true)" class="flex-1 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl">Import Semua</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Result Modal -->
        <div x-show="resultModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="resultModalOpen" x-transition class="fixed inset-0 bg-slate-900 bg-opacity-40"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                
                <div x-show="resultModalOpen" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 px-8 py-6">
                        <div class="flex items-center gap-3 text-white">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div>
                                <h3 class="text-xl font-bold">Import Berhasil!</h3>
                                <p class="text-green-100 text-sm" x-text="importResult?.message"></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-8">
                        <div class="space-y-3" x-show="importResult">
                            <div class="flex justify-between items-center p-4 bg-green-50 rounded-xl">
                                <span class="text-slate-700 font-semibold">Data Diimport:</span>
                                <span class="text-2xl font-bold text-green-600" x-text="importResult?.imported || 0"></span>
                            </div>
                            <div class="flex justify-between items-center p-4 bg-slate-50 rounded-xl" x-show="importResult && importResult.skipped > 0">
                                <span class="text-slate-700 font-semibold">Data Dilewati:</span>
                                <span class="text-2xl font-bold text-slate-600" x-text="importResult?.skipped || 0"></span>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button @click="finishImport()" class="w-full py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl">Selesai</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
