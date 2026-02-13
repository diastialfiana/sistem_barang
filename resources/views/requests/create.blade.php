<x-app-layout>
    <div class="max-w-6xl mx-auto" x-data="requestForm()">
        

        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-slate-800 tracking-tight">
                Buat Request <span class="text-blue-600">Barang</span>
            </h1>
            <p class="mt-2 text-slate-500 font-medium">
                Isi form berikut untuk mengajukan permintaan barang operasional.
            </p>
        </div>

        <div class="bg-white rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.04)] border border-slate-100 overflow-hidden relative">
            <div class="h-1 bg-gradient-to-r from-blue-500 to-indigo-600 w-full"></div>

            <form action="{{ route('requests.store') }}" method="POST" class="p-8 md:p-10 relative z-10">
                @csrf
                
                @if ($errors->any())
                    <div class="mb-8 bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        <div>
                            <p class="text-sm text-red-700 font-bold">Validasi Gagal</p>
                            <ul class="text-xs text-red-600 mt-1 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10 pb-8 border-b border-slate-50">
                    <div class="md:col-span-1">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Tanggal Request</label>
                        <input type="date" name="request_date" value="{{ old('request_date', date('Y-m-d')) }}" 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-blue-500 focus:ring focus:ring-blue-100 transition-all text-slate-800 font-semibold"
                            required>
                    </div>
                     <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Divisi / Cabang</label>
                        <input type="text" readonly value="{{ optional(Auth::user()->branch)->name ?? 'Dept. Operasional' }}" 
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-500 font-semibold cursor-not-allowed">
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <span class="bg-blue-100 text-blue-600 p-1.5 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </span>
                            Detail Barang
                        </h3>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(row, index) in rows" :key="row.id">
                            <div class="bg-slate-50/50 rounded-2xl p-6 border border-slate-200 shadow-sm relative group hover:border-blue-200 transition-colors">
                               
                                <button type="button" @click="removeRow(index)" x-show="rows.length > 1"
                                    class="absolute -top-3 -right-3 bg-white text-red-500 hover:text-red-700 rounded-full p-2 shadow-sm border border-slate-100 transition-transform hover:scale-110">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                                   
                                    <div class="md:col-span-4">
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Nama Barang</label>
                                        <div class="relative">
                                            <select :name="`items[${index}][item_name]`" 
                                                x-model="row.item_name"
                                                @change="updateStock(index)"
                                                class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 pr-8 focus:border-blue-500 focus:ring focus:ring-blue-100 text-slate-800 font-semibold appearance-none"
                                                required>
                                                <option value="" disabled selected>Pilih Barang...</option>
                                                @foreach($items as $item)
                                                    <option value="{{ $item->name }}">{{ $item->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Stok</label>
                                        <div class="relative">
                                            <input type="text" readonly :value="row.stockDisplay"
                                                class="w-full bg-slate-100 border-none rounded-xl text-slate-500 font-mono text-sm py-3 px-4 shadow-inner cursor-not-allowed">
                                            <div class="absolute inset-y-0 right-3 flex items-center">
                                                <span class="text-xs font-bold text-slate-400" x-text="row.unit"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Qty</label>
                                        <input type="number" :name="`items[${index}][quantity]`" 
                                            x-model="row.quantity"
                                            :max="row.maxStock"
                                            min="1"
                                            required
                                            class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:border-blue-500 focus:ring focus:ring-blue-100 text-slate-900 font-bold">
                                            
                                        <span x-show="row.quantity > row.maxStock" class="text-xs text-red-500 font-bold mt-1 block">
                                            Melebihi stok!
                                        </span>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Kirim Tgl</label>
                                        <input type="date" :name="`items[${index}][due_date]`" 
                                            class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:border-blue-500 focus:ring focus:ring-blue-100 text-slate-700 text-sm"
                                            required>
                                    </div>
                                  
                                     <div class="md:col-span-2">
                                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Ket.</label>
                                        <input type="text" :name="`items[${index}][notes]`" 
                                            placeholder="Opsional"
                                            class="w-full bg-white border border-slate-200 rounded-xl px-4 py-3 focus:border-blue-500 focus:ring focus:ring-blue-100 text-slate-700 text-sm">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <button type="button" @click="addRow()" 
                        class="w-full py-4 border-2 border-dashed border-slate-200 rounded-2xl text-slate-400 font-bold hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition-all duration-300 flex justify-center items-center gap-2 group">
                        <span class="bg-slate-100 text-slate-400 rounded-lg p-1 group-hover:bg-blue-200 group-hover:text-blue-700 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4" />
                            </svg>
                        </span>
                        Tambah Item Lain
                    </button>
                </div>
                
                <div class="mt-8 pt-6 border-t border-slate-50 flex flex-col md:flex-row justify-end gap-4">
                    <button type="button" onclick="history.back()" 
                        class="px-8 py-3.5 rounded-xl text-slate-500 font-bold hover:bg-slate-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit" name="save_action" value="draft"
                        class="px-8 py-3.5 bg-white border border-slate-200 text-slate-700 rounded-xl font-bold shadow-sm hover:bg-slate-50 hover:border-slate-300 transition-all flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                        Simpan Draft
                    </button>
                    <button type="submit" name="save_action" value="submit"
                        class="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-600/30 transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                        <span>Submit Request</span>
                         <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function requestForm() {
            return {
                availableItems: @json($items),
                rows: [
                    { id: Date.now(), item_name: '', quantity: 1, stockDisplay: '-', maxStock: 9999, unit: '' }
                ],
                
                addRow() {
                    this.rows.push({ 
                        id: Date.now(), 
                        item_name: '', 
                        quantity: 1, 
                        stockDisplay: '-',
                        maxStock: 9999,
                        unit: '' 
                    });
                },
                
                removeRow(index) {
                    this.rows.splice(index, 1);
                },

                updateStock(index) {
                    const row = this.rows[index];
                    const selectedItem = this.availableItems.find(i => i.name.toLowerCase() === row.item_name.toLowerCase());
                    
                    if (selectedItem) {
                        row.stockDisplay = selectedItem.stock;
                        row.maxStock = selectedItem.stock;
                        row.unit = selectedItem.unit;
                        if(row.quantity > selectedItem.stock) {
                
                        }
                    } else {
                        row.stockDisplay = '-'; 
                        row.maxStock = 999999;
                        row.unit = '';
                    }
                }
            }
        }
    </script>
</x-app-layout>
