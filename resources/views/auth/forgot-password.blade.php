<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - PT. Jasa Swadaya Utama</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex flex-col justify-center items-center p-4">

    <div class="mb-10 text-center">
        <img src="{{ asset('images/jayatama.png') }}" alt="Logo PT. Jasa Swadaya Utama" class="h-16 mx-auto mb-4 object-contain">
        <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">PT. Jasa Swadaya Utama</h1>
    </div>

    <div class="w-full max-w-md bg-white rounded-3xl shadow-[0_20px_50px_rgba(0,0,0,0.05)] border border-slate-100 overflow-hidden relative">
        <div class="h-2 bg-gradient-to-r from-yellow-400 to-orange-500 w-full"></div>

        <div class="p-10">
            <h2 class="text-2xl font-bold text-slate-900 mb-1">Lupa Password?</h2>
            <p class="text-slate-500 text-sm mb-8">Masukkan NIP Anda untuk mereset password kembali ke default (NIP).</p>
            
            @if ($errors->any())
                <div class="mb-6 bg-red-50 border border-red-100 p-4 rounded-xl flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <div>
                        <p class="text-sm text-red-700 font-bold">Terjadi Kesalahan</p>
                        <ul class="text-xs text-red-600 mt-1 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nomor Induk Pegawai (NIP)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884.666 1.623 1.5 1.908"></path></svg>
                        </span>
                        <input type="text" name="nip" value="{{ old('nip') }}" required autofocus
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:border-yellow-500 focus:ring focus:ring-yellow-100 transition-all text-slate-800 font-semibold placeholder-slate-400"
                            placeholder="Contoh: 12345678">
                    </div>
                </div>

                <button type="submit" class="w-full py-4 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl shadow-lg shadow-slate-900/10 transition-all hover:scale-[1.02] active:scale-[0.98]">
                    Reset Password ke NIP
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-800">
                    &larr; Kembali ke halaman login
                </a>
            </div>
        </div>
    </div>
</body>
</html>
