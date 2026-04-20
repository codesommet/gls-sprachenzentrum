<?php

namespace Tests\Feature\Encaissement;

use App\Models\Encaissement;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EncaissementCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Site $site;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $this->site = Site::factory()->create(['is_active' => true]);
        $this->user = User::factory()->create();
        $this->user->assignRole('Super Admin');
    }

    public function test_can_view_encaissement_index(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('backoffice.encaissements.index'));

        $response->assertStatus(200);
        $response->assertViewIs('backoffice.encaissements.index');
    }

    public function test_can_create_manual_encaissement(): void
    {
        $data = [
            'site_id' => $this->site->id,
            'student_name' => 'Test Student',
            'amount' => 1300.00,
            'payment_method' => 'especes',
            'fee_type' => 'mensualite',
            'collected_at' => '2025-12-01',
            'fee_month' => '2025-12-01',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('backoffice.encaissements.store'), $data);

        $response->assertRedirect(route('backoffice.encaissements.index'));

        $this->assertDatabaseHas('encaissements', [
            'student_name' => 'Test Student',
            'amount' => 1300.00,
            'source_system' => 'manual',
        ]);
    }

    public function test_can_update_encaissement(): void
    {
        $enc = Encaissement::create([
            'site_id' => $this->site->id,
            'student_name' => 'Old Name',
            'amount' => 300,
            'payment_method' => 'especes',
            'fee_type' => 'mensualite',
            'collected_at' => '2025-12-01',
            'source_system' => 'manual',
        ]);

        $response = $this->actingAs($this->user)
            ->put(route('backoffice.encaissements.update', $enc), [
                'site_id' => $this->site->id,
                'student_name' => 'New Name',
                'amount' => 500,
                'payment_method' => 'tpe',
                'fee_type' => 'inscription_a1',
                'collected_at' => '2025-12-01',
            ]);

        $response->assertRedirect(route('backoffice.encaissements.index'));

        $enc->refresh();
        $this->assertEquals('New Name', $enc->student_name);
        $this->assertEquals(500, $enc->amount);
    }

    public function test_can_delete_encaissement(): void
    {
        $enc = Encaissement::create([
            'site_id' => $this->site->id,
            'student_name' => 'To Delete',
            'amount' => 300,
            'payment_method' => 'especes',
            'fee_type' => 'mensualite',
            'collected_at' => '2025-12-01',
            'source_system' => 'manual',
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('backoffice.encaissements.destroy', $enc));

        $response->assertRedirect(route('backoffice.encaissements.index'));
        $this->assertDatabaseMissing('encaissements', ['id' => $enc->id]);
    }

    public function test_validation_rejects_missing_required_fields(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('backoffice.encaissements.store'), []);

        $response->assertSessionHasErrors(['site_id', 'student_name', 'amount', 'collected_at']);
    }

    public function test_filters_by_site(): void
    {
        $otherSite = Site::factory()->create(['is_active' => true]);

        Encaissement::create([
            'site_id' => $this->site->id,
            'student_name' => 'Student A',
            'amount' => 300,
            'payment_method' => 'especes',
            'fee_type' => 'mensualite',
            'collected_at' => '2025-12-01',
            'source_system' => 'manual',
        ]);
        Encaissement::create([
            'site_id' => $otherSite->id,
            'student_name' => 'Student B',
            'amount' => 500,
            'payment_method' => 'tpe',
            'fee_type' => 'mensualite',
            'collected_at' => '2025-12-01',
            'source_system' => 'manual',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('backoffice.encaissements.index', ['site_id' => $this->site->id]));

        $response->assertStatus(200);
        $response->assertSee('Student A');
    }

    public function test_dashboard_returns_kpis(): void
    {
        Encaissement::create([
            'site_id' => $this->site->id,
            'student_name' => 'Student',
            'amount' => 1300,
            'payment_method' => 'especes',
            'fee_type' => 'mensualite',
            'collected_at' => now()->format('Y-m-d'),
            'source_system' => 'manual',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('backoffice.encaissements.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('backoffice.encaissements.dashboard');
    }
}
