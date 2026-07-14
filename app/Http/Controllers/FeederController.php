<?php

namespace App\Http\Controllers;

use App\Models\SyncLog;
use App\Services\FeederService;
use App\Services\FeederSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeederController extends Controller
{
    public function __construct(
        protected FeederService $feeder,
        protected FeederSyncService $syncService,
    ) {}

    /**
     * Admin panel sinkronisasi Neo Feeder
     */
    public function index()
    {
        $recentLogs = SyncLog::orderBy('started_at', 'desc')->take(15)->get();

        // Test connection to get connection status
        $connectionStatus = $this->testFeederConnection();

        return view('admin.feeder.index', compact('recentLogs', 'connectionStatus'));
    }

    /**
     * Internal helper: Test koneksi Feeder
     */
    protected function testFeederConnection(): array
    {
        try {
            $token = $this->feeder->getToken(true);
            if ($token) {
                // Optionally call a lightweight action to test actual auth
                $res = $this->feeder->request('GetProdi', ['limit' => 1, 'offset' => 0]);
                if ($res['success']) {
                    return ['status' => 'ok', 'message' => 'Terhubung ke Neo Feeder'];
                } else {
                    return ['status' => 'error', 'message' => 'Token OK, namun request gagal: ' . $res['error_desc']];
                }
            } else {
                return ['status' => 'error', 'message' => 'Gagal mendapatkan token dari Neo Feeder'];
            }
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Exception: ' . $e->getMessage()];
        }
    }

    /**
     * Test koneksi secara AJAX
     */
    public function testConnection(Request $request)
    {
        $result = $this->testFeederConnection();
        return response()->json($result);
    }

    /**
     * Trigger sync (one entity or all)
     */
    public function sync(Request $request)
    {
        $request->validate([
            'entity' => 'required|in:all,semester,prodi,mahasiswa,dosen,kelas,courses',
        ]);

        $entity  = $request->input('entity');
        $userId  = Auth::id();

        // Release the session lock so frontend AJAX polling for logs is not blocked
        session_write_close();

        $result = match ($entity) {
            'semester'  => $this->syncService->syncSemester('manual_admin', $userId),
            'prodi'     => $this->syncService->syncProdi('manual_admin', $userId),
            'courses'   => $this->syncService->syncCourses('manual_admin', $userId),
            'mahasiswa' => $this->syncService->syncMahasiswa('manual_admin', $userId),
            'dosen'     => $this->syncService->syncDosen('manual_admin', $userId),
            'kelas'     => $this->syncService->syncKelas('manual_admin', $userId),
            default     => $this->syncService->syncAll('manual_admin', $userId),
        };

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'] ?? ($result['success'] ? 'Sinkronisasi berhasil.' : 'Sinkronisasi gagal.'),
            'stats'   => $result['stats'] ?? null,
        ]);
    }

    /**
     * Get latest sync logs as JSON (for polling progress)
     */
    public function logs(Request $request)
    {
        $logs = SyncLog::orderBy('started_at', 'desc')->take(15)->get()->map(function ($log) {
            return [
                'id'          => $log->id,
                'sync_type'   => $log->sync_type,
                'triggered_by'=> $log->triggered_by,
                'started_at'  => $log->started_at?->format('Y-m-d H:i:s'),
                'finished_at' => $log->finished_at?->format('Y-m-d H:i:s'),
                'status'      => $log->status,
                'total_fetched'   => $log->total_fetched,
                'total_inserted'  => $log->total_inserted,
                'total_updated'   => $log->total_updated,
                'total_deactivated' => $log->total_deactivated,
                'total_errors'    => $log->total_errors,
                'duration_seconds'=> $log->duration_seconds,
                'notes'           => $log->notes,
            ];
        });

        return response()->json(['logs' => $logs]);
    }

    /**
     * Show detail of one sync log
     */
    public function logDetail(int $id)
    {
        $log = SyncLog::findOrFail($id);
        return response()->json($log);
    }

    /**
     * Peek raw response from Neo Feeder (for debugging)
     */
    public function peek(Request $request)
    {
        $request->validate([
            'act'    => 'required|string|max:100',
            'filter' => 'nullable|string|max:255',
            'limit'  => 'nullable|integer|min:1|max:10',
        ]);

        $act    = $request->input('act');
        $filter = $request->input('filter', '');
        $limit  = (int)$request->input('limit', 3);

        $result = $this->syncService->peekRaw($act, [
            'filter' => $filter,
            'limit'  => $limit,
            'offset' => 0,
        ]);

        return response()->json([
            'success'    => $result['success'],
            'error_code' => $result['error_code'] ?? null,
            'error_desc' => $result['error_desc'] ?? null,
            'count'      => count($result['data'] ?? []),
            'fields'     => !empty($result['data']) ? array_keys($result['data'][0]) : [],
            'sample'     => array_slice($result['data'] ?? [], 0, 2),
        ]);
    }
}
