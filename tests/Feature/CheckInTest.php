<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.biometrics.enabled' => true, 'services.biometrics.token' => str_repeat('t', 64)]);
        $this->actingAs(User::factory()->create());
    }

    private function member(): Member
    {
        $member = Member::create(['name' => 'Socio de prueba', 'member_code' => 'GYM-TEST', 'status' => 'expired']);
        $member->face_vector = array_fill(0, 128, 0.1);
        $member->save();
        return $member;
    }

    private function subscription(Member $member, string $start, string $end): void
    {
        $type = MembershipType::create(['name' => 'Mensual', 'duration_days' => 30, 'price_general' => 100, 'price_student' => 80]);
        $payment = Payment::create(['member_id' => $member->id, 'amount' => 100, 'payment_date' => today()]);
        Subscription::create(['member_id' => $member->id, 'membership_type_id' => $type->id, 'payment_id' => $payment->id, 'start_date' => $start, 'end_date' => $end]);
    }

    public function test_manual_access_returns_visible_result_and_includes_end_date(): void
    {
        $member = $this->member();
        $this->subscription($member, today()->subDay()->toDateString(), today()->toDateString());
        $this->post(route('check-in.store'), ['member_code' => 'GYM-TEST'])
            ->assertRedirect(route('check-in.index'))->assertSessionHas('access_result.status', 'success');
    }

    public function test_future_subscription_does_not_grant_access(): void
    {
        $member = $this->member();
        $this->subscription($member, today()->addDay()->toDateString(), today()->addMonth()->toDateString());
        $this->post(route('check-in.store'), ['member_code' => 'GYM-TEST'])
            ->assertSessionHas('access_result.status', 'error');
    }

    public function test_current_subscription_is_used_even_if_a_future_one_exists(): void
    {
        $member = $this->member();
        $this->subscription($member, today()->subDay()->toDateString(), today()->toDateString());
        $this->subscription($member, today()->addDay()->toDateString(), today()->addMonth()->toDateString());
        $this->post(route('check-in.store'), ['member_code' => 'GYM-TEST'])
            ->assertSessionHas('access_result.status', 'success');
    }

    public function test_ai_failure_is_not_an_access_denial(): void
    {
        $this->member();
        Http::fake(['*' => Http::response('Unavailable', 503)]);
        $this->postJson(route('check-in.biometric'), ['image' => 'test'])
            ->assertStatus(503)->assertJsonPath('status', 'unavailable');
    }

    public function test_unknown_face_is_waiting(): void
    {
        $this->member();
        Http::fake(['*' => Http::response(['success' => true, 'match' => false, 'message' => 'Sin coincidencia'])]);
        $this->postJson(route('check-in.biometric'), ['image' => 'test'])
            ->assertOk()->assertJsonPath('status', 'waiting');
    }

    public function test_enrollment_rejects_malformed_vectors(): void
    {
        $this->post(route('members.store'), ['name' => 'Test', 'face_vector' => '[1,2,3]'])
            ->assertSessionHasErrors('face_vector');
        $this->assertDatabaseCount('members', 0);
    }

    public function test_enrollment_saves_a_valid_vector_without_optional_contact_fields(): void
    {
        $this->post(route('members.store'), ['name' => 'Test', 'face_vector' => json_encode(array_fill(0, 128, 0.1))])
            ->assertSessionHasNoErrors()->assertRedirect(route('members.index'));
        $member = Member::firstOrFail();
        $this->assertCount(128, $member->face_vector);
        $this->assertArrayNotHasKey('face_vector', $member->toArray());
    }
}
