<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PengaturanAkunTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'nama' => 'User Akun',
            'username' => 'user_akun',
            'password' => Hash::make('passwordLama123'),
            'role' => 'Staf',
            'no_hp' => '081233334444',
        ]);
    }

    /**
     * Skenario 1: Pengguna melihat halaman pengaturan akun miliknya
     */
    public function test_can_view_profile_settings_page(): void
    {
        $response = $this->actingAs($this->user)->get('/pengaturan-akun');
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Akun');
        $response->assertSee('user_akun');
    }

    /**
     * Skenario 2: Pengguna berhasil mengubah nama dan nomor HP profil
     */
    public function test_can_update_profile_information(): void
    {
        $response = $this->actingAs($this->user)->put('/pengaturan-akun/profil', [
            'nama' => 'Nama Baru Diperbarui',
            'no_hp' => '089988887777',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id_user' => $this->user->id_user,
            'nama' => 'Nama Baru Diperbarui',
            'no_hp' => '089988887777',
        ]);
    }

    /**
     * Skenario 3: Username pengguna terkunci dan tidak dapat diubah
     */
    public function test_username_cannot_be_changed(): void
    {
        $this->actingAs($this->user)->put('/pengaturan-akun/profil', [
            'nama' => 'Nama Baru',
            'username' => 'hacked_username',
        ]);

        $this->assertEquals('user_akun', $this->user->fresh()->username);
    }

    /**
     * Skenario 4: Ganti password berhasil jika password saat ini cocok dan konfirmasi valid
     */
    public function test_can_change_password_with_correct_current_password(): void
    {
        $response = $this->actingAs($this->user)->put('/pengaturan-akun/password', [
            'password_lama' => 'passwordLama123',
            'password_baru' => 'passwordBaru456',
            'password_baru_confirmation' => 'passwordBaru456',
        ]);

        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('passwordBaru456', $this->user->fresh()->password));
    }

    /**
     * Skenario 5: Ganti password ditolak jika password saat ini salah
     */
    public function test_change_password_fails_if_current_password_wrong(): void
    {
        $response = $this->actingAs($this->user)->put('/pengaturan-akun/password', [
            'password_lama' => 'passwordSalahTotal',
            'password_baru' => 'passwordBaru456',
            'password_baru_confirmation' => 'passwordBaru456',
        ]);

        $response->assertSessionHasErrors('password_lama');
        $this->assertTrue(Hash::check('passwordLama123', $this->user->fresh()->password));
    }
}
