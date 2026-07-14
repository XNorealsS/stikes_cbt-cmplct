# CONTEXT PROYEK E-LEARNING STIKES MUHAMMADIYAH LHOKSEUMAWE
Aplikasi ini adalah platform ujian online internal kampus.
Fokus Utama: Zero Bug pada Transaksi Jawaban, Anti-Curang, Ringan di VPS Rendah.
Aturan Mutlak:
1. Jangan biarkan query database berjalan n+1. Gunakan eager loading (with()).
2. Gunakan relasi Eloquent ORM secara ketat.
3. Gunakan database transaction (DB::transaction) saat menyimpan lembar jawaban akhir mahasiswa.
