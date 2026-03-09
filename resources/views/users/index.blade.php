<x-app-layout>
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Manajemen User (Super Admin)</h2>
        <a href="{{ route('users.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
            + Tambah User
        </a>
    </div>
    
    <!-- ATK Summary Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        @foreach($atkSummary as $key => $data)
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-all">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-{{ $key == 'current_month' ? 'blue' : 'indigo' }}-50 text-{{ $key == 'current_month' ? 'blue' : 'indigo' }}-600 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider">Total ATK {{ $data['label'] }}</h3>
                    <p class="text-2xl font-black text-slate-800 tracking-tight">Rp. {{ number_format($data['total_price'], 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="flex items-center justify-between text-sm py-2 px-3 bg-slate-50 rounded-lg">
                <span class="text-slate-600 font-medium">Banyak Barang (Qty):</span>
                <span class="font-bold text-slate-800">{{ number_format($data['total_qty'], 0, ',', '.') }} Item</span>
            </div>
            
            @php
                // Use the raw month and year passed from controller
                $m = $data['month'];
                $y = $data['year'];
            @endphp
            <div class="mt-4 pt-4 border-t border-slate-50">
                <a href="{{ route('recaps.show', ['year' => $y, 'month' => $m]) }}" class="text-{{ $key == 'current_month' ? 'blue' : 'indigo' }}-600 hover:text-{{ $key == 'current_month' ? 'blue' : 'indigo' }}-800 text-xs font-bold flex items-center gap-1 group/link">
                    Lihat Detail Rekap
                    <svg class="w-3 h-3 transform group-hover/link:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <form method="GET" action="{{ route('users.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama/Email..." class="border-gray-300 rounded-md">
            
            <select name="role" class="border-gray-300 rounded-md">
                <option value="">Semua Role</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                    </option>
                @endforeach
            </select>

            <select name="branch_id" class="border-gray-300 rounded-md">
                <option value="">Semua Cabang</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-md hover:bg-gray-700">Filter</button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cabang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jabatan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Perusahaan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($users as $user)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->nip ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->role_color }}">
                                {{ ucfirst(str_replace('_', ' ', $user->getRoleNames()->first() ?? '-')) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->branch->name ?? 'Global/Pusat' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->job_title ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $user->company ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('users.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                            
                            @if(auth()->id() !== $user->id)
                                <form action="{{ route('users.reset_password', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Reset password user ini menjadi NIP?')">
                                    @csrf
                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900 mr-3">Reset Pass</button>
                                </form>

                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin hapus user ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="px-6 py-4">
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
