<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use App\Models\ClassRoom;
use App\Models\Question;
use App\Models\Exam;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use App\Models\Ruang;
use App\Models\Sesi;
use App\Models\JenisUjian;
use App\Models\BankSoal;
use App\Models\Materi;
use App\Models\Tugas;
use App\Models\TugasSubmission;
use App\Models\StudentExam;
use App\Models\StudentAnswer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Clean up existing records
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        User::truncate();
        Course::truncate();
        ClassRoom::truncate();
        Question::truncate();
        Exam::truncate();
        Prodi::truncate();
        TahunAkademik::truncate();
        Ruang::truncate();
        Sesi::truncate();
        JenisUjian::truncate();
        BankSoal::truncate();
        Materi::truncate();
        Tugas::truncate();
        TugasSubmission::truncate();
        StudentExam::truncate();
        StudentAnswer::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 2. Seed Prodi
        $prodi1 = Prodi::create([
            'kode' => 'S1-KEP',
            'nama' => 'S1 Ilmu Keperawatan',
            'jenjang' => 'S1',
            'akreditasi' => 'Baik Sekali',
            'is_aktif' => true,
        ]);

        $prodi2 = Prodi::create([
            'kode' => 'D3-KEB',
            'nama' => 'D3 Kebidanan',
            'jenjang' => 'D3',
            'akreditasi' => 'Unggul',
            'is_aktif' => true,
        ]);

        // 3. Seed Tahun Akademik
        $ta = TahunAkademik::create([
            'nama' => '2025/2026 Ganjil',
            'tahun_mulai' => 2025,
            'semester' => 'ganjil',
            'is_aktif' => true,
        ]);

        // 4. Seed Ruang
        $ruang1 = Ruang::create([
            'nama' => 'Lab Komputer A',
            'kapasitas' => 40,
            'lokasi' => 'Gedung A Lantai 2',
            'is_aktif' => true,
        ]);

        $ruang2 = Ruang::create([
            'nama' => 'Lab Komputer B',
            'kapasitas' => 30,
            'lokasi' => 'Gedung B Lantai 1',
            'is_aktif' => true,
        ]);

        // 5. Seed Sesi
        $sesi1 = Sesi::create([
            'nama' => 'Sesi 1',
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '10:00:00',
            'is_aktif' => true,
        ]);

        $sesi2 = Sesi::create([
            'nama' => 'Sesi 2',
            'jam_mulai' => '10:30:00',
            'jam_selesai' => '12:30:00',
            'is_aktif' => true,
        ]);

        // 6. Seed Jenis Ujian
        foreach (JenisUjian::defaults() as $def) {
            JenisUjian::create($def);
        }
        $jenisUTS = JenisUjian::where('kode', 'UTS')->first();

        // 7. Seed Admin
        User::create([
            'name' => 'Administrator CBT',
            'username' => 'admin',
            'email' => 'admin@stikes.ac.id',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        // 8. Seed Dosen
        $dosen = User::create([
            'name' => 'Dr. Budi Utomo, M.Kep.',
            'username' => 'dosen',
            'email' => 'dosen@stikes.ac.id',
            'role' => 'dosen',
            'nidn' => '1234567890',
            'prodi_id' => $prodi1->id,
            'password' => Hash::make('dxdosen@'),
        ]);

        // 9. Seed Class
        $class = ClassRoom::create([
            'name' => 'S1 Keperawatan - 2025/2026 Ganjil',
            'prodi_id' => $prodi1->id,
            'angkatan' => '2025',
            'wali_kelas_id' => $dosen->id,
        ]);

        // 10. Seed Mahasiswa
        $mahasiswa = User::create([
            'name' => 'Mahasiswa Demo',
            'username' => '250101001',
            'email' => 'mahasiswa@stikes.ac.id',
            'role' => 'mahasiswa',
            'nim' => '250101001',
            'class_id' => $class->id,
            'prodi_id' => $prodi1->id,
            'angkatan' => '2025',
            'password' => Hash::make('250101001'),
        ]);

        // 11. Seed Course
        $course = Course::create([
            'code' => 'KDP101',
            'name' => 'Keperawatan Dasar Profesional',
            'description' => 'Mata kuliah dasar untuk keterampilan keperawatan profesional.',
            'prodi_id' => $prodi1->id,
            'sks' => 3,
            'is_praktikum' => false,
        ]);

        // 11.5. Seed Bank Soal
        $bankSoal = BankSoal::create([
            'nama' => 'Bank Soal Keperawatan Dasar Profesional',
            'kode' => 'KDP101-BS',
            'course_id' => $course->id,
            'dosen_id' => $dosen->id,
            'deskripsi' => 'Kumpulan soal untuk mata kuliah Keperawatan Dasar Profesional.',
            'is_aktif' => true,
        ]);

        // 12. Seed Questions
        $questions = [  
            [
                'question_text' => 'Tindakan keperawatan manakah yang paling prioritas saat menangani pasien syok hipovolemik?',
                'option_a' => 'Memberikan posisi Trendelenburg',
                'option_b' => 'Memasang infus dua jalur dengan cairan ringer laktat',
                'option_c' => 'Memberikan oksigen nasal kanul 10 liter/menit',
                'option_d' => 'Mengukur tanda-tanda vital setiap 1 jam',
                'option_e' => 'Memberikan injeksi analgesik intravena',
                'correct_option' => 'B',
                'question_type' => 'pg',
            ],
            [
                'question_text' => 'Berapakah frekuensi denyut nadi normal per menit untuk orang dewasa sehat pada kondisi istirahat?',
                'option_a' => '40 - 60 kali/menit',
                'option_b' => '60 - 100 kali/menit',
                'option_c' => '90 - 120 kali/menit',
                'option_d' => '100 - 140 kali/menit',
                'option_e' => '50 - 80 kali/menit',
                'correct_option' => 'B',
                'question_type' => 'pg',
            ],
            [
                'question_text' => 'Manakah yang termasuk dalam klasifikasi luka dekubitus derajat II?',
                'option_a' => 'Kulit kemerahan yang tidak memucat saat ditekan',
                'option_b' => 'Hilangnya sebagian ketebalan kulit dermis berupa lepuhan',
                'option_c' => 'Hilangnya seluruh ketebalan kulit hingga jaringan subkutan terlihat',
                'option_d' => 'Hilangnya seluruh jaringan kulit hingga tulang dan otot terlihat',
                'option_e' => 'Luka tertutup eskar tebal hitam',
                'correct_option' => 'B',
                'question_type' => 'pg',
            ],
            [
                'question_text' => 'Sebelum memberikan obat digoksin, tindakan keperawatan apa yang wajib dilakukan?',
                'option_a' => 'Mengukur tekanan darah sistolik',
                'option_b' => 'Menghitung denyut nadi apikal selama 1 menit penuh',
                'option_c' => 'Mengukur saturasi oksigen perifer',
                'option_d' => 'Memeriksa hasil laboratorium ureum',
                'option_e' => 'Melakukan tes alergi kulit',
                'correct_option' => 'B',
                'question_type' => 'pg',
            ],
            [
                'question_text' => 'Apa tujuan utama dilakukannya teknik deep breathing exercise pada pasien pasca operasi abdominal?',
                'option_a' => 'Menurunkan tingkat kecemasan pasien',
                'option_b' => 'Mencegah terjadinya atelektasis paru',
                'option_c' => 'Mengurangi intensitas nyeri daerah insisi',
                'option_d' => 'Mempercepat mobilisasi fisik',
                'option_e' => 'Mencegah infeksi nosokomial lambung',
                'correct_option' => 'B',
                'question_type' => 'pg',
            ],
            [
                'question_text' => 'Nilai GCS (Glasgow Coma Scale) 9 menunjukkan klasifikasi cedera kepala tingkat apa?',
                'option_a' => 'Cedera Kepala Ringan (CKR)',
                'option_b' => 'Cedera Kepala Sedang (CKS)',
                'option_c' => 'Cedera Kepala Berat (CKB)',
                'option_d' => 'Coma',
                'option_e' => 'Sadar Penuh',
                'correct_option' => 'B',
                'question_type' => 'pg',
            ],
            [
                'question_text' => 'Untuk mencegah transmisi tuberkulosis, jenis masker apa yang wajib digunakan oleh perawat di ruang isolasi?',
                'option_a' => 'Masker bedah standar',
                'option_b' => 'Masker respirator N95',
                'option_c' => 'Masker kain tiga lapis',
                'option_d' => 'Masker oksigen rebreathing',
                'option_e' => 'Surgical face shield saja',
                'correct_option' => 'B',
                'question_type' => 'pg',
            ],
            [
                'question_text' => 'Berapa milimeter merkuri (mmHg) batasan sistolik untuk mendiagnosis Hipertensi Stadium 1 menurut JNC 8?',
                'option_a' => '120 - 129 mmHg',
                'option_b' => '130 - 139 mmHg',
                'option_c' => '140 - 159 mmHg',
                'option_d' => '>= 160 mmHg',
                'option_e' => '110 - 119 mmHg',
                'correct_option' => 'C',
                'question_type' => 'pg',
            ],
            [
                'question_text' => 'Seorang pasien mengeluh nyeri dada kiri menjalar ke lengan kiri. Apa tindakan diagnosis awal mandiri perawat yang utama?',
                'option_a' => 'Memasang kateter urine',
                'option_b' => 'Melakukan perekaman Elektrokardiogram (EKG) 12 Lead',
                'option_c' => 'Mengambil sampel darah perifer lengkap',
                'option_d' => 'Mengatur posisi semifowler dan kolaborasi analgesik kuat',
                'option_e' => 'Memberikan minum air hangat',
                'correct_option' => 'B',
                'question_type' => 'pg',
            ],
            [
                'question_text' => 'Berapa tetes per menit (tpm) infus set makro (1 cc = 20 tetes) jika cairan Ringer Laktat 500 ml harus habis dalam waktu 8 jam?',
                'option_a' => '15 tetes/menit',
                'option_b' => '21 tetes/menit',
                'option_c' => '28 tetes/menit',
                'option_d' => '32 tetes/menit',
                'option_e' => '42 tetes/menit',
                'correct_option' => 'B',
                'question_type' => 'pg',
            ]
        ];

        foreach ($questions as $q) {
            $q['course_id'] = $course->id;
            $q['bank_soal_id'] = $bankSoal->id;
            Question::create($q);
        }

        // 13. Seed 1 Active Exam
        Exam::create([
            'course_id' => $course->id,
            'bank_soal_id' => $bankSoal->id,
            'dosen_id' => $dosen->id,
            'class_id' => $class->id,
            'tahun_akademik_id' => $ta->id,
            'jenis_ujian_id' => $jenisUTS->id,
            'ruang_id' => $ruang1->id,
            'sesi_id' => $sesi1->id,
            'exam_type' => 'UTS',
            'title' => 'Ujian Tengah Semester (UTS) KDP',
            'description' => 'Ujian Utama Teori Keperawatan Dasar Profesional.',
            'petunjuk' => 'Kerjakan dengan jujur. Pilihlah satu jawaban yang paling tepat.',
            'start_time' => now()->subHours(1),
            'end_time' => now()->addHours(5),
            'duration_minutes' => 60,
            'token' => 'UTS123',
            'is_random' => true,
            'total_questions' => 10,
            'passing_grade' => 60.00,
        ]);

        // 14. Seed Materi Pembelajaran for Elearning
        Materi::create([
            'user_id' => $dosen->id,
            'course_id' => $course->id,
            'class_id' => $class->id,
            'tahun_akademik_id' => $ta->id,
            'judul' => 'Modul 1: Konsep & Teori Keperawatan Dasar',
            'deskripsi' => 'Modul pembelajaran ini menjelaskan falsafah dasar keperawatan profesional.',
            'tipe' => 'file',
            'file_path' => 'materi/modul_1_konsep_keperawatan.pdf',
            'tanggal_tayang' => now()->subDays(5),
            'is_aktif' => true,
        ]);

        Materi::create([
            'user_id' => $dosen->id,
            'course_id' => $course->id,
            'class_id' => $class->id,
            'tahun_akademik_id' => $ta->id,
            'judul' => 'Modul 2: Etika Keperawatan & Komunikasi Terapeutik',
            'deskripsi' => 'Video pembelajaran mengenai asuhan keperawatan dan komunikasi terapeutik dengan pasien.',
            'tipe' => 'link',
            'link_url' => 'https://www.youtube.com/watch?v=gUWJ-6nL5-8',
            'tanggal_tayang' => now()->subDays(3),
            'is_aktif' => true,
        ]);

        Materi::create([
            'user_id' => $dosen->id,
            'course_id' => $course->id,
            'class_id' => $class->id,
            'tahun_akademik_id' => $ta->id,
            'judul' => 'Modul 3: Ringkasan Prosedur Pemeriksaan Fisik (TTV)',
            'deskripsi' => 'Panduan singkat tentang cara menghitung tanda-tanda vital pada pasien dewasa.',
            'tipe' => 'text',
            'konten' => "A. PENDAHULUAN\nPemeriksaan tanda-tanda vital (TTV) merupakan salah satu tindakan keperawatan yang paling dasar namun krusial. TTV digunakan untuk mendeteksi adanya perubahan sistem tubuh secara cepat. Komponen utama TTV meliputi Tekanan Darah (TD), Nadi (HR), Pernapasan (RR), dan Suhu Tubuh (T).\n\nB. PANDUAN PROSEDUR & DIAGNOSIS\n1. Tekanan Darah (Blood Pressure)\nTekanan darah adalah gaya dorong darah terhadap dinding arteri saat jantung memompa.\n- Nilai Normal Dewasa: 120/80 mmHg.\n- Hipotensi: < 90/60 mmHg.\n- Hipertensi: > 140/90 mmHg.\n*Prosedur:* Pastikan pasien beristirahat minimal 5 menit sebelum pengukuran. Pasang manset 2-3 cm di atas arteri brakialis pada lengan atas. Pompa manset hingga denyut radial menghilang, lalu naikkan 20-30 mmHg. Turunkan tekanan secara perlahan (2-3 mmHg/detik) sambil mendengarkan bunyi Korotkoff I (Sistolik) dan bunyi Korotkoff V (Diastolik).\n\n2. Pengukuran Denyut Nadi (Heart Rate / Pulse)\nNadi merepresentasikan denyut jantung yang teraba pada arteri perifer akibat kontraksi ventrikel kiri.\n- Nilai Normal Dewasa: 60 - 100 kali per menit.\n- Takikardia (Nadi cepat): > 100 kali per menit.\n- Bradikardia (Nadi lambat): < 60 kali per menit.\n*Prosedur:* Lakukan palpasi menggunakan tiga jari (telunjuk, tengah, manis) pada arteri radialis di pergelangan tangan bagian dalam segaris jempol. Hitung denyut selama 60 detik penuh jika nadi tidak teratur, atau 30 detik dikalikan 2 jika denyut teratur. Catat frekuensi, irama (teratur/tidak teratur), dan kekuatan denyutan.\n\n3. Frekuensi Pernapasan (Respiratory Rate)\nPernapasan adalah proses pertukaran oksigen dan karbondioksida antara tubuh dengan lingkungan luar.\n- Nilai Normal Dewasa: 12 - 20 kali per menit.\n- Takipnea: > 20 kali per menit.\n- Bradipnea: < 12 kali per menit.\n*Prosedur:* Hitung frekuensi napas tanpa disadari oleh pasien (biasanya dilakukan langsung setelah meraba nadi dengan membiarkan jari tetap menempel pada pergelangan tangan pasien). Amati satu siklus pernapasan penuh (inspirasi + ekspirasi) selama 60 detik. Amati juga kedalaman pernapasan dan penggunaan otot bantu napas.\n\n4. Suhu Tubuh (Body Temperature)\nSuhu tubuh mencerminkan keseimbangan antara panas yang diproduksi oleh tubuh dengan panas yang dilepaskan ke lingkungan sekitar.\n- Nilai Normal: 36.5 - 37.5 derajat Celcius.\n- Febris/Hipertermia (Demam): > 37.5 derajat Celcius.\n- Hipotermia: < 36.0 derajat Celcius.\n*Prosedur:* Pengukuran dapat dilakukan di area Aksila (ketiak), Oral (mulut), maupun Rektal (dubur). Bersihkan ujung termometer menggunakan alkohol swab, pasangkan pada puncak ketiak pasien secara tepat, minta pasien menyilangkan tangan ke dada, lalu tunggu hingga alarm termometer berbunyi.\n\nC. KESIMPULAN & DOKUMENTASI\nSegera catat hasil pemeriksaan TTV pada lembar observasi klinis (chart pasien). Apabila ditemukan nilai TTV yang menyimpang secara signifikan dari rentang normal atau terjadi perubahan mendadak, segera laporkan temuan tersebut kepada dokter penanggung jawab pasien untuk tindakan kolaboratif lebih lanjut.",
            'tanggal_tayang' => now()->subDays(1),
            'is_aktif' => true,
        ]);

        // 15. Seed Tugas Kuliah
        $tugas1 = Tugas::create([
            'user_id' => $dosen->id,
            'course_id' => $course->id,
            'class_id' => $class->id,
            'tahun_akademik_id' => $ta->id,
            'judul' => 'Tugas 1: Resume Teori Keperawatan Florence Nightingale',
            'deskripsi' => 'Tulis resume minimal 2 halaman dalam format PDF mengenai kontribusi Florence Nightingale terhadap dunia keperawatan.',
            'poin_nilai' => 100.00,
            'deadline' => now()->addDays(5),
            'tanggal_tayang' => now()->subDays(3),
            'is_aktif' => true,
        ]);

        $tugas2 = Tugas::create([
            'user_id' => $dosen->id,
            'course_id' => $course->id,
            'class_id' => $class->id,
            'tahun_akademik_id' => $ta->id,
            'judul' => 'Tugas 2: Laporan Pemeriksaan Fisik Tanda-Tanda Vital',
            'deskripsi' => 'Kumpulkan lembar laporan praktikum TTV yang sudah ditandatangani instruktur lab dalam format PDF.',
            'poin_nilai' => 100.00,
            'deadline' => now()->addDays(2),
            'tanggal_tayang' => now()->subDays(4),
            'is_aktif' => true,
        ]);

        // Student submits Tugas 2 (but not graded yet)
        TugasSubmission::create([
            'tugas_id' => $tugas2->id,
            'user_id' => $mahasiswa->id,
            'file_path' => 'submissions/laporan_ttv_nim.pdf',
            'catatan' => 'Izin mengumpulkan laporan pemeriksaan TTV. Terima kasih, pak.',
            'nilai' => null,
            'feedback_dosen' => null,
            'submitted_at' => now()->subDays(1),
        ]);

        $tugas3 = Tugas::create([
            'user_id' => $dosen->id,
            'course_id' => $course->id,
            'class_id' => $class->id,
            'tahun_akademik_id' => $ta->id,
            'judul' => 'Tugas 3: Studi Kasus Asuhan Keperawatan Pasien Hipertensi',
            'deskripsi' => 'Analisislah studi kasus terlampir dan susun asuhan keperawatan lengkap mulai dari pengkajian hingga evaluasi.',
            'poin_nilai' => 100.00,
            'deadline' => now()->subDays(2),
            'tanggal_tayang' => now()->subDays(7),
            'is_aktif' => true,
        ]);

        // Student submits Tugas 3 (already graded by Dosen)
        TugasSubmission::create([
            'tugas_id' => $tugas3->id,
            'user_id' => $mahasiswa->id,
            'file_path' => 'submissions/studi_kasus_hipertensi.pdf',
            'catatan' => 'Berikut tugas kasus asuhan keperawatan hipertensi saya.',
            'nilai' => 88.50,
            'feedback_dosen' => 'Analisis diagnosa keperawatan sudah tepat. Rencana tindakan bisa ditingkatkan lagi pada bagian edukasi keluarga.',
            'submitted_at' => now()->subDays(4),
        ]);

        // 16. Seed Past Ujian (for Exam History)
        $pastExam = Exam::create([
            'course_id' => $course->id,
            'bank_soal_id' => $bankSoal->id,
            'dosen_id' => $dosen->id,
            'class_id' => $class->id,
            'tahun_akademik_id' => $ta->id,
            'jenis_ujian_id' => $jenisUTS->id,
            'ruang_id' => $ruang1->id,
            'sesi_id' => $sesi1->id,
            'exam_type' => 'UTS',
            'title' => 'Kuis Pendahuluan Keperawatan Dasar',
            'description' => 'Evaluasi awal pemahaman modul pengenalan dasar keperawatan.',
            'petunjuk' => 'Pilih satu jawaban terbaik.',
            'start_time' => now()->subDays(8)->subHours(2),
            'end_time' => now()->subDays(8)->addHours(4),
            'duration_minutes' => 30,
            'token' => 'KUIS01',
            'is_random' => false,
            'total_questions' => 3,
            'passing_grade' => 60.00,
        ]);

        // StudentExam record for this past exam
        $studentExam = StudentExam::create([
            'user_id' => $mahasiswa->id,
            'exam_id' => $pastExam->id,
            'started_at' => now()->subDays(8)->subHour(),
            'finished_at' => now()->subDays(8)->subHour()->addMinutes(15),
            'score' => 100.00,
            'status' => 'finished',
        ]);

        // Find the first 3 questions seeded in this course to link to answers
        $dbQuestions = Question::where('course_id', $course->id)->take(3)->get();

        $order = 1;
        foreach ($dbQuestions as $q) {
            StudentAnswer::create([
                'student_exam_id' => $studentExam->id,
                'question_id' => $q->id,
                'selected_option' => $q->correct_option, // All correct to get 100
                'is_correct' => true,
                'question_order' => $order++,
            ]);
        }
    }
}
