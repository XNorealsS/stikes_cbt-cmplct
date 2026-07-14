<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\ClassRoom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FeederSyncTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $feederUrl = 'https://feeder.stikeslhokseumawe.ac.id/ws/live2.php';

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

        // Clean up config file in testing storage if any
        Storage::delete('feeder_config.json');
    }

    public function test_admin_can_access_feeder_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.feeder.index'));

        $response->assertStatus(200);
        $response->assertSee('Integrasi PDDikti Neo Feeder');
    }

    public function test_admin_can_save_feeder_configuration(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.feeder.config'), [
                'url' => $this->feederUrl,
                'username' => 'ws_user_test',
                'password' => 'ws_pass_test',
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertTrue(Storage::exists('feeder_config.json'));
        $config = json_decode(Storage::get('feeder_config.json'), true);
        $this->assertEquals('ws_user_test', $config['username']);
    }

    public function test_feeder_connection_test_success(): void
    {
        Http::fake([
            $this->feederUrl => Http::response([
                'error_code' => 0,
                'error_desc' => '',
                'data' => [
                    'token' => 'active-mock-token-xyz'
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.feeder.test'), [
                'url' => $this->feederUrl,
                'username' => 'ws_user_test',
                'password' => 'ws_pass_test',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'token' => 'active-mock-token-xyz'
            ]);
    }

    public function test_feeder_sync_courses(): void
    {
        // First save config
        $this->actingAs($this->admin)
            ->postJson(route('admin.feeder.config'), [
                'url' => $this->feederUrl,
                'username' => 'ws_user_test',
                'password' => 'ws_pass_test',
            ]);

        Http::fake([
            $this->feederUrl => Http::sequence()
                // Response for GetToken
                ->push([
                    'error_code' => 0,
                    'error_desc' => '',
                    'data' => ['token' => 'mock-token']
                ])
                // Response for GetListMataKuliah (first page)
                ->push([
                    'error_code' => 0,
                    'error_desc' => '',
                    'data' => [
                        [
                            'kode_mata_kuliah' => 'MK-MOCK-01',
                            'nama_mata_kuliah' => 'Mock Course A',
                            'sks_mata_kuliah' => 3
                        ],
                        [
                            'kode_mata_kuliah' => 'MK-MOCK-02',
                            'nama_mata_kuliah' => 'Mock Course B',
                            'sks_mata_kuliah' => 2
                        ]
                    ]
                ])
                // Response for GetListMataKuliah (second page - returns empty to break loop)
                ->push([
                    'error_code' => 0,
                    'error_desc' => '',
                    'data' => []
                ])
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.feeder.sync'), [
                'type' => 'courses'
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('courses', [
            'code' => 'MK-MOCK-01',
            'name' => 'Mock Course A',
        ]);

        $this->assertDatabaseHas('courses', [
            'code' => 'MK-MOCK-02',
            'name' => 'Mock Course B',
        ]);
    }

    public function test_feeder_sync_dosen(): void
    {
        // First save config
        $this->actingAs($this->admin)
            ->postJson(route('admin.feeder.config'), [
                'url' => $this->feederUrl,
                'username' => 'ws_user_test',
                'password' => 'ws_pass_test',
            ]);

        Http::fake([
            $this->feederUrl => Http::sequence()
                // Response for GetToken
                ->push([
                    'error_code' => 0,
                    'error_desc' => '',
                    'data' => ['token' => 'mock-token']
                ])
                // Response for GetListDosen (first page)
                ->push([
                    'error_code' => 0,
                    'error_desc' => '',
                    'data' => [
                        [
                            'nidn' => '00112233',
                            'nama_dosen' => 'Lecturer Mock',
                            'email' => 'mocklecturer@stikes.ac.id'
                        ]
                    ]
                ])
                // Response for GetListDosen (second page - empty to break loop)
                ->push([
                    'error_code' => 0,
                    'error_desc' => '',
                    'data' => []
                ])
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.feeder.sync'), [
                'type' => 'dosen'
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'username' => '00112233',
            'name' => 'Lecturer Mock',
            'role' => 'dosen',
        ]);
    }

    public function test_feeder_sync_mahasiswa_creates_class_automatically(): void
    {
        // First save config
        $this->actingAs($this->admin)
            ->postJson(route('admin.feeder.config'), [
                'url' => $this->feederUrl,
                'username' => 'ws_user_test',
                'password' => 'ws_pass_test',
            ]);

        Http::fake([
            $this->feederUrl => Http::sequence()
                // Response for GetToken
                ->push([
                    'error_code' => 0,
                    'error_desc' => '',
                    'data' => ['token' => 'mock-token']
                ])
                // Response for GetListRiwayatPendidikanMahasiswa (first page)
                ->push([
                    'error_code' => 0,
                    'error_desc' => '',
                    'data' => [
                        [
                            'nim' => '10203040',
                            'nama_mahasiswa' => 'Student Mock',
                            'nama_program_studi' => 'S1 Keperawatan',
                            'nama_periode_masuk' => '2025/2026 Ganjil',
                            'email' => 'studentmock@stikes.ac.id'
                        ]
                    ]
                ])
                // Response for GetListRiwayatPendidikanMahasiswa (second page - empty to break loop)
                ->push([
                    'error_code' => 0,
                    'error_desc' => '',
                    'data' => []
                ])
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.feeder.sync'), [
                'type' => 'mahasiswa'
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Check if class was automatically created
        $this->assertDatabaseHas('classes', [
            'name' => 'S1 Keperawatan - 2025/2026 Ganjil'
        ]);

        $class = ClassRoom::where('name', 'S1 Keperawatan - 2025/2026 Ganjil')->first();

        // Check if student was created and associated with the class
        $this->assertDatabaseHas('users', [
            'username' => '10203040',
            'name' => 'Student Mock',
            'role' => 'mahasiswa',
            'class_id' => $class->id
        ]);
    }
}
