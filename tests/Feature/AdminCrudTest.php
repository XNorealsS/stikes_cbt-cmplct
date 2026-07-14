<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\ClassRoom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@stikes.ac.id',
            'role' => 'admin',
            'password' => bcrypt('password'),
        ]);

        $this->class = ClassRoom::create([
            'name' => 'Tingkat I - Kelas A',
            'description' => 'Test Deskripsi',
        ]);
    }

    public function test_admin_can_access_classes_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.classes.index'));

        $response->assertStatus(200);
        $response->assertSee('Tingkat I - Kelas A');
    }

    public function test_admin_can_create_class(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.classes.store'), [
                'name' => 'Tingkat I - Kelas B',
                'description' => 'Kelas Baru',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('classes', [
            'name' => 'Tingkat I - Kelas B',
        ]);
    }

    public function test_admin_can_update_class(): void
    {
        $response = $this->actingAs($this->admin)
            ->putJson(route('admin.classes.update', $this->class->id), [
                'name' => 'Tingkat I - Kelas A Suntingan',
                'description' => 'Deskripsi Suntingan',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('classes', [
            'id' => $this->class->id,
            'name' => 'Tingkat I - Kelas A Suntingan',
        ]);
    }

    public function test_admin_can_delete_class(): void
    {
        $response = $this->actingAs($this->admin)
            ->deleteJson(route('admin.classes.destroy', $this->class->id));

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('classes', [
            'id' => $this->class->id,
        ]);
    }

    public function test_admin_can_create_student_with_class(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.users.store'), [
                'name' => 'Siswa Baru',
                'username' => 'nim1234',
                'email' => 'siswa@stikes.ac.id',
                'role' => 'mahasiswa',
                'class_id' => $this->class->id,
                'password' => 'password123',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'username' => 'nim1234',
            'class_id' => $this->class->id,
            'role' => 'mahasiswa',
        ]);
    }

    public function test_admin_can_fetch_class_students(): void
    {
        $student = User::create([
            'name' => 'Siswa Test',
            'username' => 'nim5678',
            'email' => 'siswa_test@stikes.ac.id',
            'role' => 'mahasiswa',
            'class_id' => $this->class->id,
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.classes.students', $this->class->id));

        $response->assertStatus(200)
            ->assertJsonFragment([
                'success' => true,
                'class_name' => $this->class->name,
                'name' => 'Siswa Test'
            ]);
    }

    public function test_admin_can_access_monitoring(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.monitoring.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_control_exam_sessions(): void
    {
        $course = Course::create([
            'code' => 'TEST101',
            'name' => 'Test Course',
        ]);

        $dosen = User::create([
            'name' => 'Dosen Test',
            'username' => 'dosen_test',
            'email' => 'dosen_test@stikes.ac.id',
            'role' => 'dosen',
            'password' => bcrypt('password'),
        ]);

        $student = User::create([
            'name' => 'Siswa Test 2',
            'username' => 'nim56789',
            'email' => 'siswa_test2@stikes.ac.id',
            'role' => 'mahasiswa',
            'class_id' => $this->class->id,
            'password' => bcrypt('password'),
        ]);

        $exam = \App\Models\Exam::create([
            'course_id' => $course->id,
            'dosen_id' => $dosen->id,
            'title' => 'Ujian Tengah Semester',
            'start_time' => now()->subHour(),
            'end_time' => now()->addHour(),
            'duration_minutes' => 60,
            'token' => 'TOKENX',
            'is_random' => true,
            'total_questions' => 50,
        ]);

        $session = \App\Models\StudentExam::create([
            'user_id' => $student->id,
            'exam_id' => $exam->id,
            'started_at' => now(),
            'status' => 'progress',
        ]);

        // 1. Toggle Pending (Pause)
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.monitoring.toggle-pending', $session->id));

        $response->assertStatus(200);
        $this->assertDatabaseHas('student_exams', [
            'id' => $session->id,
            'status' => 'pending',
        ]);

        // 2. Toggle Pending (Resume)
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.monitoring.toggle-pending', $session->id));

        $response->assertStatus(200);
        $this->assertDatabaseHas('student_exams', [
            'id' => $session->id,
            'status' => 'progress',
        ]);

        // 3. Adjust time
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.monitoring.adjust-time', $exam->id), [
                'duration_minutes' => 90,
                'end_time' => now()->addHours(2)->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('exams', [
            'id' => $exam->id,
            'duration_minutes' => 90,
        ]);

        // 4. Reset Student Exam
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.monitoring.reset', $session->id));

        $response->assertStatus(200);
        $this->assertDatabaseMissing('student_exams', [
            'id' => $session->id,
        ]);
    }
}
