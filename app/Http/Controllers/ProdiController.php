<?php

namespace App\Http\Controllers;

use App\Models\Prodi;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProdiController extends Controller
{
    public function index()
    {
        $prodis = Prodi::withCount('users', 'courses', 'classes')->orderBy('nama')->get();
        return view('admin.prodi', compact('prodis'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode'       => 'required|string|max:20|unique:prodis',
            'nama'       => 'required|string|max:255',
            'jenjang'    => 'required|in:D3,D4,S1,S2,Profesi',
            'akreditasi' => 'nullable|string|max:50',
        ]);

        $data['is_aktif'] = true;
        $prodi = Prodi::create($data);

        ActivityLog::log('Tambah Prodi', "Menambahkan program studi: {$prodi->nama} ({$prodi->kode}).");
        return response()->json(['success' => true, 'message' => "Program studi {$prodi->nama} berhasil ditambahkan."]);
    }

    public function update(Request $request, $id)
    {
        $prodi = Prodi::findOrFail($id);

        $data = $request->validate([
            'kode'       => ['required', 'string', 'max:20', Rule::unique('prodis')->ignore($prodi->id)],
            'nama'       => 'required|string|max:255',
            'jenjang'    => 'required|in:D3,D4,S1,S2,Profesi',
            'akreditasi' => 'nullable|string|max:50',
            'is_aktif'   => 'nullable|boolean',
        ]);

        $prodi->update($data);

        ActivityLog::log('Edit Prodi', "Mengubah program studi: {$prodi->nama}.");
        return response()->json(['success' => true, 'message' => 'Program studi berhasil diperbarui.']);
    }

    public function destroy($id)
    {
        $prodi = Prodi::withCount('users', 'courses')->findOrFail($id);

        if ($prodi->users_count > 0 || $prodi->courses_count > 0) {
            return response()->json([
                'success' => false,
                'message' => "Tidak dapat menghapus prodi yang masih memiliki {$prodi->users_count} pengguna dan {$prodi->courses_count} mata kuliah terkait.",
            ], 422);
        }

        $nama = $prodi->nama;
        $prodi->delete();

        ActivityLog::log('Hapus Prodi', "Menghapus program studi: {$nama}.");
        return response()->json(['success' => true, 'message' => 'Program studi berhasil dihapus.']);
    }
}
