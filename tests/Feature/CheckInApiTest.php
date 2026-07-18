<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Equipment;
use App\Models\EquipmentLoan;
use App\Models\EquipmentLoanItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CheckInApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeApprovedLoan(User $member): EquipmentLoan
    {
        $category = Category::create(['name' => 'Kamera', 'description' => null]);
        $equipment = Equipment::create([
            'category_id'        => $category->id,
            'name'               => 'Sony A7 III',
            'code'               => 'CAM-TEST',
            'quantity_total'     => 2,
            'quantity_available' => 2,
            'is_active'          => true,
        ]);

        $loan = EquipmentLoan::create([
            'user_id'   => $member->id,
            'loan_code' => EquipmentLoan::generateCode(),
            'loan_date' => now()->toDateString(),
            'purpose'   => 'Testing',
            'status'    => 'approved',
            'due_date'  => now()->addDays(2)->toDateString(),
        ]);

        EquipmentLoanItem::create([
            'equipment_loan_id' => $loan->id,
            'equipment_id'      => $equipment->id,
            'quantity'          => 1,
        ]);

        return $loan;
    }

    public function test_web_login_issues_sanctum_token(): void
    {
        $user = User::create([
            'name'      => 'Admin',
            'email'     => 'admin@test.id',
            'password'  => Hash::make('password123'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email'    => 'admin@test.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertNotNull(session('api_token'));
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name'         => 'web-frontend',
        ]);
    }

    public function test_check_in_via_api_activates_loan(): void
    {
        $member = User::create([
            'name' => 'Member', 'email' => 'm@test.id',
            'password' => Hash::make('password123'), 'role' => 'member', 'is_active' => true,
        ]);
        $loan = $this->makeApprovedLoan($member);
        $token = $member->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/check-ins', [
                'loan_code' => $loan->loan_code,
                'action'    => 'check_in',
                'device_id' => 'TABLET-01',
            ]);

        $response->assertStatus(201)->assertJsonPath('success', true);
        $this->assertEquals('active', $loan->fresh()->status);
    }

    public function test_check_in_twice_fails_validation(): void
    {
        $member = User::create([
            'name' => 'Member', 'email' => 'm2@test.id',
            'password' => Hash::make('password123'), 'role' => 'member', 'is_active' => true,
        ]);
        $loan = $this->makeApprovedLoan($member);
        $token = $member->createToken('test')->plainTextToken;

        $headers = ['Authorization' => "Bearer {$token}"];

        // Check-in pertama berhasil.
        $this->withHeaders($headers)->postJson('/api/v1/check-ins', [
            'loan_code' => $loan->loan_code,
            'action'    => 'check_in',
        ])->assertStatus(201);

        // Check-in kedua ditolak (loan sudah tidak berstatus approved).
        $this->withHeaders($headers)->postJson('/api/v1/check-ins', [
            'loan_code' => $loan->loan_code,
            'action'    => 'check_in',
        ])->assertStatus(422);
    }

    public function test_check_out_requires_item_conditions(): void
    {
        $member = User::create([
            'name' => 'Member', 'email' => 'm3@test.id',
            'password' => Hash::make('password123'), 'role' => 'member', 'is_active' => true,
        ]);
        $loan = $this->makeApprovedLoan($member);
        $token = $member->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        // Aktifkan loan lebih dulu.
        $this->withHeaders($headers)->postJson('/api/v1/check-ins', [
            'loan_code' => $loan->loan_code,
            'action'    => 'check_in',
        ])->assertStatus(201);

        // Check-out tanpa kondisi item → gagal validasi.
        $this->withHeaders($headers)->postJson('/api/v1/check-ins', [
            'loan_code' => $loan->loan_code,
            'action'    => 'check_out',
        ])->assertStatus(422)->assertJsonValidationErrors('item_conditions');
    }
}
