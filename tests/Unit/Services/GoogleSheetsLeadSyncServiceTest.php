<?php

namespace Tests\Unit\Services;

use App\Models\GlsInscription;
use App\Models\Group;
use App\Models\GroupApplication;
use App\Models\Site;
use App\Services\GoogleSheetsClient;
use App\Services\GoogleSheetsLeadSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GoogleSheetsLeadSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GoogleSheetsClient $mockClient;
    protected GoogleSheetsLeadSyncService $syncService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockClient = Mockery::mock(GoogleSheetsClient::class);
        $this->syncService = new GoogleSheetsLeadSyncService($this->mockClient);

        config([
            'google-sheets.sheet_map' => [
                'agadir' => 'AGADIR',
                'marrakech' => 'MARRAKECH',
                'casablanca' => 'CASABLANCA',
                'rabat' => 'RABAT',
                'kenitra' => 'GLS KENITRA',
                'sale' => 'GS',
                'online' => 'ONLINE',
            ],
            'google-sheets.confirmed_sheet' => 'CONFIRMED',
        ]);
    }

    public function test_resolves_correct_sheet_for_group_application(): void
    {
        $site = Site::factory()->create(['slug' => 'agadir', 'name' => 'Agadir']);
        $group = Group::factory()->create(['site_id' => $site->id]);
        $app = GroupApplication::factory()->create(['group_id' => $group->id]);

        $this->assertEquals('AGADIR', $this->syncService->resolveSheetName($app));
    }

    public function test_resolves_correct_sheet_for_gls_inscription(): void
    {
        $site = Site::factory()->create(['slug' => 'rabat', 'name' => 'Rabat']);
        $inscription = GlsInscription::factory()->create(['centre' => $site->id]);

        $this->assertEquals('RABAT', $this->syncService->resolveSheetName($inscription));
    }

    public function test_resolves_online_sheet_for_online_inscription(): void
    {
        $inscription = GlsInscription::factory()->create([
            'centre' => 0,
            'type_cours' => 'en_ligne',
        ]);

        $this->assertEquals('ONLINE', $this->syncService->resolveSheetName($inscription));
    }

    public function test_resolves_null_for_unknown_center(): void
    {
        $site = Site::factory()->create(['slug' => 'unknown-city', 'name' => 'Unknown']);
        $group = Group::factory()->create(['site_id' => $site->id]);
        $app = GroupApplication::factory()->create(['group_id' => $group->id]);

        $this->assertNull($this->syncService->resolveSheetName($app));
    }

    public function test_builds_simplified_row_data_for_group_application(): void
    {
        $site = Site::factory()->create(['slug' => 'rabat', 'name' => 'Rabat']);
        $group = Group::factory()->create(['site_id' => $site->id, 'level' => 'B1']);
        $app = GroupApplication::factory()->create([
            'group_id' => $group->id,
            'full_name' => 'John Doe',
            'whatsapp_number' => '+212600000000',
            'email' => 'john@example.com',
        ]);

        $rowData = $this->syncService->buildRowData($app);

        $this->assertCount(5, $rowData);
        $this->assertEquals('John Doe', $rowData[0]);         // full_name
        $this->assertEquals('B1', $rowData[1]);                // level
        $this->assertEquals('+212600000000', $rowData[2]);     // phone
        $this->assertEquals('john@example.com', $rowData[3]);  // email
        $this->assertEquals('Rabat', $rowData[4]);             // center
    }

    public function test_builds_simplified_row_data_for_gls_inscription(): void
    {
        $site = Site::factory()->create(['slug' => 'casablanca', 'name' => 'Casablanca']);
        $inscription = GlsInscription::factory()->create([
            'name' => 'Jane Smith',
            'niveau' => 'A2',
            'phone' => '+212700000000',
            'email' => 'jane@example.com',
            'centre' => $site->id,
        ]);

        $rowData = $this->syncService->buildRowData($inscription);

        $this->assertCount(5, $rowData);
        $this->assertEquals('Jane Smith', $rowData[0]);
        $this->assertEquals('A2', $rowData[1]);
        $this->assertEquals('+212700000000', $rowData[2]);
        $this->assertEquals('jane@example.com', $rowData[3]);
        $this->assertEquals('Casablanca', $rowData[4]);
    }

    public function test_append_lead_updates_if_already_synced(): void
    {
        $site = Site::factory()->create(['slug' => 'agadir', 'name' => 'Agadir']);
        $group = Group::factory()->create(['site_id' => $site->id]);
        $app = GroupApplication::factory()->create([
            'group_id' => $group->id,
            'google_sheet_synced_at' => now(),
            'google_sheet_name' => 'AGADIR',
            'google_sheet_row' => 5,
        ]);

        $this->mockClient->shouldReceive('updateRow')
            ->once()
            ->with('AGADIR', 5, Mockery::type('array'));

        $this->mockClient->shouldNotReceive('append');

        $this->syncService->appendLeadToCenterSheet($app);
    }

    public function test_append_confirmed_skips_if_already_synced(): void
    {
        $site = Site::factory()->create(['slug' => 'rabat', 'name' => 'Rabat']);
        $group = Group::factory()->create(['site_id' => $site->id]);
        $app = GroupApplication::factory()->create([
            'group_id' => $group->id,
            'google_sheet_confirmed_synced_at' => now(),
        ]);

        $this->mockClient->shouldNotReceive('ensureSheetExists');
        $this->mockClient->shouldNotReceive('append');

        $this->syncService->appendLeadToConfirmedSheet($app);
    }

    public function test_append_new_lead_calls_client_append(): void
    {
        $site = Site::factory()->create(['slug' => 'marrakech', 'name' => 'Marrakech']);
        $group = Group::factory()->create(['site_id' => $site->id, 'level' => 'A2']);
        $app = GroupApplication::factory()->create([
            'group_id' => $group->id,
            'full_name' => 'Test User',
        ]);

        $this->mockClient->shouldReceive('append')
            ->once()
            ->with('MARRAKECH', Mockery::type('array'))
            ->andReturn(10);

        $this->syncService->appendLeadToCenterSheet($app);

        $app->refresh();
        $this->assertEquals('MARRAKECH', $app->google_sheet_name);
        $this->assertEquals(10, $app->google_sheet_row);
        $this->assertNotNull($app->google_sheet_synced_at);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
