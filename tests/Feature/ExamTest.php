<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\Question;
use App\Models\Exam;
use App\Models\StudentExam;
use App\Models\StudentAnswer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExamTest extends TestCase
{
    use RefreshDatabase;

    protected $mahasiswa;
    protected $dosen;
    protected $course;
    protected $exam;
    protected $questions = [];

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create Users
        $this->mahasiswa = User::create([
            'name' => 'Test Student',
            'username' => 'nim2026',
            'email' => 'student@stikes.ac.id',
            'role' => 'mahasiswa',
            'password' => bcrypt('password'),
        ]);

        $this->dosen = User::create([
            'name' => 'Test Lecturer',
            'username' => 'nip2026',
            'email' => 'lecturer@stikes.ac.id',
            'role' => 'dosen',
            'password' => bcrypt('password'),
        ]);

        // 2. Create Course
        $this->course = Course::create([
            'code' => 'TEST01',
            'name' => 'Uji Coba Sistem',
            'description' => 'Mata kuliah testing',
        ]);

        // 3. Create Questions (5 questions)
        for ($i = 1; $i <= 5; $i++) {
            $this->questions[] = Question::create([
                'course_id' => $this->course->id,
                'category' => 'Testing Unit',
                'difficulty' => 'mudah',
                'question_text' => "Pertanyaan ke-{$i}?",
                'option_a' => 'Opsi A',
                'option_b' => 'Opsi B',
                'option_c' => 'Opsi C',
                'option_d' => 'Opsi D',
                'option_e' => 'Opsi E',
                'correct_option' => 'A', // Key answer is always A
            ]);
        }

        // 4. Create Exam Session
        $this->exam = Exam::create([
            'course_id' => $this->course->id,
            'dosen_id' => $this->dosen->id,
            'title' => 'Ujian Unit Testing',
            'start_time' => now()->subMinutes(10),
            'end_time' => now()->addHours(2),
            'duration_minutes' => 60,
            'token' => 'UNITST',
            'is_random' => false,
            'total_questions' => 5,
        ]);
    }

    /**
     * Test authenticating and starting the exam with token
     */
    public function test_student_can_start_exam_with_correct_token(): void
    {
        $response = $this->actingAs($this->mahasiswa)
            ->postJson(route('api.exam.start'), [
                'exam_id' => $this->exam->id,
                'token' => 'UNITST',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'session_id'
            ])
            ->assertJson([
                'status' => 'success'
            ]);

        $sessionId = $response->json('session_id');

        // Verify StudentExam record is created
        $this->assertDatabaseHas('student_exams', [
            'id' => $sessionId,
            'user_id' => $this->mahasiswa->id,
            'exam_id' => $this->exam->id,
            'status' => 'progress',
        ]);

        // Verify answers are seeded and mapped to correct order
        $this->assertEquals(5, StudentAnswer::where('student_exam_id', $sessionId)->count());
    }

    /**
     * Test starting exam fails with incorrect token
     */
    public function test_student_cannot_start_exam_with_incorrect_token(): void
    {
        $response = $this->actingAs($this->mahasiswa)
            ->postJson(route('api.exam.start'), [
                'exam_id' => $this->exam->id,
                'token' => 'WRONGT',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Token ujian tidak valid. Silakan periksa kembali.'
            ]);
    }

    /**
     * Test saving an answer asynchronously
     */
    public function test_student_can_save_answer(): void
    {
        // Start session first
        $startRes = $this->actingAs($this->mahasiswa)
            ->postJson(route('api.exam.start'), [
                'exam_id' => $this->exam->id,
                'token' => 'UNITST',
            ]);
        $sessionId = $startRes->json('session_id');
        $questionId = $this->questions[0]->id;

        // Save answer A
        $response = $this->actingAs($this->mahasiswa)
            ->postJson(route('api.exam.save-answer'), [
                'student_exam_id' => $sessionId,
                'question_id' => $questionId,
                'option_id' => 'A',
                'is_doubtful' => false
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'saved'
            ]);

        // Verify recorded in DB
        $this->assertDatabaseHas('student_answers', [
            'student_exam_id' => $sessionId,
            'question_id' => $questionId,
            'selected_option' => 'A',
            'is_doubtful' => false
        ]);
    }

    /**
     * Test timer synchronization
     */
    public function test_timer_sync(): void
    {
        $startRes = $this->actingAs($this->mahasiswa)
            ->postJson(route('api.exam.start'), [
                'exam_id' => $this->exam->id,
                'token' => 'UNITST',
            ]);
        $sessionId = $startRes->json('session_id');

        $response = $this->actingAs($this->mahasiswa)
            ->getJson(route('api.exam.timer-sync', ['id' => $sessionId]));

        $response->assertStatus(200)
            ->assertJsonStructure(['remaining_seconds']);
        
        $this->assertLessThanOrEqual(3605, $response->json('remaining_seconds'));
    }

    /**
     * Test final submission and score calculation (all correct answers)
     */
    public function test_student_can_submit_final_and_calculates_100_percent_score(): void
    {
        $startRes = $this->actingAs($this->mahasiswa)
            ->postJson(route('api.exam.start'), [
                'exam_id' => $this->exam->id,
                'token' => 'UNITST',
            ]);
        $sessionId = $startRes->json('session_id');

        // Save correct option (A) for all 5 questions
        foreach ($this->questions as $q) {
            $this->actingAs($this->mahasiswa)
                ->postJson(route('api.exam.save-answer'), [
                    'student_exam_id' => $sessionId,
                    'question_id' => $q->id,
                    'option_id' => 'A',
                ]);
        }

        // Submit final
        $response = $this->actingAs($this->mahasiswa)
            ->postJson(route('api.exam.submit-final'), [
                'student_exam_id' => $sessionId,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'submited',
                'score' => 100
            ]);

        // Verify exam status is finished
        $this->assertDatabaseHas('student_exams', [
            'id' => $sessionId,
            'status' => 'finished',
            'score' => 100.00
        ]);
    }

    /**
     * Test class restriction on exams
     */
    public function test_student_cannot_start_exam_if_class_restriction_does_not_match(): void
    {
        $class1 = \App\Models\ClassRoom::create(['name' => 'Kelas A']);
        $class2 = \App\Models\ClassRoom::create(['name' => 'Kelas B']);

        // Assign student to Class A
        $this->mahasiswa->class_id = $class1->id;
        $this->mahasiswa->save();

        // Assign exam to Class B
        $this->exam->class_id = $class2->id;
        $this->exam->save();

        $response = $this->actingAs($this->mahasiswa)
            ->postJson(route('api.exam.start'), [
                'exam_id' => $this->exam->id,
                'token' => 'UNITST',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Ujian ini tidak ditujukan untuk kelas Anda.'
            ]);
    }
}
