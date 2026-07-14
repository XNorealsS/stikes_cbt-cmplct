<?php

namespace App\Console\Commands;

use App\Services\FeederSyncService;
use Illuminate\Console\Command;

class FeederSyncCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'feeder:sync
                            {--entity=all : Entity to sync: all|semester|prodi|mahasiswa|dosen|kelas}';

    protected $description = 'Sinkronisasi data dari Neo Feeder PDDIKTI ke sistem CBT STIKes Muhammadiyah Lhokseumawe';

    public function handle(FeederSyncService $syncService): int
    {
        $entity = $this->option('entity');

        $this->info('🔄 Memulai sinkronisasi Neo Feeder...');
        $this->line('Entitas: ' . strtoupper($entity));
        $this->line('Waktu  : ' . now()->format('Y-m-d H:i:s'));
        $this->line(str_repeat('─', 50));

        $start = microtime(true);

        $result = match ($entity) {
            'semester'  => $syncService->syncSemester(trigger: 'artisan'),
            'prodi'     => $syncService->syncProdi(trigger: 'artisan'),
            'mahasiswa' => $syncService->syncMahasiswa(trigger: 'artisan'),
            'dosen'     => $syncService->syncDosen(trigger: 'artisan'),
            'kelas'     => $syncService->syncKelas(trigger: 'artisan'),
            default     => $syncService->syncAll(trigger: 'artisan'),
        };

        $elapsed = round(microtime(true) - $start, 2);

        if ($entity === 'all' && isset($result['stats'])) {
            foreach ($result['stats'] as $ent => $s) {
                $this->line("  [{$ent}] Fetched: {$s['fetched']} | Inserted: {$s['inserted']} | Updated: {$s['updated']} | Deactivated: {$s['deactivated']} | Errors: {$s['errors_count']}");
            }
        } elseif (isset($result['stats'])) {
            $s = $result['stats'];
            $this->line("  Fetched: {$s['fetched']} | Inserted: {$s['inserted']} | Updated: {$s['updated']} | Deactivated: {$s['deactivated']} | Errors: {$s['errors_count']}");
        }

        $this->line(str_repeat('─', 50));

        if ($result['success'] ?? false) {
            $this->info("✅ Sinkronisasi selesai dalam {$elapsed} detik.");
            return self::SUCCESS;
        } else {
            $this->error('❌ Sinkronisasi gagal: ' . ($result['message'] ?? 'Unknown error'));
            return self::FAILURE;
        }
    }
}
