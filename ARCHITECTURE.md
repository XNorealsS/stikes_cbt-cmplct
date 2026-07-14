# ARSITEKTUR SISTEM
- Monolith terstruktur dengan Laravel 11 + Blade View.
- Untuk menghemat CPU Server, pengacakan urutan soal dilakukan SEKALI ketika mahasiswa mengklik "Mulai Ujian" dan disimpan permanen pada tabel `student_answers` di kolom `question_order`. Jangan lakukan pengacakan dinamis (ORDER BY RAND()) di setiap pemuatan halaman karena akan merusak performa server.
