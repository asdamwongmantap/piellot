<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\CompanyAdminRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewPagesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'ADMIN', 'status' => 'ACTIVE']);
    }

    private function pic(): User
    {
        $company = Company::create(['name' => 'PT Uji', 'primary_pic_name' => 'Budi', 'phone' => '0812345678', 'email' => 'b@x.id', 'status' => 'ACTIVE']);

        return User::factory()->create(['role' => 'PIC', 'status' => 'ACTIVE', 'company_id' => $company->id]);
    }

    public function test_schedule_available_to_both_roles(): void
    {
        $this->actingAs($this->admin())->get('/jadwal')->assertOk();
        $this->actingAs($this->pic())->get('/jadwal')->assertOk();
    }

    public function test_pic_access_is_admin_only_and_lists_invites(): void
    {
        $pic = $this->pic();
        CompanyAdminRequest::create([
            'company_id' => $pic->company_id, 'requested_name' => 'Sari', 'requested_email' => 'sari@x.id',
            'requested_by_user_id' => $pic->id, 'status' => 'APPROVED',
        ]);

        $this->actingAs($pic)->get('/akses-pic')->assertForbidden();

        $this->actingAs($this->admin())->get('/akses-pic')->assertOk()
            ->assertInertia(fn ($page) => $page->component('Access/Index')->has('accesses', 2));
    }

    public function test_pic_can_change_password(): void
    {
        $pic = $this->pic();

        $this->actingAs($pic)->get('/akun')->assertOk();
        $this->actingAs($pic)->put('/akun/password', [
            'current_password' => 'password', 'password' => 'KataSandiBaru123', 'password_confirmation' => 'KataSandiBaru123',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(\Hash::check('KataSandiBaru123', $pic->fresh()->password));
        $this->actingAs($this->admin())->get('/akun')->assertForbidden();
    }
}
