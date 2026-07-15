<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'E-Learning STIKesMu')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS (via Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Axios -->
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    @yield('styles')
    <style>
        /* ─── Sidebar ──────────────────────────────────────────────────── */
        #main-sidebar {
            background-color: var(--sidebar-bg) !important;
            border-right: 1px solid rgba(255,255,255,0.05) !important;
        }
        #main-sidebar .brand-header {
            background-color: var(--sidebar-logo-bg) !important;
            border-bottom: 1px solid rgba(255,255,255,0.15) !important;
        }
        #main-sidebar .profile-footer {
            background-color: var(--sidebar-bg) !important;
            border-top: 1px solid rgba(255,255,255,0.15) !important;
        }

        /* Avatar circles always rounded */
        .avatar-circle {
            border-radius: var(--radius-full) !important;
        }

        /* Sidebar nav links & buttons */
        #main-sidebar a, #main-sidebar button {
            color: var(--sidebar-text) !important;
            font-size: 13px !important;
            padding: 9px 12px !important;
            border-radius: var(--radius-sm) !important;
            transition: all 0.15s ease-out;
            opacity: 0.88;
        }
        #main-sidebar a:hover, #main-sidebar button:hover {
            background-color: var(--sidebar-hover-bg) !important;
            color: var(--sidebar-active-text) !important;
            opacity: 1;
        }
        #main-sidebar a.active-link,
        #main-sidebar button.active {
            background-color: var(--sidebar-active-bg) !important;
            color: var(--sidebar-active-text) !important;
            font-weight: 600 !important;
            opacity: 1;
        }

        /* Chevron rotation */
        .chevron {
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        button.active .chevron {
            transform: rotate(180deg);
        }

        /* Collapsible submenus */
        .sidebar-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 1px solid rgba(255, 255, 255, 0.12) !important;
            margin-left: 1.25rem !important;
            padding-left: 0.5rem !important;
            margin-top: 0.25rem !important;
            margin-bottom: 0.25rem !important;
            display: block !important;
        }
        .sidebar-submenu.show { max-height: 500px; }

        /* Submenu links */
        .sidebar-submenu a {
            color: var(--sidebar-text) !important;
            padding: 0.45rem 0.75rem !important;
            font-size: 12px !important;
            border-radius: var(--radius-sm) !important;
            display: block !important;
            opacity: 0.85;
        }
        .sidebar-submenu a:hover {
            background-color: var(--sidebar-hover-bg) !important;
            color: var(--sidebar-active-text) !important;
            opacity: 1;
        }
        .sidebar-submenu a.active-link {
            background-color: var(--sidebar-active-bg) !important;
            color: var(--sidebar-active-text) !important;
            font-weight: 600 !important;
            opacity: 1;
        }

        /* Sidebar scrollbar */
        #main-sidebar *::-webkit-scrollbar { width: 3px; }
        #main-sidebar *::-webkit-scrollbar-track { background: transparent; }
        #main-sidebar *::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.15);
            border-radius: var(--radius-full);
        }
        #main-sidebar *::-webkit-scrollbar-thumb:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
        #main-sidebar nav {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.15) transparent;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex flex-col">
    <!-- CSRF Token setup for Axios -->
    <script>
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        }
    </script>

    @auth
    @if (!View::hasSection('no-nav'))
    <!-- Main Shell Container (Sidebar + Header + Content) -->
    <div class="flex min-h-screen">

        @php
            $role = auth()->user()->role;
        @endphp

        <aside
            id="main-sidebar"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            :style="sidebarReady ? '' : 'transition: none'"
            class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col -translate-x-full transform bg-[var(--sidebar-bg)] border-r border-[var(--sidebar-border)] lg:translate-x-0"
        >
            {{-- Brand --}}
            <div class="flex h-16 items-center gap-3 px-5" style="border-bottom: 1px solid rgba(255,255,255,0.15);">
                <img src="{{ asset('logo.png') }}" onerror="this.onerror=null;this.src='https://siakad.stikeslhokseumawe.ac.id/logo.png';" alt="Logo" width="32" height="32" loading="eager" fetchpriority="high" decoding="async" class="block h-8 w-8 object-contain">
                <div>
                    <span class="block text-sm font-bold text-[var(--sidebar-active-text)] leading-tight">STIKesMu</span>
                    <span class="block text-[10px] text-[var(--sidebar-text)] tracking-wider uppercase font-semibold">Lhokseumawe</span>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                @include('partials.sidebar-all')
            </nav>

            {{-- User info --}}
            <div class="px-4 py-4" style="border-top: 1px solid rgba(255,255,255,0.15);">
                <div class="flex items-center gap-3">
                    <div class="avatar-circle flex h-8 w-8 items-center justify-center bg-[var(--sidebar-active-bg)] text-xs font-semibold text-[var(--sidebar-active-text)]" style="border: 1px solid rgba(255,255,255,0.1);">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="truncate text-xs font-semibold text-[var(--sidebar-active-text)]">{{ auth()->user()->name }}</p>
                        <p class="truncate text-[10px] text-[var(--sidebar-text)] font-medium">{{ ucfirst($role) }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Sidebar Mobile Overlay -->
        <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 z-30 bg-black/40 hidden lg:hidden"></div>

        <!-- Right Side Container (Header + Main Content) -->
        <div class="flex-grow flex flex-col min-w-0 lg:ml-64">
            <header class="topbar px-4 sm:px-6 flex items-center justify-between z-25 relative">
                <!-- Mobile Search Overlay (covers full header width on mobile) -->
                <div id="mobile-search-overlay" class="hidden absolute inset-0 bg-white z-50 md:hidden px-4 flex items-center gap-2">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 text-sm shrink-0"></i>
                    <input type="text"
                           id="mobile-search-input"
                           onkeydown="handleHeaderSearch(event)"
                           placeholder="Ketik untuk mencari..."
                           class="flex-1 py-2 bg-transparent border-none outline-none text-sm text-slate-800 placeholder-slate-400">
                    <button type="button" onclick="closeMobileSearch()" class="shrink-0 w-8 h-8 flex items-center justify-center text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition">
                        <i class="fa-solid fa-xmark text-base"></i>
                    </button>
                </div>
                
                <!-- Left: Hamburger Toggle & Breadcrumbs / Title -->
                <div class="flex items-center gap-2 max-w-[55%] overflow-hidden shrink-0">
                    <button type="button" onclick="toggleSidebar()" class="text-gray-500 hover:text-gray-800 lg:hidden transition mr-1 shrink-0">
                        <i class="fa-solid fa-bars text-base"></i>
                    </button>
                    @php
                        $segments = request()->segments();
                        $breadcrumbs = [];
                        $url = '';
                        foreach ($segments as $segment) {
                            $url .= '/' . $segment;
                            $name = str_replace('-', ' ', $segment);
                            $name = ucwords($name);
                            if (strtolower($name) === 'dosen') $name = 'Dosen';
                            if (strtolower($name) === 'admin') $name = 'Admin';
                            if (strtolower($name) === 'mahasiswa') $name = 'Mahasiswa';
                            if (strtolower($name) === 'bank soal') $name = 'Bank Soal';
                            if (strtolower($name) === 'jadwal ujian') $name = 'Jadwal Ujian';
                            if (strtolower($name) === 'rekap nilai') $name = 'Rekap Nilai';
                            if (strtolower($name) === 'analisis soal') $name = 'Analisis Soal';
                            $breadcrumbs[] = [
                                'name' => $name,
                                'url' => $url
                            ];
                        }
                        $currentTitle = end($breadcrumbs)['name'] ?? 'Dashboard';
                    @endphp
                    <!-- Mobile page title -->
                    <span class="sm:hidden text-slate-800 font-bold text-sm truncate whitespace-nowrap">{{ $currentTitle }}</span>
                    
                    <!-- Desktop breadcrumbs -->
                    <nav class="hidden sm:flex text-[10px] font-bold text-gray-400 uppercase tracking-wider overflow-x-auto scrollbar-none" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 sm:space-x-1.5 whitespace-nowrap">
                            <li class="inline-flex items-center whitespace-nowrap font-semibold">
                                <span class="text-gray-400">STIKESMU</span>
                            </li>
                            @foreach ($breadcrumbs as $crumb)
                                <li class="inline-flex items-center whitespace-nowrap">
                                    <i class="fa-solid fa-chevron-right text-[8px] text-gray-300 mx-1"></i>
                                    @if ($loop->last)
                                        <span class="text-primary font-black">{{ $crumb['name'] }}</span>
                                    @else
                                        <a href="{{ $crumb['url'] }}" class="text-gray-400 hover:text-primary transition">{{ $crumb['name'] }}</a>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </nav>
                </div>

                <!-- Middle: Search Bar (Admin & Dosen - Desktop only) -->
                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'dosen')
                <div class="hidden md:flex items-center flex-1 max-w-md lg:max-w-lg mx-6 relative" id="header-search-wrapper">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                    <input type="text"
                           id="header-search-input"
                           onkeydown="handleHeaderSearch(event)"
                           placeholder="Cari nama, NIM, NIDN, soal..."
                           value="{{ request('search') }}"
                           class="w-full py-2.5 bg-slate-50 border border-slate-200 text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:border-green-600 focus:outline-none transition rounded-lg"
                           style="padding-left: 2.5rem !important; padding-right: 1rem !important;">
                </div>
                @endif

                <!-- Right: Action Bar (Profile, Notifications & Mobile Search Trigger) -->
                <div class="flex items-center gap-2 sm:gap-4 ml-auto">
                    <!-- Mobile Search Trigger Button -->
                    @if(auth()->user()->role === 'admin' || auth()->user()->role === 'dosen')
                    <button type="button" onclick="toggleMobileSearch()" id="mobile-search-btn" class="md:hidden w-9 h-9 flex items-center justify-center text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition cursor-pointer">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                    @endif
                    <!-- Live Digital Clock -->
                    <div class="hidden sm:flex font-mono text-[10px] font-semibold text-slate-500 bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-md items-center gap-1.5">
                        <i class="fa-regular fa-clock text-slate-400 text-[9px]"></i>
                        <span id="live-digital-clock">00:00:00</span>
                    </div>

                    @auth
                    @if (auth()->user()->isMahasiswa())
                        @php
                            $unreadNotifications = \App\Models\Notification::where('user_id', auth()->id())
                                ->where('is_read', false)
                                ->orderBy('created_at', 'desc')
                                ->get();
                        @endphp
                        <div class="relative" id="notification-dropdown-wrapper">
                            <button type="button" onclick="toggleNotificationDropdown()" class="text-gray-500 hover:text-gray-800 transition relative p-1.5 rounded-full hover:bg-gray-150 cursor-pointer flex items-center justify-center">
                                <i class="fa-solid fa-bell text-base"></i>
                                @if(count($unreadNotifications) > 0)
                                    <span class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-[8px] font-bold text-white rounded-full flex items-center justify-center animate-pulse px-1">
                                        {{ count($unreadNotifications) }}
                                    </span>
                                @endif
                            </button>
                            <div id="notification-dropdown" class="absolute right-0 mt-2 w-80 bg-white border border-slate-200 rounded-xl shadow-lg py-2 hidden z-50" style="border-radius: var(--radius-xl); box-shadow: var(--shadow-lg);">
                                <div class="px-4 py-2 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                                    <span class="font-bold text-xs text-gray-700">Notifikasi</span>
                                    @if(count($unreadNotifications) > 0)
                                        <button onclick="markAllNotificationsRead()" class="text-[10px] text-emerald-700 font-bold hover:underline">Tandai semua dibaca</button>
                                    @endif
                                </div>
                                <div class="max-h-64 overflow-y-auto divide-y divide-gray-100">
                                    @forelse($unreadNotifications as $notif)
                                        <div class="p-3 text-xs hover:bg-gray-50 transition flex flex-col gap-1 text-left">
                                            <div class="flex justify-between items-start gap-2">
                                                <span class="font-bold text-gray-900 leading-tight">{{ $notif->title }}</span>
                                                <span class="text-[8px] text-gray-400 font-medium whitespace-nowrap">{{ $notif->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-gray-600 leading-normal text-[11px]">{{ $notif->body }}</p>
                                        </div>
                                    @empty
                                        <div class="p-4 text-center text-gray-400 text-xs">
                                            Tidak ada notifikasi baru.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endif
                    @endauth

                    <!-- Topbar Role Badge -->
                    <span class="topbar-role-badge uppercase tracking-wider hidden md:inline-block">
                        {{ auth()->user()->role === 'admin' ? 'Superadmin' : auth()->user()->role }}
                    </span>

                    <!-- Profile Dropdown Widget (SIAKAD style) -->
                    <div class="relative" id="profile-dropdown-wrapper">
                        <button type="button" onclick="toggleProfileDropdown()" class="flex items-center gap-2 px-2.5 py-1.5 hover:bg-slate-50 transition text-left cursor-pointer border border-transparent hover:border-slate-200" style="border-radius: var(--radius-md);">
                            <div class="avatar-circle h-7 w-7 bg-emerald-700 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="hidden sm:block text-[11px]">
                                <span class="block font-bold text-slate-800 leading-none">{{ auth()->user()->name }}</span>
                                <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">{{ auth()->user()->role === 'admin' ? 'Superadmin' : auth()->user()->role }}</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-slate-400 text-[8px] ml-1"></i>
                        </button>

                        <div id="profile-dropdown" class="absolute right-0 mt-2 w-52 bg-white border border-slate-200 py-1.5 hidden z-50" style="border-radius: var(--radius-xl); box-shadow: var(--shadow-lg);">
                            <div class="px-4 py-2 border-b border-gray-100 sm:hidden">
                                <span class="block font-bold text-gray-800 text-xs truncate">{{ auth()->user()->name }}</span>
                                <span class="block text-[9px] text-gray-400 font-bold uppercase tracking-wider truncate mt-0.5">{{ auth()->user()->role }}</span>
                            </div>
                            <!-- Logout Action inside dropdown -->
                            <a href="#" onclick="event.preventDefault(); confirmLogout();" class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-red-650 hover:bg-red-50 transition">
                                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                <span>Keluar</span>
                            </a>
                        </div>
                    </div>

                    <!-- Hidden Logout Form -->
                    <form action="{{ route('logout') }}" method="POST" id="logout-form" class="hidden">
                        @csrf
                    </form>
                </div>
            </header>

            <!-- Workspace Scroll Area -->
            <main class="flex-grow p-4 md:p-6 overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>
    @endif
    @endauth

    <!-- For Guest/No-Nav Views (Login & Exam Room) -->
    @if (!auth()->check() || View::hasSection('no-nav'))
        <main class="flex-grow flex flex-col">
            @yield('content')
        </main>
    @endif

    <!-- Global Javascript Helpers -->
    <script>
        // Live Clock logic
        function updateLiveClock() {
            const clockEl = document.getElementById('live-digital-clock');
            if (!clockEl) return;
            const now = new Date();
            const pad = (n) => n < 10 ? '0' + n : n;
            clockEl.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
        }
        setInterval(updateLiveClock, 1000);
        updateLiveClock();

        // Sidebar mobile toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('main-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            if (sidebar && overlay) {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
        }

        // Header search submit
        function handleHeaderSearch(e) {
            if (e.key === 'Enter') {
                const query = e.target.value.trim();
                const role = "{{ auth()->check() ? auth()->user()->role : '' }}";
                if (role === 'admin') {
                    window.location.href = `/admin/users?search=${encodeURIComponent(query)}`;
                } else if (role === 'dosen') {
                    window.location.href = `/dosen/bank-soal?search=${encodeURIComponent(query)}`;
                }
            }
        }

        // Toggle mobile search overlay
        function toggleMobileSearch() {
            const overlay = document.getElementById('mobile-search-overlay');
            if (!overlay) return;
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            setTimeout(() => {
                const input = document.getElementById('mobile-search-input');
                if (input) input.focus();
            }, 50);
        }

        function closeMobileSearch() {
            const overlay = document.getElementById('mobile-search-overlay');
            if (overlay) {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }
        }

        // Close mobile search when clicking outside
        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('header-search-wrapper');
            const mobileContainer = document.getElementById('mobile-search-container');
            if (wrapper && mobileContainer && !wrapper.contains(e.target)) {
                mobileContainer.classList.add('hidden');
            }
        });

        // Profile dropdown toggle
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profile-dropdown');
            if (dropdown) dropdown.classList.toggle('hidden');
        }

        function confirmLogout() {
            Swal.fire({
                title: 'Konfirmasi Keluar',
                text: 'Apakah Anda yakin ingin keluar dari sistem?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#14532d',
                cancelButtonColor: '#EF4444',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }

        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: message,
                confirmButtonColor: '#14532d'
            });
        }

        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: message,
                confirmButtonColor: '#14532d'
            });
        }

        function toggleNotificationDropdown() {
            const dropdown = document.getElementById('notification-dropdown');
            if (dropdown) dropdown.classList.toggle('hidden');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            // Notifications
            const notifWrapper = document.getElementById('notification-dropdown-wrapper');
            const notifDropdown = document.getElementById('notification-dropdown');
            if (notifWrapper && notifDropdown && !notifWrapper.contains(e.target)) {
                notifDropdown.classList.add('hidden');
            }

            // Profile
            const profileWrapper = document.getElementById('profile-dropdown-wrapper');
            const profileDropdown = document.getElementById('profile-dropdown');
            if (profileWrapper && profileDropdown && !profileWrapper.contains(e.target)) {
                profileDropdown.classList.add('hidden');
            }

            // Mobile Search
            const searchWrapper = document.getElementById('header-search-wrapper');
            const searchContainer = document.getElementById('header-search-container');
            if (searchWrapper && searchContainer && !searchWrapper.contains(e.target) && window.innerWidth < 768) {
                searchContainer.classList.add('hidden');
                searchContainer.classList.remove('flex');
            }
        });

        function markAllNotificationsRead() {
            axios.post("/mahasiswa/notifications/mark-read")
                .then(res => {
                    if (res.data.success) {
                        window.location.reload();
                    }
                });
        }

        // Collapsible group toggle
        function toggleCollapsible(grupId) {
            const submenu = document.getElementById(`submenu-${grupId}`);
            const btn = document.getElementById(`btn-${grupId}`);
            if (submenu && btn) {
                submenu.classList.toggle('show');
                btn.classList.toggle('active');
            }
        }

        // Auto open active collapsible menu on page load
        document.addEventListener('DOMContentLoaded', function() {
            const activeLink = document.querySelector('.sidebar-submenu a.active-link');
            if (activeLink) {
                const submenu = activeLink.closest('.sidebar-submenu');
                if (submenu) {
                    submenu.classList.add('show');
                    const grupId = submenu.id.replace('submenu-', '');
                    const btn = document.getElementById(`btn-${grupId}`);
                    if (btn) {
                        btn.classList.add('active');
                    }
                }
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
