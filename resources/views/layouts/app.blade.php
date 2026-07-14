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
        /* Sidebar Styling Overrides to align with Design System variables */
        #main-sidebar {
            background-color: var(--sidebar-bg) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
        #main-sidebar .brand-header {
            background-color: var(--sidebar-logo-bg) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
        #main-sidebar .profile-footer {
            background-color: var(--sidebar-bg) !important;
            border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
        }
        #main-sidebar a, #main-sidebar button {
            color: var(--sidebar-text) !important;
            font-size: 13px !important;
            transition: all 0.15s ease-in-out;
            opacity: 0.9;
        }
        #main-sidebar a:hover, #main-sidebar button:hover {
            background-color: var(--sidebar-hover-bg) !important;
            color: var(--sidebar-active-text) !important;
            opacity: 1;
        }
        /* Active parent link */
        #main-sidebar a.active-link {
            background-color: var(--sidebar-active-bg) !important;
            color: var(--sidebar-active-text) !important;
            font-weight: 600 !important;
            opacity: 1;
        }
        /* Collapsible submenus structure with max-height transition */
        .sidebar-submenu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-left: 1px solid rgba(255, 255, 255, 0.15) !important;
            margin-left: 1.25rem !important;
            padding-left: 0.5rem !important;
            margin-top: 0.25rem !important;
            margin-bottom: 0.25rem !important;
            display: block !important; /* overrides display:none from layout scripts */
        }
        .sidebar-submenu.show {
            max-height: 500px;
        }
        /* Submenu links specific styles */
        .sidebar-submenu a {
            color: var(--sidebar-text) !important;
            padding: 0.4rem 0.75rem !important;
            font-size: 12.5px !important;
            border-radius: var(--radius-sm) !important;
            display: block !important;
        }
        .sidebar-submenu a:hover {
            background-color: var(--sidebar-hover-bg) !important;
            color: var(--sidebar-active-text) !important;
        }
        .sidebar-submenu a.active-link {
            background-color: var(--sidebar-active-bg) !important;
            color: var(--sidebar-active-text) !important;
            font-weight: 600 !important;
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
            <div class="flex h-16 items-center gap-3 border-b border-[var(--sidebar-border)] bg-[var(--sidebar-logo-bg)] px-5">
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
            <div class="border-t border-[var(--sidebar-border)] bg-[var(--sidebar-logo-bg)] px-4 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[var(--sidebar-active-bg)] text-xs font-semibold text-[var(--sidebar-active-text)] border border-[var(--sidebar-border)]">
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
            <!-- Top Header Bar -->
            <header class="topbar px-6 flex items-center justify-between z-25 relative">
                <!-- Left: Hamburger Toggle & Breadcrumbs -->
                <div class="flex items-center gap-3">
                    <button type="button" onclick="toggleSidebar()" class="text-gray-500 hover:text-gray-800 lg:hidden transition mr-1">
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
                    @endphp
                    <nav class="flex text-[10px] font-bold text-gray-400 uppercase tracking-wider" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 sm:space-x-1.5">
                            <li class="inline-flex items-center">
                                <span class="text-gray-400">STIKESMU</span>
                            </li>
                            @foreach ($breadcrumbs as $crumb)
                                <li class="inline-flex items-center">
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

                <!-- Middle: Search Bar (Admin & Dosen) -->
                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'dosen')
                <div class="flex items-center flex-1 md:max-w-sm mx-2 md:mx-6 relative" id="header-search-wrapper">
                    <!-- Mobile Search Trigger Button -->
                    <button type="button" onclick="toggleMobileSearch()" class="md:hidden text-gray-500 hover:text-gray-800 p-1.5 rounded-full hover:bg-gray-150 flex items-center justify-center cursor-pointer">
                        <i class="fa-solid fa-magnifying-glass text-base"></i>
                    </button>
                    <!-- Search Input container -->
                    <div id="header-search-container" class="hidden md:flex items-center w-full absolute md:relative top-full left-0 right-0 mt-2 md:mt-0 bg-white md:bg-transparent p-2 md:p-0 border border-gray-200 md:border-transparent rounded-lg shadow-lg md:shadow-none z-50">
                        <i class="fa-solid fa-magnifying-glass text-gray-400 absolute left-5 md:left-3 text-[10px]"></i>
                        <input type="text" onkeydown="handleHeaderSearch(event)" placeholder="Ketik nama atau NIM/NIDN/soal..." class="w-full pl-10 md:pl-8 pr-4 py-1.5 bg-gray-50 border border-gray-250 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-emerald-700 focus:bg-white transition" value="{{ request('search') }}">
                    </div>
                </div>
                @endif

                <!-- Right: Profile Dropdown & Notifications -->
                <div class="flex items-center gap-4 ml-auto">
                    <!-- Live Digital Clock (Secondary position) -->
                    <div class="hidden sm:flex font-mono text-[10px] font-bold text-gray-500 bg-gray-50 border border-gray-250 px-2.5 py-1 rounded-[4px] items-center space-x-1.5">
                        <i class="fa-regular fa-clock text-gray-400"></i>
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
                            <div id="notification-dropdown" class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-xl shadow-xl py-2 hidden z-50">
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
                        <button type="button" onclick="toggleProfileDropdown()" class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-50 transition text-left cursor-pointer border border-transparent hover:border-gray-200">
                            <div class="h-7 w-7 rounded-full bg-emerald-700 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="hidden sm:block text-[11px]">
                                <span class="block font-bold text-gray-800 leading-none">{{ auth()->user()->name }}</span>
                                <span class="block text-[8px] text-gray-400 font-extrabold uppercase tracking-wider mt-0.5">{{ auth()->user()->role === 'admin' ? 'Superadmin' : auth()->user()->role }}</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-gray-400 text-[8px] ml-1"></i>
                        </button>
                        
                        <div id="profile-dropdown" class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-xl shadow-xl py-1.5 hidden z-50">
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

        // Toggle mobile search input
        function toggleMobileSearch() {
            const container = document.getElementById('header-search-container');
            if (container) {
                container.classList.toggle('hidden');
                container.classList.toggle('flex');
                if (container.classList.contains('flex')) {
                    const input = container.querySelector('input');
                    if (input) input.focus();
                }
            }
        }

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
                const icon = btn.querySelector('.chevron');
                if (icon) {
                    if (submenu.classList.contains('show')) {
                        icon.className = "fa-solid fa-chevron-down text-[9px] chevron transition-transform duration-200";
                    } else {
                        icon.className = "fa-solid fa-chevron-right text-[9px] chevron transition-transform duration-200";
                    }
                }
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
                        const icon = btn.querySelector('.chevron');
                        if (icon) {
                            icon.className = "fa-solid fa-chevron-down text-[9px] chevron transition-transform duration-200";
                        }
                    }
                }
            }
        });
    </script>
    @yield('scripts')
</body>
</html>
