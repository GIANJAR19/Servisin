<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KelolaPenggunaTest extends TestCase
{
    use RefreshDatabase;

    protected User $pemilik;
    protected User $staf;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pemilik = User::create([
            'nama' => 'Gianjar Pemilik',
            'username' => 'pemilik',
            'password' => Hash::make('password123'),
            'role' => 'Pemilik',
            'no_hp' => '08123456789',
        ]);

        $this->staf = User::create([
            'nama' => 'Staf Biasa',
            'username' => 'staf_biasa',
            'password' => Hash::make('password123'),
            'role' => 'Staf',
            'no_hp' => '08129876543',
        ]);
    }

    /**
     * Skenario 1: Pemilik dapat melihat daftar seluruh pengguna
     */
    public function test_pemilik_can_view_user_list(): void
    {
        $response = $this->actingAs($this->pemilik)->get('/pengguna');
        $response->assertStatus(200);
        $response->assertSee('Kelola Pengguna');
        $response->assertSee('staf_biasa');
    }

    /**
     * Skenario 2: Staf dilarang mengakses modul kelola pengguna (HTTP 403 Forbidden)
     */
    public function test_staf_cannot_access_user_management(): void
    {
        $response = $this->actingAs($this->staf)->get('/pengguna');
        $response->assertStatus(403);

        $createResponse = $this->actingAs($this->staf)->get('/pengguna/create');
        $createResponse->assertStatus(403);
    }

    /**
     * Skenario 3: Pemilik berhasil mendaftarkan akun pengguna baru
     */
    public function test_pemilik_can_create_new_user(): void
    {
        $response = $this->actingAs($this->pemilik)->post('/pengguna', [
            'nama' => 'Mekanik Baru',
            'username' => 'mekanik1',
            'password' => 'secret123',
            'role' => 'Staf',
            'no_hp' => '081377778888',
        ]);

        $response->assertRedirect('/pengguna');
        $this->assertDatabaseHas('users', [
            'username' => 'mekanik1',
            'nama' => 'Mekanik Baru',
            'role' => 'Staf',
        ]);

        $newUser = User::where('username', 'mekanik1')->first();
        $this->assertTrue(Hash::check('secret123', $newUser->password));
    }

    /**
     * Skenario 4: Validasi username unik pada kelola pengguna
     */
    public function test_duplicate_username_is_rejected(): void
    {
        $response = $this->actingAs($this->pemilik)->post('/pengguna', [
            'nama' => 'User Duplikat',
            'username' => 'staf_biasa', // Sudah ada
            'password' => 'secret123',
            'role' => 'Staf',
        ]);

        $response->assertSessionHasErrors('username');
    }

    /**
     * Skenario 5: Pemilik tidak dapat menghapus akunnya sendiri
     */
    public function test_user_cannot_delete_their_own_account(): void
    {
        $response = $this->actingAs($this->pemilik)->delete("/pengguna/{$this->pemilik->id_user}");
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id_user' => $this->pemilik->id_user]);
    }

    /**
     * Skenario 6: Pemilik dapat menghapus akun staf lain
     */
    public function test_pemilik_can_delete_other_user(): void
    {
        $response = $this->actingAs($this->pemilik)->delete("/pengguna/{$this->staf->id_user}");
        $response->assertRedirect('/pengguna');
        $this->assertDatabaseMissing('users', ['id_user' => $this->staf->id_user]);
    }
}
