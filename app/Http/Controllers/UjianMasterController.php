<?php

namespace App\Http\Controllers;

use App\Models\Ruang;
use App\Models\Sesi;
use App\Models\JenisUjian;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UjianMasterController extends Controller
{
    // =====================================================
    // RUANG UJIAN
    // =====================================================

    public function ruangIndex()
    {
        $ruangs = Ruang::withCount('exams')->orderBy('nama')->get();
        return view('admin.ruang', compact('ruangs'));
    }

    public function ruangStore(Request $request)
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:100',
            'kapasitas' => 'required|integer|min:1|max:500',
            'lokasi'    => 'nullable|string|max:255',
        ]);
        $data['is_aktif'] = true;
        $ruang = Ruang::create($data);

        ActivityLog::log('Tambah Ruang', "Menambahkan ruang ujian: {$ruang->nama}.");
        return response()->json(['success' => true, 'message' => "Ruang {$ruang->nama} berhasil ditambahkan."]);
    }

    public function ruangUpdate(Request $request, $id)
    {
        $ruang = Ruang::findOrFail($id);
        $data = $request->validate([
            'nama'      => 'required|string|max:100',
            'kapasitas' => 'required|integer|min:1|max:500',
            'lokasi'    => 'nullable|string|max:255',
            'is_aktif'  => 'nullable|boolean',
        ]);
        $ruang->update($data);

        ActivityLog::log('Edit Ruang', "Mengubah ruang ujian: {$ruang->nama}.");
        return response()->json(['success' => true, 'message' => 'Ruang ujian berhasil diperbarui.']);
    }

    public function ruangDestroy($id)
    {
        $ruang = Ruang::withCount('exams')->findOrFail($id);
        if ($ruang->exams_count > 0) {
            return response()->json(['success' => false, 'message' => 'Ruang ini masih digunakan oleh ' . $ruang->exams_count . ' ujian.'], 422);
        }
        $nama = $ruang->nama;
        $ruang->delete();

        ActivityLog::log('Hapus Ruang', "Menghapus ruang ujian: {$nama}.");
        return response()->json(['success' => true, 'message' => 'Ruang ujian berhasil dihapus.']);
    }

    // =====================================================
    // SESI WAKTU UJIAN
    // =====================================================

    public function sesiIndex()
    {
        $sesis = Sesi::withCount('exams')->orderBy('jam_mulai')->get();
        return view('admin.sesi', compact('sesis'));
    }

    public function sesiStore(Request $request)
    {
        $data = $request->validate([
            'nama'        => 'required|string|max:100',
            'jam_mulai'   => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ]);
        $data['is_aktif'] = true;
        $sesi = Sesi::create($data);

        ActivityLog::log('Tambah Sesi', "Menambahkan sesi ujian: {$sesi->nama}.");
        return response()->json(['success' => true, 'message' => "Sesi {$sesi->nama} berhasil ditambahkan."]);
    }

    public function sesiUpdate(Request $request, $id)
    {
        $sesi = Sesi::findOrFail($id);
        $data = $request->validate([
            'nama'        => 'required|string|max:100',
            'jam_mulai'   => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'is_aktif'    => 'nullable|boolean',
        ]);
        $sesi->update($data);

        ActivityLog::log('Edit Sesi', "Mengubah sesi ujian: {$sesi->nama}.");
        return response()->json(['success' => true, 'message' => 'Sesi ujian berhasil diperbarui.']);
    }

    public function sesiDestroy($id)
    {
        $sesi = Sesi::withCount('exams')->findOrFail($id);
        if ($sesi->exams_count > 0) {
            return response()->json(['success' => false, 'message' => 'Sesi ini masih digunakan oleh ' . $sesi->exams_count . ' ujian.'], 422);
        }
        $nama = $sesi->nama;
        $sesi->delete();

        ActivityLog::log('Hapus Sesi', "Menghapus sesi ujian: {$nama}.");
        return response()->json(['success' => true, 'message' => 'Sesi ujian berhasil dihapus.']);
    }

    // =====================================================
    // JENIS UJIAN
    // =====================================================

    public function jenisUjianIndex()
    {
        $jenisUjians = JenisUjian::withCount('exams')->orderBy('kode')->get();
        return view('admin.jenis_ujian', compact('jenisUjians'));
    }

    public function jenisUjianStore(Request $request)
    {
        $data = $request->validate([
            'kode'        => 'required|string|max:20|unique:jenis_ujians',
            'nama'        => 'required|string|max:255',
            'bobot_nilai' => 'required|numeric|min:0|max:100',
            'deskripsi'   => 'nullable|string',
        ]);
        $data['kode'] = strtoupper($data['kode']);
        $data['is_aktif'] = true;
        $jenis = JenisUjian::create($data);

        ActivityLog::log('Tambah Jenis Ujian', "Menambahkan jenis ujian: {$jenis->nama}.");
        return response()->json(['success' => true, 'message' => "Jenis ujian {$jenis->nama} berhasil ditambahkan."]);
    }

    public function jenisUjianUpdate(Request $request, $id)
    {
        $jenis = JenisUjian::findOrFail($id);
        $data = $request->validate([
            'kode'        => ['required', 'string', 'max:20', Rule::unique('jenis_ujians')->ignore($jenis->id)],
            'nama'        => 'required|string|max:255',
            'bobot_nilai' => 'required|numeric|min:0|max:100',
            'deskripsi'   => 'nullable|string',
            'is_aktif'    => 'nullable|boolean',
        ]);
        $data['kode'] = strtoupper($data['kode']);
        $jenis->update($data);

        ActivityLog::log('Edit Jenis Ujian', "Mengubah jenis ujian: {$jenis->nama}.");
        return response()->json(['success' => true, 'message' => 'Jenis ujian berhasil diperbarui.']);
    }

    public function jenisUjianDestroy($id)
    {
        $jenis = JenisUjian::withCount('exams')->findOrFail($id);
        if ($jenis->exams_count > 0) {
            return response()->json(['success' => false, 'message' => 'Jenis ujian ini masih digunakan oleh ' . $jenis->exams_count . ' ujian.'], 422);
        }
        $nama = $jenis->nama;
        $jenis->delete();

        ActivityLog::log('Hapus Jenis Ujian', "Menghapus jenis ujian: {$nama}.");
        return response()->json(['success' => true, 'message' => 'Jenis ujian berhasil dihapus.']);
    }
}
