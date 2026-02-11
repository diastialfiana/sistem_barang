<x-app-layout>
    <div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <h2 class="text-2xl font-bold text-gray-800">Daftar Request Barang</h2>
        
        <form method="GET" action="{{ route('requests.index') }}" class="flex items-center space-x-2">
            <select name="location_type" class="border-gray-300 rounded-md shadow-sm text-sm py-2 px-3 pr-8 focus:ring-indigo-500 focus:border-indigo-500" onchange="this.form.submit()">
                <option value="">Semua Lokasi</option>
                <option value="dalam_kota" {{ request('location_type') == 'dalam_kota' ? 'selected' : '' }}>Dalam Kota</option>
                <option value="luar_kota" {{ request('location_type') == 'luar_kota' ? 'selected' : '' }}>Luar Kota</option>
            </select>
            <input type="month" name="month" value="{{ request('month') }}" class="border-gray-300 rounded-md shadow-sm text-sm py-2 px-3 focus:ring-indigo-500 focus:border-indigo-500" onchange="this.form.submit()">
        </form>

        <div class="flex space-x-2">
            @auth
                <a href="{{ route('requests.export_pdf_list', ['location_type' => request('location_type'), 'month' => request('month')]) }}" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 text-sm">
                    PDF List
                </a>
                <a href="{{ route('requests.export_excel_list', ['location_type' => request('location_type'), 'month' => request('month')]) }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm">
                    Excel List
                </a>
                <a href="{{ route('requests.export_recap', ['location_type' => request('location_type'), 'month' => request('month')]) }}" class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 text-sm">
                    Excel Recap (Grouped)
                </a>
            @endauth
            @can('create_requests')
                <a href="{{ route('requests.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm">
                    + Buat Request
                </a>
            @endcan
        </div>
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    @unlessrole('user')
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cabang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requester</th>
                    @endunlessrole
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($requests as $request)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $request->code }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($request->request_date)->setTimezone('Asia/Jakarta')->format('d M Y') }}
                        </td>
                        @unlessrole('user')
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $request->branch->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $request->user->name }}
                            </td>
                        @endunlessrole
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'draft' => 'bg-gray-100 text-gray-800',
                                    'pending_spv' => 'bg-yellow-100 text-yellow-800',
                                    'pending_ka' => 'bg-orange-100 text-orange-800',
                                    'pending_ga' => 'bg-blue-100 text-blue-800',
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                ];
                                $statusLabels = [
                                    'draft' => 'Draft',
                                    'pending_spv' => 'Menunggu SPV',
                                    'pending_ka' => 'Menunggu KA',
                                    'pending_ga' => 'Menunggu GA',
                                    'approved' => 'Selesai',
                                    'rejected' => 'Ditolak',
                                ];
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$request->status] ?? 'bg-gray-100' }}">
                                {{ $statusLabels[$request->status] ?? ucfirst($request->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('requests.show', $request->id) }}" class="text-indigo-600 hover:text-indigo-900">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            Belum ada request.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4">
            {{ $requests->links() }}
        </div>
    </div>
</x-app-layout>
