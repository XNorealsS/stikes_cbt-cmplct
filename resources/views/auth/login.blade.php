@extends('layouts.app')

@section('title', 'Login - E-Learning STIKesMu')

@section('content')
<div class="flex min-h-screen">
    <div class="relative hidden lg:flex lg:w-1/2 flex-col justify-between p-12 text-white overflow-hidden">
        <!-- Background Image Layer -->
        <div class="absolute inset-0">
            <img src="https://siakad.stikeslhokseumawe.ac.id/bg.jpeg"
                 class="w-full h-full object-cover opacity-50"
                 alt="bg">
        </div>

        <!-- Optional Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-700 via-emerald-800 to-emerald-950 opacity-80"></div>

        <!-- Content -->
        <div class="relative z-10">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg">
                    <img src="https://siakad.stikeslhokseumawe.ac.id/logo.png" alt="Logo">
                </div>
                <span class="text-xl font-bold tracking-tight">E-Learning STIKesMu</span>
            </div>
        </div>

        <div class="space-y-6 relative z-10">
            <h1 style="font-size: 2.25rem; font-weight: bold; color: #fff; line-height: 1.2; margin: 0;">
                Selamat Datang di<br>
                Portal E-Learning &amp; CBTMu
            </h1>

            <p class="text-lg text-emerald-100/80">
                STIKes Muhammadiyah Lhokseumawe — Platform resmi E-Learning dan Computer Based Test (CBTMu) untuk mendukung kegiatan pembelajaran, tugas kuliah, dan pelaksanaan ujian secara digital dalam satu akses.
            </p>
            <div class="flex gap-8 pt-4"></div>
        </div>

        <p class="text-xs text-emerald-300/50 relative z-10">&copy; 2026 STIKesMu Lhokseumawe. All rights reserved.</p>
    </div>

    <div class="flex w-full flex-col items-center justify-center bg-slate-50 px-6 py-12 lg:w-1/2">
        <div class="mb-8 flex items-center gap-3 lg:hidden">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg">
                <img src="https://siakad.stikeslhokseumawe.ac.id/logo.png" alt="Logo">
            </div>
            <span class="text-xl font-bold text-slate-800">E-Learning STIKesMu</span>
        </div>

        <div class="w-full max-w-md">
            <div class="space-y-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Masuk ke Akun</h2>
                    <p class="mt-1 text-sm text-slate-500">Masukkan username dan password untuk melanjutkan</p>
                </div>

                <form id="login-form" class="space-y-5">
                    @csrf
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-slate-700" for="login">
                            Username
                        </label>
                        <input class="block w-full rounded-lg border-slate-300 px-4 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-green-500 focus:ring-green-500" id="login" type="text" name="login" required autofocus placeholder="Masukkan username">
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-slate-700" for="password">
                            Password
                        </label>
                        <input class="block w-full rounded-lg border-slate-300 px-4 py-2.5 text-sm text-slate-800 shadow-sm placeholder:text-slate-400 focus:border-green-500 focus:ring-green-500" id="password" type="password" name="password" required placeholder="Masukkan password">
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label for="remember" class="flex cursor-pointer items-center gap-2">
                            <input id="remember" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-green-600 focus:ring-green-500" name="remember">
                            <span class="text-sm text-slate-600">Ingat saya</span>
                        </label>
                    </div>

                    <button type="submit" id="btn-submit" class="inline-flex items-center rounded-md border border-transparent bg-green-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-green-800 focus:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 active:bg-green-900 w-full justify-center py-2.5 text-sm font-semibold tracking-wide">
                        Masuk
                    </button>
                </form>

                <p class="text-center text-sm text-slate-500">
                    Belum punya akun? Silahkan Hubungi admin untuk mendapatkan akses.
                </p>
            </div>
        </div>

        <p class="mt-8 text-center text-xs text-slate-400 lg:hidden">&copy; 2026 STIKesMu Lhokseumawe</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('login-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btnSubmit = document.getElementById('btn-submit');
        const originalText = btnSubmit.innerHTML;
        
        // Disable button & show processing text
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `Memproses...`;

        const loginVal = document.getElementById('login').value;
        const passwordVal = document.getElementById('password').value;
        const rememberVal = document.getElementById('remember').checked;

        axios.post('/login', {
            login: loginVal,
            password: passwordVal,
            remember: rememberVal
        })
        .then(response => {
            if (response.data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Login Berhasil',
                    text: response.data.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    window.location.href = response.data.redirect;
                });
            }
        })
        .catch(error => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = originalText;
            
            const message = error.response && error.response.data && error.response.data.message
                ? error.response.data.message
                : 'Username/Email atau password salah.';
            
            showError(message);
        });
    });
</script>
@endsection
