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
        \App\Models\StudentExam::truncate();
        \App\Models\StudentAnswer::truncate();
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
            Question::create($q);
        }

        // 13. Seed 1 Active Exam
        Exam::create([
            'course_id' => $course->id,
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
    }
}
