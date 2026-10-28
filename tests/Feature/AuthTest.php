<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'nama' => 'Gianjar Pemilik',
            'username' => 'pemilik',
            'password' => Hash::make('password123'),
            'role' => 'Pemilik',
            'no_hp' => '08123456789',
        ]);
    }

    /**
     * Skenario 1: Halaman login dapat diakses oleh guest
     */
    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Masuk ke SERVISIN');
    }

    /**
     * Skenario 2: Login berhasil dengan kredensial yang valid
     */
    public function test_users_can_authenticate_using_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'username' => 'pemilik',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/dashboard');
    }

    /**
     * Skenario 3: Login gagal dengan password salah
     */
    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'username' => 'pemilik',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('username');
    }

    /**
     * Skenario 4: Validasi form login (username dan password wajib diisi)
     */
    public function test_login_fields_are_required(): void
    {
        $response = $this->post('/login', [
            'username' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['username', 'password']);
    }

    /**
     * Skenario 5: Logout berhasil mengakhiri sesi pengguna
     */
    public function test_users_can_logout(): void
    {
        $this->actingAs($this->user);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
