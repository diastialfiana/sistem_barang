<x-app-layout>
    <div class="max-w-5xl mx-auto">
        <!-- Header & Status -->
        <div class="bg-white shadow rounded-lg p-6 mb-6 flex justify-between items-start">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Request #{{ $request->code }}</h1>
                <p class="text-gray-500 text-sm">Dibuat pada {{ \Carbon\Carbon::parse($request->request_date)->setTimezone('Asia/Jakarta')->format('d F Y') }}</p>
                <div class="mt-2">
                    <span class="font-semibold text-gray-700">Requester:</span> {{ $request->user->name }} ({{ $request->user->job_title }}) <br>
                    @if($request->user->company)
                        <span class="font-semibold text-gray-700">Company:</span> {{ $request->user->company }} <br>
                    @endif
                    <span class="font-semibold text-gray-700">Cabang:</span> {{ $request->branch->name }}
                </div>
            </div>
            <div class="text-right">
                @if(Auth::user()->hasAnyRole(['super_admin', 'admin_1', 'admin_2']) || Auth::id() == $request->user_id)
                    <div style="margin-bottom: 30px;">
                        <a href="{{ route('requests.export', ['request' => $request->id, 'type' => 'pdf']) }}" class="text-xs bg-red-600 text-white px-2 py-1 rounded hover:bg-red-700 mr-1"> Download PDF</a>
                        <a href="{{ route('requests.export', ['request' => $request->id, 'type' => 'excel']) }}" class="text-xs bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700">Download Excel</a>
                    </div>
                @endif
                <span class="px-3 py-1 text-sm font-bold rounded-full 
                    {{ $request->status == 'approved' ? 'bg-green-100 text-green-800' : 
                      ($request->status == 'rejected' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                    {{ strtoupper(str_replace('_', ' ', $request->status)) }}
                </span>
                @if($request->rejection_reason)
                    <p class="text-red-600 text-sm mt-2 max-w-xs">Alasan Penolakan: {{ $request->rejection_reason }}</p>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
            <h3 class="px-6 py-4 bg-gray-50 font-bold border-b border-gray-200">Daftar Barang</h3>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Satuan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($request->items as $item)
                        <tr>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $item->item->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $item->item->unit }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $item->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Approval Timeline -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h3 class="font-bold text-lg mb-4">Riwayat Approval</h3>
            <div class="border-l-2 border-gray-200 ml-3 space-y-6 pl-6 relative">
                @foreach($request->approvals as $approval)
                    <div class="relative">
                        <span class="absolute -left-[31px] bg-{{ $approval->status == 'approved' ? 'green' : 'red' }}-500 h-4 w-4 rounded-full border-2 border-white"></span>
                        <p class="text-sm font-bold">{{ strtoupper($approval->stage) }} - {{ $approval->approver->name }}</p>
                        <p class="text-xs text-gray-500">{{ $approval->signed_at ? \Carbon\Carbon::parse($approval->signed_at)->setTimezone('Asia/Jakarta')->format('d M Y H:i') : '' }}</p>
                        <p class="font-semibold text-{{ $approval->status == 'approved' ? 'green' : 'red' }}-600 text-sm">
                            {{ ucfirst($approval->status) }}
                        </p>
                    </div>
                @endforeach
                
                @if($request->approvals->isEmpty())
                    <p class="text-sm text-gray-500 italic">Belum ada data approval.</p>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-4 justify-end mb-10">
            <!-- Edit/Delete for Staff (Requester) - Restrict to pending only -->
            @if(Auth::id() == $request->user_id && !in_array($request->status, ['approved', 'rejected']))
                <form action="{{ route('requests.destroy', $request->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus request ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 text-white px-6 py-2 rounded shadow hover:bg-red-600 font-bold">
                        Hapus Request
                    </button>
                </form>
                <a href="{{ route('requests.edit', $request->id) }}" class="bg-yellow-500 text-white px-6 py-2 rounded shadow hover:bg-yellow-600 font-bold flex items-center">
                    Edit Request
                </a>
            @endif

            <!-- Edit/Delete for SPV (Admin 1) - Restrict to pending only -->
            @if(Auth::user()->hasRole('admin_1') && Auth::user()->branch_id == $request->branch_id && !in_array($request->status, ['approved', 'rejected']))
                <form action="{{ route('requests.destroy', $request->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus request ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 text-white px-6 py-2 rounded shadow hover:bg-red-600 font-bold">
                        Hapus Request
                    </button>
                </form>
                <a href="{{ route('requests.edit', $request->id) }}" class="bg-yellow-500 text-white px-6 py-2 rounded shadow hover:bg-yellow-600 font-bold flex items-center">
                    Edit Request
                </a>
            @endif

            {{-- User submits Draft --}}
            @if($request->status == 'draft' && Auth::id() == $request->user_id)
                <form action="{{ route('requests.submit', $request->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded shadow hover:bg-indigo-700 font-bold confirmation-btn">
                        Submit ke SPV
                    </button>
                </form>
            @endif

            {{-- SPV Approval --}}
            @if($request->status == 'pending_spv' && Auth::user()->hasRole('admin_1') && Auth::user()->branch_id == $request->branch_id)
                <div class="flex gap-2">
                    <form action="{{ route('requests.approve', $request->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Approve</button>
                    </form>
                    <form id="reject-form-{{ $request->id }}" action="{{ route('requests.reject', $request->id) }}" method="POST">
                        @csrf
                        <button type="button" onclick="promptReject('reject-form-{{ $request->id }}')" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Reject</button>
                    </form>
                </div>
            @endif

            {{-- KA Approval --}}
            @if($request->status == 'pending_ka' && Auth::user()->hasRole('admin_2'))
                <div class="flex gap-2">
                    <form action="{{ route('requests.approve', $request->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Approve</button>
                    </form>
                    <form id="reject-form-{{ $request->id }}" action="{{ route('requests.reject', $request->id) }}" method="POST">
                        @csrf
                        <button type="button" onclick="promptReject('reject-form-{{ $request->id }}')" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Reject</button>
                    </form>
                </div>
            @endif

            {{-- GA Approval --}}
            @if($request->status == 'pending_ga' && Auth::user()->hasRole('super_admin'))
                <div class="flex gap-2">
                    <form action="{{ route('requests.approve', $request->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Approve (Final)</button>
                    </form>
                    <form id="reject-form-{{ $request->id }}" action="{{ route('requests.reject', $request->id) }}" method="POST">
                        @csrf
                        <button type="button" onclick="promptReject('reject-form-{{ $request->id }}')" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Reject</button>
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- Reusable Component for Approve/Reject Logic --}}
    @verbatim
    <script>
        function promptReject(formId) {
            const reason = prompt("Masukkan alasan penolakan:");
            if (reason) {
                const form = document.getElementById(formId);
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'reason';
                input.value = reason;
                form.appendChild(input);
                form.submit();
            }
        }
    </script>
    @endverbatim
</x-app-layout>
