<?php

namespace App\Http\Controllers;

use App\Models\TahunAkademik;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class TahunAkademikController extends Controller
{
    public function index()
    {
        $tahunAkademik = TahunAkademik::orderBy('tahun_mulai', 'desc')->orderBy('semester', 'asc')->get();
        return view('admin.tahun_akademik', compact('tahunAkademik'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tahun_mulai' => 'required|integer|min:2000|max:2099',
            'semester'    => 'required|in:ganjil,genap',
        ]);

        $semester = $data['semester'] === 'ganjil' ? 'Ganjil' : 'Genap';
        $data['nama'] = "{$data['tahun_mulai']}/" . ($data['tahun_mulai'] + 1) . " {$semester}";
        $data['is_aktif'] = false;

        $ta = TahunAkademik::create($data);
        ActivityLog::log('Tambah Tahun Akademik', "Menambahkan tahun akademik: {$ta->nama}.");

        return response()->json(['success' => true, 'message' => "Tahun akademik {$ta->nama} berhasil ditambahkan."]);
    }

    public function update(Request $request, $id)
    {
        $ta = TahunAkademik::findOrFail($id);

        $data = $request->validate([
            'tahun_mulai' => 'required|integer|min:2000|max:2099',
            'semester'    => 'required|in:ganjil,genap',
        ]);

        $semester = $data['semester'] === 'ganjil' ? 'Ganjil' : 'Genap';
        $data['nama'] = "{$data['tahun_mulai']}/" . ($data['tahun_mulai'] + 1) . " {$semester}";
        $ta->update($data);

        ActivityLog::log('Edit Tahun Akademik', "Mengubah tahun akademik: {$ta->nama}.");
        return response()->json(['success' => true, 'message' => 'Tahun akademik berhasil diperbarui.']);
    }

    public function setAktif($id)
    {
        $ta = TahunAkademik::findOrFail($id);
        $ta->setAktif();

        ActivityLog::log('Set Tahun Aktif', "Menetapkan tahun akademik aktif: {$ta->nama}.");
        return response()->json(['success' => true, 'message' => "{$ta->nama} ditetapkan sebagai tahun akademik aktif."]);
    }

    public function destroy($id)
    {
        $ta = TahunAkademik::findOrFail($id);

        if ($ta->is_aktif) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat menghapus tahun akademik yang sedang aktif.'], 422);
        }

        $nama = $ta->nama;
        $ta->delete();

        ActivityLog::log('Hapus Tahun Akademik', "Menghapus tahun akademik: {$nama}.");
        return response()->json(['success' => true, 'message' => 'Tahun akademik berhasil dihapus.']);
    }
}
