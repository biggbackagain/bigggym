<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BiometricClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeploymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_disabled_biometrics_never_contacts_python(): void
    {
        config(['services.biometrics.enabled' => false]);
        Http::fake();
        $this->actingAs(User::factory()->create());
        $this->postJson(route('check-in.biometric'), ['image' => 'test'])->assertStatus(503)->assertJsonPath('status', 'disabled');
        $this->postJson(route('biometrics.extract'), ['image' => 'test'])->assertStatus(503)->assertJsonPath('success', false);
        Http::assertNothingSent();
    }

    public function test_manual_access_page_renders_without_biometrics(): void
    {
        config(['services.biometrics.enabled' => false]);
        $this->actingAs(User::factory()->create())->get(route('check-in.index'))
            ->assertOk()->assertSee('Acceso con código habilitado');
        $this->get(route('members.create'))->assertOk()->assertDontSee('id="btnStart"', false);
    }

    public function test_client_sends_token_to_configured_service(): void
    {
        config(['services.biometrics.enabled' => true, 'services.biometrics.url' => 'https://ai.example.com', 'services.biometrics.token' => str_repeat('t', 64)]);
        Http::fake(['https://ai.example.com/health' => Http::response(['status' => 'ok'])]);
        app(BiometricClient::class)->connection()->get('/health');
        Http::assertSent(fn ($request) => $request->url() === 'https://ai.example.com/health' && $request->hasHeader('Authorization', 'Bearer '.str_repeat('t', 64)));
    }

    public function test_remote_plaintext_service_is_rejected(): void
    {
        config(['services.biometrics.enabled' => true, 'services.biometrics.url' => 'http://ai.example.com', 'services.biometrics.token' => str_repeat('t', 64)]);
        $this->expectException(\LogicException::class);
        app(BiometricClient::class)->connection();
    }

    public function test_admin_creation_requires_valid_password_and_creates_role(): void
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Nombre del administrador', 'Recepción')
            ->expectsQuestion('Correo electrónico', 'admin@example.com')
            ->expectsQuestion('Contraseña (mínimo 12 caracteres)', 'long-test-password')
            ->expectsQuestion('Confirma la contraseña', 'long-test-password')
            ->assertSuccessful();
        $this->assertTrue(User::firstOrFail()->hasRole('superadmin'));
    }

    public function test_admin_bootstrap_does_not_change_existing_accounts(): void
    {
        $user = User::factory()->create();
        $this->artisan('app:create-admin')->assertFailed();
        $this->assertDatabaseCount('users', 1);
        $this->assertSame($user->password, $user->fresh()->password);
    }
}
