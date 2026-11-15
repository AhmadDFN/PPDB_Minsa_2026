<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\AccountType;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the public homepage loads successfully and renders Minsatama identity.
     */
    public function test_public_homepage_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('MIN 1 Kota Madiun');
        $response->assertSee('PPDB');
        $response->assertSee('Program Unggulan');
        $response->assertSee('Program Reguler');
    }

    /**
     * Test that the admin layout foundation preview renders successfully.
     */
    public function test_admin_foundation_preview_renders_successfully(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        /** @var User $admin */
        $admin = User::factory()->create([
            'account_type' => AccountType::ADMIN,
            'account_status' => AccountStatus::ACTIVE,
        ]);
        $admin->assignRole('admin_ppdb');

        $response = $this->actingAs($admin, 'web')->get('/admin/preview');

        $response->assertStatus(200);
        $response->assertSee('Pratinjau Fondasi Layout Admin');
        $response->assertSee('Panel Admin');
        $response->assertSee('PPDB Minsatama');
    }

    /**
     * Test that application configuration has expected locale and timezone.
     */
    public function test_application_configuration_is_correct(): void
    {
        $this->assertEquals('PPDB Minsatama', config('app.name'));
        $this->assertEquals('id', config('app.locale'));
        $this->assertEquals('Asia/Jakarta', config('app.timezone'));
    }
}
