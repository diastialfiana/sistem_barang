<x-app-layout>
    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Tambah User Baru</h2>

        <form action="{{ route('users.store') }}" method="POST" x-data="{ role: '' }">
            @csrf

            <!-- Name -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
                <input type="text" name="name" class="w-full border-gray-300 rounded shadow-sm" required>
            </div>

            <!-- NIP -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">NIP (Nomor Induk Pegawai)</label>
                <input type="text" name="nip" class="w-full border-gray-300 rounded shadow-sm" required>
            </div>

            <!-- Email (Optional now) -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" class="w-full border-gray-300 rounded shadow-sm" required>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" name="password" class="w-full border-gray-300 rounded shadow-sm" required>
            </div>

            <!-- Role -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Role</label>
                <select name="role" x-model="role" class="w-full border-gray-300 rounded shadow-sm" required>
                    <option value="">Pilih Role...</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Conditional Fields -->
            <div x-show="role !== 'super_admin'" x-transition>
                <!-- Branch -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Cabang (Wajib untuk User/SPV/KA)</label>
                    <select name="branch_id" class="w-full border-gray-300 rounded shadow-sm">
                        <option value="">Pilih Cabang...</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Job Title -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Jabatan</label>
                    <input type="text" name="job_title" class="w-full border-gray-300 rounded shadow-sm" placeholder="Contoh: Staff Gudang">
                </div>

                <!-- Company -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Perusahaan</label>
                    <input type="text" name="company" class="w-full border-gray-300 rounded shadow-sm" placeholder="Contoh: PT Trans, Bank Mega">
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 font-bold">
                    Simpan User
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
