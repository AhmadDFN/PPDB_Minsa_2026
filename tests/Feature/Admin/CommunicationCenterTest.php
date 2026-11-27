<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\CommunicationChannel;
use App\Models\CommunicationChannelCredential;
use App\Models\CommunicationSetting;
use App\Models\User;
use App\Services\Authentication\Delivery\FonnteOtpDelivery;
use App\Services\Communication\CommunicationChannelService;
use App\Services\Communication\CommunicationCredentialService;
use App\Services\Communication\CommunicationMessageTemplateService;
use App\Services\Communication\Providers\FonnteProvider;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class CommunicationCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_only_superadmin_can_open_provider_gateway(): void
    {
        $this->actingAs($this->admin('superadmin'))->get(route('admin.communication.index'))->assertOk();
        $this->actingAs($this->admin('admin_ppdb'))->get(route('admin.communication.index'))->assertForbidden();
        $this->actingAs(User::factory()->applicant()->create())->get(route('admin.communication.index'))->assertForbidden();
        Auth::logout();
        $this->get(route('admin.communication.index'))->assertRedirect(route('admin.login'));
    }

    public function test_sensitive_channel_actions_are_server_side_superadmin_only(): void
    {
        $admin = $this->admin('admin_ppdb');

        $this->actingAs($admin)->post(route('admin.communication.channels.store'), [
            'provider_code' => 'fonnte', 'channel_name' => 'Blocked', 'device_token' => 'not-allowed-token',
        ])->assertForbidden();
    }

    public function test_template_editor_displays_stored_values_placeholders_and_fonnte_provider(): void
    {
        CommunicationSetting::singleton()->update(['otp_password_reset_template' => 'Kode pemulihan {{otp}}']);

        $this->actingAs($this->admin('superadmin'))->get(route('admin.communication.index'))
            ->assertOk()
            ->assertSee('Kode pemulihan {{otp}}')
            ->assertSee('&#123;&#123;otp_validity_minutes&#125;&#125;', false)
            ->assertSee('name="provider_code" value="fonnte"', false)
            ->assertDontSee('device-token-test-123456');
    }

    public function test_superadmin_can_update_message_templates_and_unknown_placeholders_are_rejected(): void
    {
        $admin = $this->admin('superadmin');
        $templates = CommunicationMessageTemplateService::defaults();
        $templates[CommunicationMessageTemplateService::OTP_ACTIVATION] = 'Halo {{applicant_name}}, kode {{otp}} dari {{institution_name}}.';

        $this->actingAs($admin)->put(route('admin.communication.message-templates.update'), $templates)
            ->assertRedirect()->assertSessionHas('success');

        $this->assertSame($templates[CommunicationMessageTemplateService::OTP_ACTIVATION], CommunicationSetting::singleton()->otp_activation_template);

        $templates[CommunicationMessageTemplateService::OTP_ACTIVATION] = 'Kode {{unknown_value}}';
        $this->actingAs($admin)->put(route('admin.communication.message-templates.update'), $templates)
            ->assertRedirect()->assertSessionHas('error');
    }

    public function test_admin_ppdb_cannot_update_message_templates(): void
    {
        $this->actingAs($this->admin('admin_ppdb'))
            ->put(route('admin.communication.message-templates.update'), CommunicationMessageTemplateService::defaults())
            ->assertForbidden();
    }

    public function test_store_validates_device_and_encrypts_credential(): void
    {
        $token = 'device-token-test-123456';
        Http::fake(['https://api.fonnte.com/device' => Http::response([
            'status' => true, 'device_status' => 'connect', 'device_number' => '628123456789', 'device_name' => 'QA Device',
        ])]);

        $response = $this->actingAs($this->admin('superadmin'))->post(route('admin.communication.channels.store'), [
            'provider_code' => 'fonnte', 'channel_name' => 'QA Fonnte', 'device_token' => $token,
        ]);

        $response->assertRedirect()->assertSessionHas('success');
        $this->assertDatabaseHas('communication_channels', ['channel_name' => 'QA Fonnte', 'connection_status' => 'connected']);
        $credential = CommunicationChannelCredential::query()->firstOrFail();
        $this->assertNotSame($token, $credential->credential_ciphertext);
        $this->assertSame($token, Crypt::decryptString($credential->credential_ciphertext));
        $response->assertDontSee($token);
    }

    public function test_failed_credential_update_preserves_previous_token(): void
    {
        $channel = $this->channel('old-token');
        $oldCiphertext = $channel->credential->credential_ciphertext;
        Http::fake(['https://api.fonnte.com/device' => Http::response(['status' => false], 401)]);

        $this->actingAs($this->admin('superadmin'))->patch(route('admin.communication.channels.update', $channel), [
            'channel_name' => 'Updated name', 'device_token' => 'new-token-that-fails',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertSame($oldCiphertext, $channel->fresh()->credential->credential_ciphertext);
    }

    public function test_activation_keeps_only_one_channel_active(): void
    {
        $first = $this->channel('first-token', true);
        $second = $this->channel('second-token');
        CommunicationSetting::query()->create(['id' => 1, 'active_channel_id' => $first->id, 'database_provider_mode_enabled' => true]);

        $this->actingAs($this->admin('superadmin'))->post(route('admin.communication.channels.activate', $second))
            ->assertRedirect()->assertSessionHas('success');

        $this->assertSame($second->id, CommunicationSetting::query()->firstOrFail()->active_channel_id);
        $this->assertNull(CommunicationSetting::query()->firstOrFail()->active_channel_id === $first->id ? $first->id : null);
    }

    public function test_database_channel_sends_without_falling_back_to_environment(): void
    {
        $channel = $this->channel('database-token', true);
        CommunicationSetting::query()->create(['id' => 1, 'active_channel_id' => $channel->id, 'database_provider_mode_enabled' => true]);
        Http::fake(['https://api.fonnte.com/send' => Http::response(['status' => true, 'id' => 'msg-1'])]);

        $result = app(CommunicationChannelService::class)->sendMessage('628123456789', 'TEST');

        $this->assertTrue($result->accepted);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.fonnte.com/send' && $request->header('Authorization')[0] === 'database-token');
    }

    public function test_database_channel_sends_recovery_otp_without_legacy_token(): void
    {
        $channel = $this->channel('database-token', true);
        CommunicationSetting::query()->create(['id' => 1, 'active_channel_id' => $channel->id, 'database_provider_mode_enabled' => true]);
        config(['services.fonnte.token' => null]);
        Http::fake(['https://api.fonnte.com/send' => Http::response(['status' => true])]);

        $this->assertTrue(app(FonnteOtpDelivery::class)->send('08123456789', '123456', 'password_reset', User::factory()->applicant()->create()));
        Http::assertSent(fn ($request) => $request->header('Authorization')[0] === 'database-token' && str_contains($request['message'], '123456'));
    }

    public function test_settings_singleton_is_idempotent(): void
    {
        CommunicationSetting::singleton();
        CommunicationSetting::singleton();

        $this->assertSame(1, CommunicationSetting::query()->count());
    }

    public function test_deactivation_clears_pointer_without_selecting_another_channel(): void
    {
        $first = $this->channel('first-token');
        $second = $this->channel('second-token');
        CommunicationSetting::query()->create(['id' => 1, 'active_channel_id' => $first->id, 'database_provider_mode_enabled' => true]);

        $this->actingAs($this->admin('superadmin'))->post(route('admin.communication.channels.deactivate', $first))
            ->assertRedirect()->assertSessionHas('success');

        $this->assertNull(CommunicationSetting::query()->firstOrFail()->active_channel_id);
        $this->assertNotSame($second->id, CommunicationSetting::query()->firstOrFail()->active_channel_id);
    }

    public function test_disconnected_channel_cannot_be_activated(): void
    {
        $channel = $this->channel('disconnected-token');
        $channel->update(['connection_status' => 'disconnected']);

        $this->actingAs($this->admin('superadmin'))->post(route('admin.communication.channels.activate', $channel))
            ->assertRedirect()->assertSessionHas('error');

        $this->assertDatabaseMissing('communication_settings', ['active_channel_id' => $channel->id]);
    }

    public function test_stale_credential_version_is_rejected(): void
    {
        $channel = $this->channel('old-token');
        $service = app(CommunicationCredentialService::class);

        $service->updateValidated($channel, 'new-token', 1, ['safe_metadata' => ['device_status' => 'connect']], $this->admin('superadmin'));

        $this->expectException(RuntimeException::class);
        $service->updateValidated($channel, 'stale-token', 1, ['safe_metadata' => ['device_status' => 'connect']], $this->admin('superadmin'));
    }

    public function test_fonnte_rejects_invalid_payload_before_http(): void
    {
        Http::fake();
        $provider = app(FonnteProvider::class);

        $this->assertSame('invalid_target', $provider->sendMessage('token', '', 'message')->errorCode);
        $this->assertSame('invalid_message', $provider->sendMessage('token', '628123456789', '   ')->errorCode);
        Http::assertNothingSent();
    }

    public function test_fonnte_accepts_valid_payload_with_http_fake(): void
    {
        Http::fake(['https://api.fonnte.com/send' => Http::response(['status' => true, 'id' => 'msg-1'])]);

        $result = app(FonnteProvider::class)->sendMessage('token', '628123456789', 'message');

        $this->assertTrue($result->accepted);
        Http::assertSentCount(1);
    }

    private function admin(string $role): User
    {
        $user = User::factory()->admin()->create(['email' => $role.'@example.test']);
        $user->assignRole($role);

        return $user;
    }

    private function channel(string $token, bool $active = false): CommunicationChannel
    {
        $channel = CommunicationChannel::query()->create([
            'provider_code' => 'fonnte', 'channel_name' => 'Channel '.uniqid(),
            'sender_identifier' => '628123456789', 'connection_status' => 'connected',
        ]);
        app(CommunicationCredentialService::class)->store($channel, $token);

        return $channel->fresh('credential');
    }
}
