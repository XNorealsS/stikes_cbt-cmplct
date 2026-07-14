@extends('layouts.admin')

@section('title', 'Sinkronisasi Neo Feeder PDDIKTI - CBT STIKes Muhammadiyah Lhokseumawe')

@section('admin-content')
<div class="space-y-5">

    <!-- Page Header (SIAKAD style) -->
    <div class="mb-4">
        <p class="text-xs text-slate-400 mb-1">STIKESMU &gt; Admin &gt; Integrasi Neo Feeder PDDIKTI</p>
        <div class="flex flex-wrap items-start justify-between gap-3 pb-3 border-b-2 border-green-700">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-1.5 h-6 bg-green-700 rounded-full inline-block"></span>
                    Sinkronisasi Neo Feeder PDDIKTI
                </h1>
                <p class="text-xs text-slate-500 mt-0.5">Sinkronisasi data mahasiswa, dosen, prodi, semester, dan kelas kuliah langsung dari Neo Feeder PDDIKTI STIKes Muhammadiyah Lhokseumawe.</p>
            </div>
        </div>
    </div>

    {{-- Status Bar --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        {{-- Connection Status Card --}}
        <div class="border border-slate-200 bg-white p-3" id="conn-status-card">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Status Koneksi Feeder</p>
            <div id="conn-status-badge" class="mt-1 flex items-center gap-2">
                <span class="inline-block h-2.5 w-2.5 rounded-full bg-slate-300 animate-pulse"></span>
                <span class="text-sm font-semibold text-slate-500">Memeriksa...</span>
            </div>
            <p id="conn-status-msg" class="text-[10px] text-slate-400 mt-1"></p>
            <button onclick="testConnection()" class="mt-2 text-[10px] text-blue-600 hover:underline font-semibold">
                Uji Koneksi Ulang
            </button>
        </div>

        {{-- Feeder Endpoint --}}
        <div class="border border-slate-200 bg-white p-3">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Endpoint Feeder</p>
            <p class="text-xs font-mono text-slate-700 mt-1 break-all">feeder.stikeslhokseumawe.ac.id</p>
            <p class="text-[10px] text-slate-400 mt-0.5">ws/live2.php &bull; mode: HTTPS</p>
        </div>

        {{-- Last Full Sync --}}
        <div class="border border-slate-200 bg-white p-3">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Sync Terakhir</p>
            @php
                $lastSync = $recentLogs->where('sync_type','full')->where('status','success')->first();
            @endphp
            @if ($lastSync)
                <p class="text-xs font-semibold text-slate-700 mt-1">{{ $lastSync->finished_at?->format('d M Y H:i') ?? '-' }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">Full sync &bull; {{ $lastSync->duration_seconds }}s</p>
            @else
                <p class="text-xs text-slate-500 mt-1">Belum pernah sync</p>
            @endif
        </div>
    </div>

    {{-- ONE-CLICK Sync Button --}}
    <div class="border border-blue-200 bg-blue-50 p-5 rounded">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-sm font-bold text-blue-900">🔄 Sinkronisasi Semua Data</p>
                <p class="text-xs text-blue-600 mt-0.5">Satu tombol untuk sync semester, prodi, dosen, mahasiswa, dan kelas kuliah secara berurutan dari Neo Feeder.</p>
            </div>
            <button
                id="btn-sync-all"
                onclick="doSync('all')"
                class="flex-shrink-0 bg-blue-700 hover:bg-blue-800 text-white text-sm font-bold px-6 py-3 transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <i class="fa-solid fa-rotate" id="icon-sync-all"></i>
                <span id="label-sync-all">Sync Semua Sekarang</span>
            </button>
        </div>

        {{-- Progress Bar --}}
        <div id="sync-progress" class="mt-4 hidden">
            <div class="bg-blue-200 h-1.5 rounded w-full overflow-hidden">
                <div id="sync-progress-bar" class="h-full bg-blue-600 rounded animate-pulse" style="width: 0%"></div>
            </div>
            <p id="sync-progress-msg" class="text-xs text-blue-600 mt-1">Memulai sinkronisasi...</p>
        </div>

        {{-- Sync Result Stats --}}
        <div id="sync-result" class="hidden mt-4 border-t border-blue-200 pt-3">
            <p class="text-xs font-bold text-blue-900 mb-2">Hasil Sinkronisasi:</p>
            <div id="sync-result-grid" class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                <!-- filled by JS -->
            </div>
        </div>
    </div>

    {{-- Per-Entity Sync Buttons --}}
    <div>
        <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Sync Per Entitas (Opsional)</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2">
            @foreach([
                'semester'  => ['label' => 'Semester', 'icon' => 'fa-calendar'], 
                'prodi'     => ['label' => 'Prodi', 'icon' => 'fa-building-columns'], 
                'courses'   => ['label' => 'Mata Kuliah', 'icon' => 'fa-book'], 
                'mahasiswa' => ['label' => 'Mahasiswa', 'icon' => 'fa-user-graduate'], 
                'dosen'     => ['label' => 'Dosen', 'icon' => 'fa-chalkboard-teacher'], 
                'kelas'     => ['label' => 'Kelas', 'icon' => 'fa-door-open']
            ] as $entity => $info)
            <button
                onclick="doSync('{{ $entity }}')"
                class="entity-sync-btn border border-slate-200 bg-white hover:border-blue-400 hover:bg-blue-50 text-slate-700 hover:text-blue-800 text-xs font-semibold py-2.5 px-3 transition flex flex-col items-center gap-1"
                data-entity="{{ $entity }}"
            >
                <i class="fa-solid {{ $info['icon'] }} text-base"></i>
                <span>{{ $info['label'] }}</span>
            </button>
            @endforeach
        </div>
    </div>

    {{-- Sync Log History --}}
    <div class="border border-slate-200 bg-white">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <div>
                <p class="text-sm font-bold text-slate-800">Riwayat Sinkronisasi</p>
                <p class="text-[10px] text-slate-400">15 proses sinkronisasi terakhir dari Neo Feeder.</p>
            </div>
            <button onclick="refreshLogs()" class="text-[10px] text-blue-600 hover:underline flex items-center gap-1">
                <i class="fa-solid fa-arrows-rotate text-[10px]"></i> Refresh
            </button>
        </div>

        <div class="overflow-x-auto" id="sync-logs-container">
            <table class="w-full text-xs">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-[10px] font-bold uppercase text-slate-400">Tipe</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-bold uppercase text-slate-400">Dipicu Oleh</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-bold uppercase text-slate-400">Waktu Mulai</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-bold uppercase text-slate-400">Durasi</th>
                        <th class="text-center px-4 py-2.5 text-[10px] font-bold uppercase text-slate-400">Status</th>
                        <th class="text-right px-4 py-2.5 text-[10px] font-bold uppercase text-slate-400">Fetched / Ins / Upd / Deact / Err</th>
                    </tr>
                </thead>
                <tbody id="sync-logs-tbody">
                    @forelse ($recentLogs as $log)
                    <tr class="border-b border-slate-50 hover:bg-slate-50 cursor-pointer" onclick="showLogDetail({{ $log->id }})">
                        <td class="px-4 py-2.5">
                            <span class="inline-block font-mono text-[10px] uppercase bg-slate-100 px-1.5 py-0.5 rounded">{{ $log->sync_type }}</span>
                        </td>
                        <td class="px-4 py-2.5 text-slate-500">{{ $log->triggered_by }}</td>
                        <td class="px-4 py-2.5 text-slate-500">{{ $log->started_at?->format('d M Y H:i:s') }}</td>
                        <td class="px-4 py-2.5 text-slate-500">{{ $log->duration_seconds ? $log->duration_seconds . 's' : '-' }}</td>
                        <td class="px-4 py-2.5 text-center">
                            @if($log->status === 'success')
                                <span class="text-emerald-600 font-bold text-[10px] uppercase">✓ Sukses</span>
                            @elseif($log->status === 'running')
                                <span class="text-amber-500 font-bold text-[10px] uppercase animate-pulse">⟳ Running</span>
                            @else
                                <span class="text-red-500 font-bold text-[10px] uppercase">✗ Gagal</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-right font-mono text-slate-500">
                            {{ $log->total_fetched }} / {{ $log->total_inserted }} / {{ $log->total_updated }} / {{ $log->total_deactivated }} / {{ $log->total_errors }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs">
                            Belum ada riwayat sinkronisasi. Klik "Sync Semua Sekarang" untuk memulai.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Debug / Raw Peek Panel --}}
    <details class="border border-amber-200 bg-amber-50">
        <summary class="px-4 py-3 text-xs font-bold text-amber-800 cursor-pointer select-none flex items-center gap-2">
            <i class="fa-solid fa-bug text-amber-500"></i>
            Debug: Cek Raw Response Neo Feeder
            <span class="text-[10px] font-normal text-amber-600 ml-2">(gunakan saat sync gagal — lihat field names yang dikembalikan Feeder)</span>
        </summary>
        <div class="p-4 border-t border-amber-200 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="text-[10px] font-bold uppercase text-amber-700 block mb-1">Action Name</label>
                    <input type="text" id="peek-act" value="GetListDosen" class="w-full border border-amber-300 text-xs px-2 py-1.5 bg-white">
                </div>
                <div>
                    <label class="text-[10px] font-bold uppercase text-amber-700 block mb-1">Filter (opsional)</label>
                    <input type="text" id="peek-filter" value="" placeholder="id_semester = 20251" class="w-full border border-amber-300 text-xs px-2 py-1.5 bg-white">
                </div>
                <div class="flex items-end">
                    <button onclick="peekFeeder()" class="w-full bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold px-3 py-1.5 transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-binoculars" id="peek-icon"></i>
                        <span id="peek-label">Peek Response</span>
                    </button>
                </div>
            </div>
            <div class="flex flex-wrap gap-1.5">
                @foreach(['GetListMahasiswa','GetRiwayatPendidikanMahasiswa','GetListDosen','GetProdi','GetSemester','GetPeriode','GetListKelasKuliah','GetPesertaKelasKuliah','GetDosenPengajarKelasKuliah'] as $presetAct)
                <button onclick="quickPeek('{{ $presetAct }}')" class="text-[10px] bg-white border border-amber-300 hover:bg-amber-100 px-2 py-1 text-amber-700 font-mono transition">{{ $presetAct }}</button>
                @endforeach
            </div>
            <div id="peek-result" class="hidden">
                <div id="peek-status" class="text-xs font-bold mb-1"></div>
                <div id="peek-fields" class="text-[10px] text-slate-600 mb-2 font-mono"></div>
                <pre id="peek-json" class="text-[10px] font-mono bg-slate-900 text-green-300 p-3 overflow-x-auto max-h-80 whitespace-pre-wrap break-words"></pre>
            </div>
        </div>
    </details>

</div>

{{-- Log Detail Modal --}}
<div id="log-detail-modal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center">
    <div class="bg-white border border-slate-200 shadow-xl w-full max-w-lg max-h-[80vh] overflow-y-auto p-6">
        <div class="flex items-start justify-between border-b pb-3 mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Detail Log Sinkronisasi</h3>
                <p class="text-[10px] text-slate-400 mt-0.5" id="modal-log-id">—</p>
            </div>
            <button onclick="closeLogModal()" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
        </div>
        <div id="modal-log-content" class="space-y-3 text-xs text-slate-700">
            Loading...
        </div>
    </div>
</div>

{{-- Real-time Sync Progress Modal --}}
<div id="sync-progress-modal" class="fixed inset-0 z-50 hidden bg-black/60 flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white border border-slate-200 shadow-2xl w-full max-w-md p-6 rounded-lg text-slate-800 space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="font-bold text-sm flex items-center gap-2">
                <i class="fa-solid fa-spinner fa-spin text-blue-600" id="sync-modal-spinner"></i>
                <span id="sync-modal-title">Sinkronisasi Sedang Berjalan...</span>
            </h3>
        </div>
        <div class="space-y-3">
            <p class="text-xs text-slate-500">
                Jangan tutup halaman ini selama proses sinkronisasi dari Neo Feeder PDDIKTI berlangsung.
            </p>
            
            <div class="bg-slate-50 border border-slate-100 p-3 rounded space-y-2 text-xs">
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-semibold">Tipe Entitas:</span>
                    <span id="sync-modal-type" class="font-bold uppercase font-mono text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">ALL</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Data Diambil (Fetched):</span>
                    <span id="sync-modal-fetched" class="font-bold text-slate-700">0</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Data Baru (Inserted):</span>
                    <span id="sync-modal-inserted" class="font-bold text-emerald-600">0</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Data Diupdate (Updated):</span>
                    <span id="sync-modal-updated" class="font-bold text-amber-600">0</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Error:</span>
                    <span id="sync-modal-errors" class="font-bold text-red-600">0</span>
                </div>
            </div>

            <div class="bg-blue-100 h-1 rounded w-full overflow-hidden">
                <div id="sync-modal-bar" class="h-full bg-blue-600 rounded" style="width: 15%"></div>
            </div>
            
            <p id="sync-modal-status-text" class="text-center text-xs font-semibold text-blue-700">Menghubungi Neo Feeder...</p>
        </div>
        <div class="flex justify-end pt-2">
            <button id="sync-modal-close-btn" disabled onclick="closeSyncProgressModal()" class="bg-slate-200 hover:bg-slate-300 disabled:opacity-50 text-slate-700 text-xs font-bold px-4 py-2 rounded transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    // ── Helpers ──────────────────────────────────────────────────────────────
    const CSRF = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function statusBadge(s) {
        if (s === 'success') return '<span class="text-emerald-600 font-bold text-[10px] uppercase">✓ Sukses</span>';
        if (s === 'running') return '<span class="text-amber-500 font-bold text-[10px] uppercase animate-pulse">⟳ Running</span>';
        return '<span class="text-red-500 font-bold text-[10px] uppercase">✗ Gagal</span>';
    }

    // ── Test Connection ───────────────────────────────────────────────────────
    function testConnection() {
        const badge = document.getElementById('conn-status-badge');
        const msg   = document.getElementById('conn-status-msg');
        badge.innerHTML = '<span class="inline-block h-2.5 w-2.5 rounded-full bg-yellow-400 animate-pulse"></span><span class="text-sm font-semibold text-yellow-600">Memeriksa...</span>';
        msg.textContent = '';

        fetch('{{ route("admin.feeder.test") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'ok') {
                badge.innerHTML = '<span class="inline-block h-2.5 w-2.5 rounded-full bg-emerald-500"></span><span class="text-sm font-semibold text-emerald-700">Terhubung</span>';
            } else {
                badge.innerHTML = '<span class="inline-block h-2.5 w-2.5 rounded-full bg-red-500"></span><span class="text-sm font-semibold text-red-600">Gagal</span>';
            }
            msg.textContent = data.message || '';
        })
        .catch(e => {
            badge.innerHTML = '<span class="inline-block h-2.5 w-2.5 rounded-full bg-red-500"></span><span class="text-sm font-semibold text-red-600">Error</span>';
            msg.textContent = e.message;
        });
    }

    let pollingInterval = null;

    function openSyncProgressModal(entity) {
        document.getElementById('sync-progress-modal').classList.remove('hidden');
        document.getElementById('sync-modal-type').textContent = entity;
        document.getElementById('sync-modal-fetched').textContent = '0';
        document.getElementById('sync-modal-inserted').textContent = '0';
        document.getElementById('sync-modal-updated').textContent = '0';
        document.getElementById('sync-modal-errors').textContent = '0';
        document.getElementById('sync-modal-bar').style.width = '10%';
        document.getElementById('sync-modal-spinner').classList.add('fa-spin');
        document.getElementById('sync-modal-title').textContent = 'Sinkronisasi Sedang Berjalan...';
        document.getElementById('sync-modal-status-text').textContent = 'Menghubungi Neo Feeder...';
        document.getElementById('sync-modal-close-btn').disabled = true;
        document.getElementById('sync-modal-close-btn').className = 'bg-slate-200 text-slate-400 text-xs font-bold px-4 py-2 rounded transition cursor-not-allowed';

        // Start polling the logs endpoint
        startProgressPolling(entity);
    }

    function closeSyncProgressModal() {
        document.getElementById('sync-progress-modal').classList.add('hidden');
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    }

    function startProgressPolling(entity) {
        if (pollingInterval) clearInterval(pollingInterval);
        
        pollingInterval = setInterval(() => {
            fetch('{{ route("admin.feeder.logs") }}', { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                if (data.logs && data.logs.length > 0) {
                    // Find the latest running sync log of this type (or any type if 'all')
                    let activeLog = null;
                    if (entity === 'all') {
                        activeLog = data.logs.find(log => log.status === 'running');
                    } else {
                        activeLog = data.logs.find(log => log.sync_type === entity && log.status === 'running');
                    }

                    if (activeLog) {
                        document.getElementById('sync-modal-fetched').textContent = activeLog.total_fetched;
                        document.getElementById('sync-modal-inserted').textContent = activeLog.total_inserted;
                        document.getElementById('sync-modal-updated').textContent = activeLog.total_updated;
                        document.getElementById('sync-modal-errors').textContent = activeLog.total_errors;
                        
                        let entityName = activeLog.sync_type.toUpperCase();
                        document.getElementById('sync-modal-type').textContent = entityName;
                        document.getElementById('sync-modal-status-text').textContent = `Memproses data ${entityName}: ${activeLog.total_fetched} record diproses...`;
                        
                        // Increment bar slightly
                        let fetched = parseInt(activeLog.total_fetched) || 0;
                        let pct = Math.min(20 + (fetched % 80), 95);
                        document.getElementById('sync-modal-bar').style.width = pct + '%';
                    }
                }
            })
            .catch(e => console.error("Polling error: ", e));
        }, 1200);
    }

    // ── Do Sync ───────────────────────────────────────────────────────────────
    function doSync(entity) {
        const isAll    = (entity === 'all');
        const btn      = isAll ? document.getElementById('btn-sync-all') : null;
        const icon     = isAll ? document.getElementById('icon-sync-all') : null;
        const label    = isAll ? document.getElementById('label-sync-all') : null;
        const progress = document.getElementById('sync-progress');
        const progBar  = document.getElementById('sync-progress-bar');
        const progMsg  = document.getElementById('sync-progress-msg');
        const result   = document.getElementById('sync-result');

        // Disable all sync buttons
        document.querySelectorAll('.entity-sync-btn, #btn-sync-all').forEach(b => b.disabled = true);

        if (isAll) {
            icon.classList.add('fa-spin');
            label.textContent = 'Sedang Sync...';
            progress.classList.remove('hidden');
            progBar.style.width = '15%';
            progMsg.textContent = 'Menghubungi Neo Feeder...';
            result.classList.add('hidden');
        } else {
            const entityBtn = document.querySelector(`.entity-sync-btn[data-entity="${entity}"]`);
            if (entityBtn) entityBtn.innerHTML = '<i class="fa-solid fa-rotate fa-spin text-base"></i><span>Loading...</span>';
        }

        // Open the beautiful floating progress modal popup
        openSyncProgressModal(entity);

        fetch('{{ route("admin.feeder.sync") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'Content-Type': 'application/json' },
            body: JSON.stringify({ entity })
        })
        .then(r => r.json())
        .then(data => {
            // Stop polling
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }

            // Update popup modal to finished status
            document.getElementById('sync-modal-spinner').classList.remove('fa-spin');
            document.getElementById('sync-modal-bar').style.width = '100%';
            
            const closeBtn = document.getElementById('sync-modal-close-btn');
            closeBtn.disabled = false;
            closeBtn.className = 'bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold px-5 py-2.5 rounded transition shadow-md';

            if (data.success) {
                document.getElementById('sync-modal-title').innerHTML = '<span class="text-emerald-600">✓ Sinkronisasi Selesai</span>';
                document.getElementById('sync-modal-status-text').className = 'text-center text-xs font-semibold text-emerald-600';
                document.getElementById('sync-modal-status-text').textContent = 'Semua data sukses disinkronkan!';
            } else {
                document.getElementById('sync-modal-title').innerHTML = '<span class="text-red-600">✗ Sinkronisasi Gagal</span>';
                document.getElementById('sync-modal-status-text').className = 'text-center text-xs font-semibold text-red-600';
                document.getElementById('sync-modal-status-text').textContent = data.message || 'Terjadi kesalahan.';
            }

            if (isAll) {
                progBar.style.width = '100%';
                progBar.classList.remove('animate-pulse');
                progBar.classList.add(data.success ? 'bg-emerald-500' : 'bg-red-500');
                progMsg.textContent = data.success ? '✓ Sinkronisasi selesai!' : '✗ ' + (data.message || 'Gagal');
                icon.classList.remove('fa-spin');
                label.textContent = 'Sync Semua Sekarang';

                // Show stats per entity
                if (data.stats) {
                    let html = '';
                    for (const [ent, s] of Object.entries(data.stats)) {
                        html += `<div class="bg-white border border-blue-100 p-2">
                            <p class="text-[10px] font-bold text-blue-700 uppercase">${ent}</p>
                            <p class="text-[10px] text-slate-500 mt-0.5">
                                <span class="text-emerald-600 font-bold">+${s.inserted}</span> baru &bull;
                                <span class="text-amber-600 font-bold">${s.updated}</span> diperbarui &bull;
                                <span class="text-red-500 font-bold">${s.deactivated}</span> dihapus
                            </p>
                        </div>`;
                    }
                    document.getElementById('sync-result-grid').innerHTML = html;
                    result.classList.remove('hidden');
                }
            } else {
                const entityBtn = document.querySelector(`.entity-sync-btn[data-entity="${entity}"]`);
                const labels = {semester:'Semester', prodi:'Prodi', courses:'Mata Kuliah', mahasiswa:'Mahasiswa', dosen:'Dosen', kelas:'Kelas'};
                const icons  = {semester:'fa-calendar', prodi:'fa-building-columns', courses:'fa-book', mahasiswa:'fa-user-graduate', dosen:'fa-chalkboard-teacher', kelas:'fa-door-open'};
                if (entityBtn) {
                    entityBtn.innerHTML = `<i class="fa-solid ${icons[entity] || 'fa-check'} text-base ${data.success ? 'text-emerald-600' : 'text-red-500'}"></i><span>${labels[entity] || entity}</span>`;
                }
            }

            refreshLogs();
        })
        .catch(e => {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
            
            document.getElementById('sync-modal-spinner').classList.remove('fa-spin');
            document.getElementById('sync-modal-title').innerHTML = '<span class="text-red-600">✗ Error Koneksi</span>';
            document.getElementById('sync-modal-status-text').className = 'text-center text-xs font-semibold text-red-600';
            document.getElementById('sync-modal-status-text').textContent = e.message;
            
            const closeBtn = document.getElementById('sync-modal-close-btn');
            closeBtn.disabled = false;
            closeBtn.className = 'bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold px-5 py-2.5 rounded transition shadow-md';

            if (isAll) {
                progBar.classList.add('bg-red-500');
                progMsg.textContent = '✗ Error: ' + e.message;
                icon.classList.remove('fa-spin');
                label.textContent = 'Sync Semua Sekarang';
            }
        })
        .finally(() => {
            setTimeout(() => {
                document.querySelectorAll('.entity-sync-btn, #btn-sync-all').forEach(b => b.disabled = false);
                // Reset entity buttons to original icons
                document.querySelectorAll('.entity-sync-btn').forEach(btn => {
                    const ent = btn.dataset.entity;
                    const labels = {semester:'Semester', prodi:'Prodi', courses:'Mata Kuliah', mahasiswa:'Mahasiswa', dosen:'Dosen', kelas:'Kelas'};
                    const icons  = {semester:'fa-calendar', prodi:'fa-building-columns', courses:'fa-book', mahasiswa:'fa-user-graduate', dosen:'fa-chalkboard-teacher', kelas:'fa-door-open'};
                    btn.innerHTML = `<i class="fa-solid ${icons[ent] || 'fa-sync'} text-base"></i><span>${labels[ent] || ent}</span>`;
                });
            }, 3000);
        });
    }

    // ── Refresh Logs Table ────────────────────────────────────────────────────
    function refreshLogs() {
        fetch('{{ route("admin.feeder.logs") }}', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            const tbody = document.getElementById('sync-logs-tbody');
            if (!data.logs || data.logs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-8 text-center text-slate-400 text-xs">Belum ada riwayat sinkronisasi.</td></tr>';
                return;
            }
            tbody.innerHTML = data.logs.map(log => `
                <tr class="border-b border-slate-50 hover:bg-slate-50 cursor-pointer" onclick="showLogDetail(${log.id})">
                    <td class="px-4 py-2.5">
                        <span class="inline-block font-mono text-[10px] uppercase bg-slate-100 px-1.5 py-0.5 rounded">${log.sync_type}</span>
                    </td>
                    <td class="px-4 py-2.5 text-slate-500 text-xs">${log.triggered_by}</td>
                    <td class="px-4 py-2.5 text-slate-500 text-xs">${log.started_at || '-'}</td>
                    <td class="px-4 py-2.5 text-slate-500 text-xs">${log.duration_seconds ? log.duration_seconds + 's' : '-'}</td>
                    <td class="px-4 py-2.5 text-center text-xs">${statusBadge(log.status)}</td>
                    <td class="px-4 py-2.5 text-right font-mono text-slate-500 text-xs">
                        ${log.total_fetched} / ${log.total_inserted} / ${log.total_updated} / ${log.total_deactivated} / ${log.total_errors}
                    </td>
                </tr>
            `).join('');
        });
    }

    // ── Log Detail Modal ──────────────────────────────────────────────────────
    function showLogDetail(id) {
        document.getElementById('log-detail-modal').classList.remove('hidden');
        document.getElementById('modal-log-id').textContent = 'Log ID #' + id;
        document.getElementById('modal-log-content').textContent = 'Loading...';

        fetch(`{{ url('admin/feeder/logs') }}/${id}`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(log => {
            const rows = [
                ['Tipe Sync', log.sync_type],
                ['Dipicu Oleh', log.triggered_by],
                ['Status', log.status],
                ['Mulai', log.started_at],
                ['Selesai', log.finished_at || '-'],
                ['Durasi', log.duration_seconds ? log.duration_seconds + ' detik' : '-'],
                ['Total Fetched', log.total_fetched],
                ['Total Inserted', log.total_inserted],
                ['Total Updated', log.total_updated],
                ['Total Deactivated', log.total_deactivated],
                ['Total Errors', log.total_errors],
                ['Catatan', log.notes || '-'],
            ];
            document.getElementById('modal-log-content').innerHTML = `
                <table class="w-full text-xs">
                    ${rows.map(([k, v]) => `<tr class="border-b border-slate-50"><td class="py-1.5 pr-4 font-semibold text-slate-500 whitespace-nowrap">${k}</td><td class="py-1.5 text-slate-700">${v ?? '-'}</td></tr>`).join('')}
                </table>
                ${log.error_log ? `<div class="mt-3 bg-red-50 border border-red-100 p-3 rounded text-[10px] font-mono text-red-700 overflow-x-auto whitespace-pre">${JSON.stringify(JSON.parse(log.error_log), null, 2)}</div>` : ''}
            `;
        })
        .catch(() => {
            document.getElementById('modal-log-content').textContent = 'Gagal memuat detail.';
        });
    }

    function closeLogModal() {
        document.getElementById('log-detail-modal').classList.add('hidden');
    }

    // ── On Load ───────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        testConnection();
    });
</script>

@endsection
