<?php

namespace App\Services;

use App\Models\User;
use App\Models\Prodi;
use App\Models\Course;
use App\Models\ClassRoom;
use App\Models\TahunAkademik;
use App\Models\SyncLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FeederSyncService
{
    protected FeederService $feeder;

    public function __construct(FeederService $feeder)
    {
        $this->feeder = $feeder;
    }

    // ─────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────

    protected function logSyncStart(string $type, string $trigger, ?int $userId = null): SyncLog
    {
        return SyncLog::create([
            'sync_type'              => $type,
            'triggered_by'           => $trigger,
            'triggered_by_user_id'   => $userId,
            'started_at'             => now(),
            'status'                 => 'running',
            'total_fetched'          => 0,
            'total_inserted'         => 0,
            'total_updated'          => 0,
            'total_deactivated'      => 0,
            'total_errors'           => 0,
        ]);
    }

    protected function logSyncEnd(SyncLog $log, string $status, array $stats, ?array $errors = null, ?string $notes = null): void
    {
        $finished = now();
        // Use abs to avoid negative duration (clock skew between DB & app server)
        $duration = max(0, (int) abs($finished->diffInSeconds($log->started_at)));

        $log->update([
            'finished_at'       => $finished,
            'status'            => $status,
            'total_fetched'     => $stats['fetched']      ?? 0,
            'total_inserted'    => $stats['inserted']     ?? 0,
            'total_updated'     => $stats['updated']      ?? 0,
            'total_deactivated' => $stats['deactivated']  ?? 0,
            'total_errors'      => $stats['errors_count'] ?? 0,
            'error_log'         => $errors ? json_encode($errors) : null,
            'notes'             => $notes,
            'duration_seconds'  => $duration,
        ]);
    }

    protected function emptyStats(): array
    {
        return ['fetched' => 0, 'inserted' => 0, 'updated' => 0, 'deactivated' => 0, 'errors_count' => 0];
    }

    // ─────────────────────────────────────────────
    // 1. SYNC SEMESTER
    // ─────────────────────────────────────────────

    public function syncSemester(string $trigger = 'artisan', ?int $userId = null): array
    {
        $log    = $this->logSyncStart('semester', $trigger, $userId);
        $stats  = $this->emptyStats();
        $errors = [];

        // Get active period first (to know which semester to mark active)
        $activeSemesterId = null;
        $periodeRes = $this->feeder->request('GetPeriode', ['limit' => 10, 'offset' => 0]);
        if ($periodeRes['success'] && !empty($periodeRes['data'])) {
            $p = $periodeRes['data'][0] ?? [];
            // Neo Feeder field names vary — try multiple
            $activeSemesterId = $p['id_semester'] ?? $p['kode_semester'] ?? $p['semester_aktif'] ?? null;
        }

        // Fetch all semesters
        $response = $this->feeder->request('GetSemester', ['limit' => 200, 'offset' => 0]);

        if (!$response['success']) {
            $errors[] = 'GetSemester gagal: ' . $response['error_desc'];
            $stats['errors_count']++;
            $this->logSyncEnd($log, 'failed', $stats, $errors);
            return ['success' => false, 'message' => $response['error_desc']];
        }

        $semesters = $response['data'] ?? [];
        $stats['fetched'] = count($semesters);

        foreach ($semesters as $sem) {
            // Try multiple field name variations
            $feederId = $sem['id_semester'] ?? $sem['kode_semester'] ?? $sem['id_smt'] ?? null;
            if (!$feederId) continue;

            $nama = $sem['nama_semester'] ?? $sem['nama_periode'] ?? $sem['semester'] ?? $feederId;

            // Derive tahun_mulai and type from feeder ID like "20251" or "20242"
            $tahunMulai   = (int) substr((string)$feederId, 0, 4);
            $semesterNum  = substr((string)$feederId, 4, 1);
            $semesterType = ($semesterNum === '1' || $semesterNum === '1') ? 'ganjil' : 'genap';

            // Determine if active: use GetPeriode result, then field in response
            $isActive = false;
            if ($activeSemesterId !== null) {
                $isActive = ((string)$feederId === (string)$activeSemesterId);
            } else {
                // Field names Neo Feeder might use
                $activeFlag = $sem['a_periode_aktif'] ?? $sem['aktif'] ?? $sem['is_aktif'] ?? $sem['status_aktif'] ?? 0;
                $isActive   = (in_array($activeFlag, [1, '1', true, 'Y', 'ya', 'Aktif', 'aktif'], true));
            }

            try {
                $ta = TahunAkademik::where('feeder_semester_id', (string)$feederId)->first();

                if ($ta) {
                    $ta->update([
                        'nama'       => $nama,
                        'tahun_mulai' => $tahunMulai,
                        'semester'   => $semesterType,
                        'is_aktif'   => $isActive,
                    ]);
                    $stats['updated']++;
                } else {
                    TahunAkademik::create([
                        'feeder_semester_id' => (string)$feederId,
                        'nama'               => $nama,
                        'tahun_mulai'        => $tahunMulai,
                        'semester'           => $semesterType,
                        'is_aktif'           => $isActive,
                    ]);
                    $stats['inserted']++;
                }
            } catch (\Exception $e) {
                $errors[] = "Semester [{$feederId}] error: " . $e->getMessage();
                $stats['errors_count']++;
            }
        }

        // Ensure only ONE active semester at a time
        if ($activeSemesterId) {
            TahunAkademik::where('feeder_semester_id', '!=', (string)$activeSemesterId)->update(['is_aktif' => false]);
        }

        // Fallback: if still no active semester, auto-activate the most recent
        if (!TahunAkademik::where('is_aktif', true)->exists()) {
            $latest = TahunAkademik::whereNotNull('feeder_semester_id')
                ->orderByDesc('feeder_semester_id')
                ->first();
            if ($latest) {
                $latest->update(['is_aktif' => true]);
                Log::info("FeederSync: Fallback — set semester {$latest->feeder_semester_id} as active (no active period found from Feeder).");
            }
        }

        $this->logSyncEnd($log, empty($errors) ? 'success' : 'success', $stats, $errors ?: null,
            $activeSemesterId ? "Semester aktif dari Feeder: {$activeSemesterId}" : "Semester aktif: auto-detect dari feeder_semester_id terbesar");

        return ['success' => true, 'stats' => $stats];
    }

    // ─────────────────────────────────────────────
    // 2. SYNC PRODI
    // ─────────────────────────────────────────────

    public function syncProdi(string $trigger = 'artisan', ?int $userId = null): array
    {
        $log    = $this->logSyncStart('prodi', $trigger, $userId);
        $stats  = $this->emptyStats();
        $errors = [];

        $response = $this->feeder->request('GetProdi', ['limit' => 100, 'offset' => 0]);

        if (!$response['success']) {
            $errors[] = 'GetProdi gagal: ' . $response['error_desc'];
            $stats['errors_count']++;
            $this->logSyncEnd($log, 'failed', $stats, $errors);
            return ['success' => false, 'message' => $response['error_desc']];
        }

        $prodis = $response['data'] ?? [];
        $stats['fetched'] = count($prodis);

        foreach ($prodis as $p) {
            // Field name fallbacks
            $feederId  = $p['id_prodi'] ?? $p['kode_prodi'] ?? null;
            $kode      = $p['kode_program_studi'] ?? $p['kode_prodi'] ?? $p['kode'] ?? 'PRODI-' . ($p['id_prodi'] ?? rand(100, 999));
            $nama      = $p['nama_program_studi'] ?? $p['nama_prodi'] ?? $p['nama'] ?? 'Prodi Tanpa Nama';
            $jenjangTxt = $p['nama_jenjang_pendidikan'] ?? $p['jenjang'] ?? $p['kode_jenjang_pendidikan'] ?? 'S1';
            $status    = $p['status'] ?? $p['a_aktif'] ?? 'A';

            if (!$feederId) continue;

            // Map jenjang text to enum
            $jenjangLow = strtolower($jenjangTxt);
            $jenjang    = 'S1';
            if (str_contains($jenjangLow, 'd3') || $jenjangLow === '5') $jenjang = 'D3';
            elseif (str_contains($jenjangLow, 'd4') || $jenjangLow === '6') $jenjang = 'D4';
            elseif (str_contains($jenjangLow, 's2') || $jenjangLow === '2') $jenjang = 'S2';
            elseif (str_contains($jenjangLow, 'profesi') || $jenjangLow === '3') $jenjang = 'Profesi';

            $isAktif = in_array($status, ['A', 'Aktif', 'aktif', '1', 1, true], true);

            try {
                // Lookup by feeder_id first, then kode
                $prodi = Prodi::where('feeder_id', $feederId)->orWhere('kode', $kode)->first();

                if ($prodi) {
                    $prodi->update([
                        'feeder_id'        => $feederId,
                        'kode'             => $kode,
                        'nama'             => $nama,
                        'jenjang'          => $jenjang,
                        'is_aktif'         => $isAktif,
                        'feeder_synced_at' => now(),
                    ]);
                    $stats['updated']++;
                } else {
                    Prodi::create([
                        'feeder_id'        => $feederId,
                        'kode'             => $kode,
                        'nama'             => $nama,
                        'jenjang'          => $jenjang,
                        'is_aktif'         => $isAktif,
                        'feeder_synced_at' => now(),
                    ]);
                    $stats['inserted']++;
                }
            } catch (\Exception $e) {
                $errors[] = "Prodi [{$kode}] error: " . $e->getMessage();
                $stats['errors_count']++;
            }
        }

        // Delete prodi no longer in Feeder (using the collected prodi feeder IDs)
        $allFeederIds = [];
        foreach ($prodis as $p) {
            $fId = $p['id_prodi'] ?? $p['kode_prodi'] ?? null;
            if ($fId) $allFeederIds[] = $fId;
        }
        if (!empty($allFeederIds)) {
            $deactivated = Prodi::whereNotIn('feeder_id', $allFeederIds)->delete();
            $stats['deactivated'] = $deactivated;
        }

        $this->logSyncEnd($log, 'success', $stats, $errors ?: null);
        return ['success' => true, 'stats' => $stats];
    }

    // ─────────────────────────────────────────────
    // 3. SYNC MAHASISWA AKTIF
    // ─────────────────────────────────────────────

    public function syncMahasiswa(string $trigger = 'artisan', ?int $userId = null): array
    {
        $log          = $this->logSyncStart('mahasiswa', $trigger, $userId);
        $stats        = $this->emptyStats();
        $errors       = [];
        $limit        = 100;
        $offset       = 0;
        $allFeederIds = [];

        $prodisMap = Prodi::pluck('id', 'feeder_id')->toArray();

        // Try multiple possible action names — Neo Feeder versions differ.
        // GetListMahasiswaDiKelas needs class_id. GetRiwayatPendidikanMahasiswa works globally.
        $workingAction = null;
        $candidateActions = [
            'GetListMahasiswa',
            'GetRiwayatPendidikanMahasiswa',
            'GetMahasiswaDiProdi',
        ];
        foreach ($candidateActions as $act) {
            $probe = $this->feeder->request($act, ['limit' => 1, 'offset' => 0]);
            if ($probe['success']) {
                $workingAction = $act;
                break;
            }
        }

        if (!$workingAction) {
            $errors[] = 'Tidak ada action mahasiswa yang berhasil. Dicoba: ' . implode(', ', $candidateActions);
            $stats['errors_count']++;
            $this->logSyncEnd($log, 'failed', $stats, $errors);
            return ['success' => false, 'message' => $errors[0]];
        }

        while (true) {
            $response = $this->feeder->request($workingAction, [
                'limit'  => $limit,
                'offset' => $offset,
            ]);

            if (!$response['success']) {
                $errors[] = "{$workingAction} offset={$offset} error: " . $response['error_desc'];
                $stats['errors_count']++;
                break;
            }

            $mahasiswas = $response['data'] ?? [];
            if (empty($mahasiswas)) break;

            foreach ($mahasiswas as $m) {
                // Field name fallbacks (Neo Feeder versions vary)
                $feederId = $m['id_registrasi_mahasiswa'] ?? $m['id_mahasiswa'] ?? null;
                $nim      = trim($m['nim'] ?? $m['nomor_induk'] ?? '');
                $nama     = $m['nama_mahasiswa'] ?? $m['nama'] ?? null;

                // Filter strictly: only ACTIVE students
                $statusNama = strtoupper($m['nama_status_mahasiswa'] ?? $m['nama_stat_mhs'] ?? $m['status_mahasiswa'] ?? '');
                if ($statusNama !== 'AKTIF') {
                    continue; // Skip anyone who is not AKTIF (lulus, mutasi, cuti, dll.)
                }

                if (empty($nim)) {
                    continue; // Skip incomplete student record in Feeder that has no NIM
                }

                $stats['fetched']++;

                // Use NIM as feeder_id fallback if no UUID
                if (!$feederId) $feederId = $nim;
                $allFeederIds[] = $feederId;

                $feederProdiId = $m['id_prodi'] ?? null;
                $prodiDbId     = $feederProdiId ? ($prodisMap[$feederProdiId] ?? null) : null;
                $angkatan      = substr((string)($m['id_smt'] ?? $m['angkatan'] ?? ''), 0, 4) ?: null;
                $email         = trim($m['email'] ?? '');
                if (empty($email)) $email = strtolower($nim) . '@stikeslhokseumawe.ac.id';

                try {
                    $user = User::where('feeder_id', $feederId)
                        ->orWhere('username', $nim)
                        ->first();

                    $payload = [
                        'feeder_id'        => $feederId,
                        'name'             => $nama ?: $nim,
                        'username'         => $nim,
                        'prodi_id'         => $prodiDbId,
                        'angkatan'         => $angkatan,
                        'status'           => 'aktif',
                        'feeder_status'    => 'A',
                        'feeder_inactive'  => false,
                        'feeder_synced_at' => now(),
                    ];

                    if ($user) {
                        // Only update email if it's currently the default placeholder
                        if (!$user->email || str_ends_with($user->email, '@stikeslhokseumawe.ac.id')) {
                            $payload['email'] = $email;
                        }
                        $user->update($payload);
                        $stats['updated']++;
                    } else {
                        $payload['email']    = $email;
                        $payload['role']     = 'mahasiswa';
                        $payload['password'] = Hash::make($nim);
                        User::create($payload);
                        $stats['inserted']++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Mahasiswa NIM={$nim}: " . $e->getMessage();
                    $stats['errors_count']++;
                    Log::warning("FeederSync mahasiswa NIM={$nim} error: " . $e->getMessage());
                }
            }

            // Real-time update of sync log after each batch
            $log->update([
                'total_fetched' => $stats['fetched'],
                'total_inserted' => $stats['inserted'],
                'total_updated' => $stats['updated'],
                'total_errors' => $stats['errors_count'],
            ]);

            if (count($mahasiswas) < $limit) break;
            $offset += $limit;
        }

        // Delete mahasiswa no longer in active list from Feeder
        if (!empty($allFeederIds)) {
            $deletedCount = User::where('role', 'mahasiswa')
                ->whereNotIn('feeder_id', $allFeederIds)
                ->delete();
            $stats['deactivated'] = $deletedCount;
        }

        $status = $stats['errors_count'] > 0 && $stats['fetched'] === 0 ? 'failed' : 'success';
        $this->logSyncEnd($log, $status, $stats, $errors ?: null,
            "Action digunakan: {$workingAction}. Total active fetched: {$stats['fetched']}");
        return ['success' => $status !== 'failed', 'stats' => $stats];
    }

    // ─────────────────────────────────────────────
    // 4. SYNC DOSEN AKTIF
    // ─────────────────────────────────────────────

    public function syncDosen(string $trigger = 'artisan', ?int $userId = null): array
    {
        $log          = $this->logSyncStart('dosen', $trigger, $userId);
        $stats        = $this->emptyStats();
        $errors       = [];
        $limit        = 100;
        $offset       = 0;
        $allFeederIds = [];

        $prodisMap = Prodi::pluck('id', 'feeder_id')->toArray();

        while (true) {
            $response = $this->feeder->request('GetListDosen', [
                'limit'  => $limit,
                'offset' => $offset,
            ]);

            if (!$response['success']) {
                $errors[] = "GetListDosen offset={$offset} error: " . $response['error_desc'];
                $stats['errors_count']++;
                break;
            }

            $dosens = $response['data'] ?? [];
            if (empty($dosens)) break;

            $stats['fetched'] += count($dosens);

            foreach ($dosens as $d) {
                // Multi-field fallbacks for different Neo Feeder versions
                $feederId = $d['id_registrasi_dosen'] ?? $d['id_dosen'] ?? $d['kode_dosen'] ?? null;
                $nidn     = trim($d['nidn'] ?? $d['nip'] ?? $d['nipd'] ?? '');
                $nama     = $d['nama_dosen'] ?? $d['nama'] ?? null;

                // Store status for record-keeping but do NOT filter —
                // status field names vary per Neo Feeder version.
                // Admin can deactivate manually if needed.
                $statusKepeg = (string)($d['id_status_aktif'] ?? $d['status_aktif'] ?? $d['a_aktif'] ?? '1');

                if (empty($nidn)) {
                    continue; // Skip incomplete lecturer record in Feeder that has no NIDN/NIP
                }

                if (!$feederId) $feederId = $nidn;
                if (!$nidn)    $nidn     = $feederId;

                $allFeederIds[] = $feederId;

                $feederProdiId = $d['id_prodi'] ?? null;
                $prodiDbId     = $feederProdiId ? ($prodisMap[$feederProdiId] ?? null) : null;
                $email         = trim($d['email'] ?? $d['email_dosen'] ?? '');
                if (empty($email)) $email = strtolower(str_replace(' ', '', $nidn)) . '@stikeslhokseumawe.ac.id';

                try {
                    // Lookup by feeder_id first (UUID), then by NIDN username
                    $user = User::where(function ($q) use ($feederId, $nidn) {
                        $q->where('feeder_id', $feederId)
                          ->orWhere('username', $nidn);
                    })->where('role', 'dosen')->first();

                    $payload = [
                        'feeder_id'        => $feederId,
                        'name'             => $nama ?: $nidn,
                        'username'         => $nidn,
                        'prodi_id'         => $prodiDbId,
                        'status'           => 'aktif',
                        'nidn'             => $nidn,
                        'feeder_status'    => (string)$statusKepeg,
                        'feeder_inactive'  => false,
                        'feeder_synced_at' => now(),
                    ];

                    if ($user) {
                        if (!$user->email || str_ends_with($user->email, '@stikeslhokseumawe.ac.id')) {
                            $payload['email'] = $email;
                        }
                        $user->update($payload);
                        $stats['updated']++;
                    } else {
                        $payload['email']    = $email;
                        $payload['role']     = 'dosen';
                        $payload['password'] = Hash::make($nidn);
                        User::create($payload);
                        $stats['inserted']++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Dosen NIDN={$nidn}: " . $e->getMessage();
                    $stats['errors_count']++;
                    Log::warning("FeederSync dosen NIDN={$nidn} error: " . $e->getMessage());
                }
            }

            // Real-time update of sync log after each batch
            $log->update([
                'total_fetched' => $stats['fetched'],
                'total_inserted' => $stats['inserted'],
                'total_updated' => $stats['updated'],
                'total_errors' => $stats['errors_count'],
            ]);

            if (count($dosens) < $limit) break;
            $offset += $limit;
        }

        // Delete dosens no longer in Feeder active list
        if (!empty($allFeederIds)) {
            $deletedCount = User::where('role', 'dosen')
                ->whereNotIn('feeder_id', $allFeederIds)
                ->delete();
            $stats['deactivated'] = $deletedCount;
        }

        $status = $stats['errors_count'] > 0 && $stats['fetched'] === 0 ? 'failed' : 'success';
        $this->logSyncEnd($log, $status, $stats, $errors ?: null);
        return ['success' => $status !== 'failed', 'stats' => $stats];
    }

    // ─────────────────────────────────────────────
    // 5. SYNC KELAS KULIAH
    // ─────────────────────────────────────────────

    public function syncKelas(string $trigger = 'artisan', ?int $userId = null): array
    {
        $log    = $this->logSyncStart('kelas', $trigger, $userId);
        $stats  = $this->emptyStats();
        $errors = [];

        // Find active semester (must have been synced first)
        $activeSemester = TahunAkademik::where('is_aktif', true)->whereNotNull('feeder_semester_id')->first();
        if (!$activeSemester) {
            // Auto-pick the most recent semester
            $activeSemester = TahunAkademik::whereNotNull('feeder_semester_id')
                ->orderByDesc('feeder_semester_id')
                ->first();
            if ($activeSemester) {
                // Deactivate others first to maintain integrity
                TahunAkademik::where('is_aktif', true)->update(['is_aktif' => false]);
                $activeSemester->update(['is_aktif' => true]);
                Log::info("FeederSync kelas: auto-selected semester {$activeSemester->feeder_semester_id} as active.");
            }
        }

        if (!$activeSemester || !$activeSemester->feeder_semester_id) {
            $errors[] = 'Tidak ada semester aktif di database yang terhubung dengan Feeder. Jalankan sync semester terlebih dahulu.';
            $stats['errors_count']++;
            $this->logSyncEnd($log, 'failed', $stats, $errors);
            return ['success' => false, 'message' => $errors[0]];
        }

        $semesterId = $activeSemester->feeder_semester_id;
        $limit      = 100;
        $offset     = 0;
        $allFeederIds = [];

        $prodisMap = Prodi::pluck('id', 'feeder_id')->toArray();
        $dosensMap = User::where('role', 'dosen')->whereNotNull('feeder_id')->pluck('id', 'feeder_id')->toArray();
        $mhsMap    = User::where('role', 'mahasiswa')->whereNotNull('feeder_id')->pluck('id', 'feeder_id')->toArray();

        // Try multiple kelas action names
        $kelasAction = 'GetListKelasKuliah';
        $probeKelas  = $this->feeder->request('GetListKelasKuliah', [
            'filter' => "id_semester = {$semesterId}",
            'limit'  => 1, 'offset' => 0,
        ]);
        if (!$probeKelas['success']) {
            // Try alternative: quoted value
            $probeKelas2 = $this->feeder->request('GetListKelasKuliah', [
                'filter' => "id_semester = '{$semesterId}'",
                'limit'  => 1, 'offset' => 0,
            ]);
            if ($probeKelas2['success']) {
                $semesterId = "'{$semesterId}'"; // Use quoted form going forward
            } else {
                // Try without filter
                $probeNoFilter = $this->feeder->request('GetListKelasKuliah', ['limit' => 1, 'offset' => 0]);
                if ($probeNoFilter['success']) {
                    $semesterId = null; // Will filter by semester in PHP
                } else {
                    $errors[] = 'GetListKelasKuliah tidak tersedia: ' . $probeKelas['error_desc'];
                    $stats['errors_count']++;
                    $this->logSyncEnd($log, 'failed', $stats, $errors);
                    return ['success' => false, 'message' => $errors[0]];
                }
            }
        }

        $originalSemesterId = $activeSemester->feeder_semester_id;

        while (true) {
            $params = ['limit' => $limit, 'offset' => $offset];
            if ($semesterId !== null) {
                $params['filter'] = "id_semester = {$semesterId}";
            }

            $response = $this->feeder->request($kelasAction, $params);

            if (!$response['success']) {
                $errors[] = "{$kelasAction} offset={$offset}: " . $response['error_desc'];
                $stats['errors_count']++;
                break;
            }

            $kelasList = $response['data'] ?? [];
            if (empty($kelasList)) break;

            $stats['fetched'] += count($kelasList);

            foreach ($kelasList as $k) {
                // If no filter was used, filter by semester in PHP
                if ($semesterId === null) {
                    $kelSemId = $k['id_semester'] ?? $k['kode_semester'] ?? null;
                    if ($kelSemId && (string)$kelSemId !== (string)$originalSemesterId) continue;
                }

                $feederKelasId  = $k['id_kelas_kuliah'] ?? $k['id_kelas'] ?? null;
                $namaKelas      = $k['nama_kelas_kuliah'] ?? $k['nama_kelas'] ?? 'Kelas';
                $feederProdiId  = $k['id_prodi'] ?? null;
                $feederMatkulId = $k['id_matkul'] ?? $k['id_mata_kuliah'] ?? null;
                $kodeMatkul     = $k['kode_mata_kuliah'] ?? $k['kode_matkul'] ?? ('MK-' . substr($feederMatkulId ?? rand(1000, 9999), 0, 8));
                $namaMatkul     = $k['nama_mata_kuliah'] ?? $k['nama_matkul'] ?? $namaKelas;
                $sks            = (int)($k['sks_mata_kuliah'] ?? $k['sks'] ?? 2);
                $kapasitas      = (int)($k['kapasitas'] ?? 40);

                if (!$feederKelasId) continue;
                $allFeederIds[] = $feederKelasId;

                $prodiDbId = $feederProdiId ? ($prodisMap[$feederProdiId] ?? null) : null;

                try {
                    // Sync Course (Mata Kuliah)
                    $course = null;
                    if ($feederMatkulId) {
                        $course = Course::where('feeder_id', $feederMatkulId)->first();
                    }
                    if (!$course && $kodeMatkul) {
                        $course = Course::where('code', $kodeMatkul)->first();
                    }

                    $courseData = [
                        'feeder_id'        => $feederMatkulId,
                        'code'             => $kodeMatkul,
                        'name'             => $namaMatkul,
                        'prodi_id'         => $prodiDbId,
                        'sks'              => $sks,
                        'feeder_synced_at' => now(),
                    ];

                    if ($course) {
                        $course->update($courseData);
                    } else {
                        $course = Course::create($courseData);
                    }

                    // Get dosen pengajar for this kelas
                    $dosenDbId = null;
                    $dosenRes  = $this->feeder->request('GetDosenPengajarKelasKuliah', [
                        'filter' => "id_kelas_kuliah = '{$feederKelasId}'",
                        'limit'  => 5,
                        'offset' => 0,
                    ]);
                    if ($dosenRes['success'] && !empty($dosenRes['data'])) {
                        $fdId      = $dosenRes['data'][0]['id_registrasi_dosen'] ?? $dosenRes['data'][0]['id_dosen'] ?? null;
                        $dosenDbId = $fdId ? ($dosensMap[$fdId] ?? null) : null;
                    }

                    $combinedName = "{$namaMatkul} - {$namaKelas}";

                    // Sync ClassRoom
                    $classRoom = ClassRoom::where('feeder_id', $feederKelasId)->first();
                    $kelasData = [
                        'feeder_id'          => $feederKelasId,
                        'prodi_id'           => $prodiDbId,
                        'name'               => $combinedName,
                        'angkatan'           => $activeSemester->tahun_mulai,
                        'feeder_semester_id' => $semesterId,
                        'feeder_synced_at'   => now(),
                    ];

                    if ($classRoom) {
                        if ($dosenDbId) $kelasData['wali_kelas_id'] = $dosenDbId;
                        $classRoom->update($kelasData);
                        $stats['updated']++;
                    } else {
                        $kelasData['description']  = "Kelas {$namaMatkul} — Feeder {$semesterId}";
                        $kelasData['wali_kelas_id'] = $dosenDbId;
                        $classRoom = ClassRoom::create($kelasData);
                        $stats['inserted']++;
                    }

                    // Sync Peserta Kelas
                    $pesertaOffset = 0;
                    while (true) {
                        $pesertaRes = $this->feeder->request('GetPesertaKelasKuliah', [
                            'filter' => "id_kelas_kuliah = '{$feederKelasId}'",
                            'limit'  => 200,
                            'offset' => $pesertaOffset,
                        ]);
                        if (!$pesertaRes['success'] || empty($pesertaRes['data'])) break;

                        $pesertaFeederIds = [];
                        foreach ($pesertaRes['data'] as $p) {
                            $mhsFeederId = $p['id_registrasi_mahasiswa'] ?? $p['id_mahasiswa'] ?? null;
                            if ($mhsFeederId) $pesertaFeederIds[] = $mhsFeederId;
                        }

                        if (!empty($pesertaFeederIds)) {
                            // Update class_id for matched students
                            User::where('role', 'mahasiswa')
                                ->whereIn('feeder_id', $pesertaFeederIds)
                                ->update(['class_id' => $classRoom->id]);
                        }

                        if (count($pesertaRes['data']) < 200) break;
                        $pesertaOffset += 200;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Kelas [{$feederKelasId}]: " . $e->getMessage();
                    $stats['errors_count']++;
                    Log::warning("FeederSync kelas [{$feederKelasId}] error: " . $e->getMessage());
                }
            }

            // Real-time update of sync log after each batch
            $log->update([
                'total_fetched' => $stats['fetched'],
                'total_inserted' => $stats['inserted'],
                'total_updated' => $stats['updated'],
                'total_errors' => $stats['errors_count'],
            ]);

            if (count($kelasList) < $limit) break;
            $offset += $limit;
        }

        // Delete classes for the active semester that are no longer in Feeder
        if (!empty($allFeederIds)) {
            $deletedCount = ClassRoom::where('feeder_semester_id', $originalSemesterId)
                ->whereNotIn('feeder_id', $allFeederIds)
                ->delete();
            $stats['deactivated'] = $deletedCount;
        }

        $status = $stats['errors_count'] > 0 && $stats['fetched'] === 0 ? 'failed' : 'success';
        $this->logSyncEnd($log, $status, $stats, $errors ?: null,
            "Sync kelas untuk semester Feeder: {$originalSemesterId}");
        return ['success' => $status !== 'failed', 'stats' => $stats];
    }

    // ─────────────────────────────────────────────
    // 5b. SYNC ALL COURSES (MATA KULIAH)
    // ─────────────────────────────────────────────

    public function syncCourses(string $trigger = 'artisan', ?int $userId = null): array
    {
        $log          = $this->logSyncStart('courses', $trigger, $userId);
        $stats        = $this->emptyStats();
        $errors       = [];
        $limit        = 100;
        $offset       = 0;
        
        $prodisMap = Prodi::pluck('id', 'feeder_id')->toArray();

        while (true) {
            $response = $this->feeder->request('GetMataKuliah', [
                'limit'  => $limit,
                'offset' => $offset,
            ]);

            if (!$response['success']) {
                $errors[] = "GetMataKuliah offset={$offset} error: " . $response['error_desc'];
                $stats['errors_count']++;
                break;
            }

            $courses = $response['data'] ?? [];
            if (empty($courses)) break;

            $stats['fetched'] += count($courses);

            foreach ($courses as $c) {
                $feederId   = $c['id_matkul'] ?? null;
                $kode       = trim($c['kode_mata_kuliah'] ?? '');
                $nama       = trim($c['nama_mata_kuliah'] ?? '');
                $sks        = (int)($c['sks_mata_kuliah'] ?? 2);
                $feederProdiId = $c['id_prodi'] ?? null;
                $prodiDbId  = $feederProdiId ? ($prodisMap[$feederProdiId] ?? null) : null;

                if (empty($kode) || !$feederId) {
                    continue; 
                }

                try {
                    $course = Course::where('feeder_id', $feederId)
                        ->orWhere('code', $kode)
                        ->first();

                    $payload = [
                        'feeder_id'        => $feederId,
                        'code'             => $kode,
                        'name'             => $nama,
                        'prodi_id'         => $prodiDbId,
                        'sks'              => $sks,
                        'feeder_synced_at' => now(),
                    ];

                    if ($course) {
                        $course->update($payload);
                        $stats['updated']++;
                    } else {
                        Course::create($payload);
                        $stats['inserted']++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Matakuliah Kode={$kode}: " . $e->getMessage();
                    $stats['errors_count']++;
                    Log::warning("FeederSync courses Kode={$kode} error: " . $e->getMessage());
                }
            }

            // Real-time update of sync log after each batch
            $log->update([
                'total_fetched' => $stats['fetched'],
                'total_inserted' => $stats['inserted'],
                'total_updated' => $stats['updated'],
                'total_errors' => $stats['errors_count'],
            ]);

            if (count($courses) < $limit) break;
            $offset += $limit;
        }

        $status = $stats['errors_count'] > 0 && $stats['fetched'] === 0 ? 'failed' : 'success';
        $this->logSyncEnd($log, $status, $stats, $errors ?: null);
        return ['success' => $status !== 'failed', 'stats' => $stats];
    }

    // ─────────────────────────────────────────────
    // 6. SYNC ALL (1 Button)
    // ─────────────────────────────────────────────

    public function syncAll(string $trigger = 'manual_admin', ?int $userId = null): array
    {
        Log::info("FeederSync: Starting full sync triggered by: {$trigger}");

        $allStats = [];

        $steps = [
            'semester'  => fn() => $this->syncSemester($trigger, $userId),
            'prodi'     => fn() => $this->syncProdi($trigger, $userId),
            'courses'   => fn() => $this->syncCourses($trigger, $userId),
            'dosen'     => fn() => $this->syncDosen($trigger, $userId),
            'mahasiswa' => fn() => $this->syncMahasiswa($trigger, $userId),
            'kelas'     => fn() => $this->syncKelas($trigger, $userId),
        ];

        foreach ($steps as $entity => $fn) {
            try {
                $result           = $fn();
                $allStats[$entity] = $result['stats'] ?? $this->emptyStats();
            } catch (\Exception $e) {
                Log::error("FeederSync full sync step [{$entity}] exception: " . $e->getMessage());
                $allStats[$entity] = $this->emptyStats();
                $allStats[$entity]['errors_count'] = 1;
            }
        }

        return [
            'success' => true,
            'message' => 'Proses sinkronisasi data Neo Feeder selesai.',
            'stats'   => $allStats,
        ];
    }

    // ─────────────────────────────────────────────
    // 7. PEEK RAW FEEDER RESPONSE (Debug)
    // ─────────────────────────────────────────────

    public function peekRaw(string $act, array $params = []): array
    {
        return $this->feeder->request($act, $params);
    }
}
