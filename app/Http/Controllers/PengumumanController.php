<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\Prodi;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function index()
    {
        $pengumumans = Pengumuman::with(['user', 'prodi'])->orderBy('created_at', 'desc')->get();
        $prodis = Prodi::where('is_aktif', true)->orderBy('nama')->get();
        return view('admin.pengumuman', compact('pengumumans', 'prodis'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul'            => 'required|string|max:255',
            'isi'              => 'required|string',
            'prodi_id'         => 'nullable|exists:prodis,id',
            'target'           => 'required|in:semua,mahasiswa,dosen',
            'tanggal_aktif'    => 'nullable|date',
            'tanggal_expired'  => 'nullable|date|after_or_equal:tanggal_aktif',
            'is_aktif'         => 'nullable|boolean',
        ]);

        $data['user_id'] = auth()->id();
        $data['is_aktif'] = $request->boolean('is_aktif', true);

        $pengumuman = Pengumuman::create($data);
        ActivityLog::log('Tambah Pengumuman', "Membuat pengumuman: {$pengumuman->judul}.");

        return response()->json(['success' => true, 'message' => 'Pengumuman berhasil diterbitkan.']);
    }

    public function update(Request $request, $id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        $data = $request->validate([
            'judul'            => 'required|string|max:255',
            'isi'              => 'required|string',
            'prodi_id'         => 'nullable|exists:prodis,id',
            'target'           => 'required|in:semua,mahasiswa,dosen',
            'tanggal_aktif'    => 'nullable|date',
            'tanggal_expired'  => 'nullable|date|after_or_equal:tanggal_aktif',
            'is_aktif'         => 'nullable|boolean',
        ]);

        $data['is_aktif'] = $request->boolean('is_aktif', true);
        $pengumuman->update($data);

        ActivityLog::log('Edit Pengumuman', "Mengubah pengumuman: {$pengumuman->judul}.");
        return response()->json(['success' => true, 'message' => 'Pengumuman berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        $judul = $pengumuman->judul;
        $pengumuman->delete();

        ActivityLog::log('Hapus Pengumuman', "Menghapus pengumuman: {$judul}.");
        return response()->json(['success' => true, 'message' => 'Pengumuman berhasil dihapus.']);
    }
}
