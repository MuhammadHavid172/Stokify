<?php

namespace Tests\Feature;

use App\Models\Kios;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TahapTigaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Admin dapat membuat kasir; kasir tersimpan dengan kios_id milik admin
     *    dan role kasir walau input role atau kios_id dikirim manual.
     */
    public function test_admin_dapat_membuat_kasir_dan_role_serta_kios_id_tetap_aman(): void
    {
        $kios = Kios::factory()->create(['status' => 'aktif']);
        $admin = User::factory()->create(['kios_id' => $kios->id, 'role' => 'admin']);

        $otherKios = Kios::factory()->create(['status' => 'aktif']);

        $payload = [
            'name' => 'Siti Kasir Baru',
            'username' => 'siti_kasir',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin', // Coba manipulasi role
            'kios_id' => $otherKios->id, // Coba manipulasi kios_id
        ];

        $response = $this->actingAs($admin)->post(route('admin.kasir.store'), $payload);

        $response->assertRedirect(route('admin.kasir.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'Siti Kasir Baru',
            'username' => 'siti_kasir',
            'kios_id' => $kios->id, // Harus milik kios admin yang login
            'role' => 'kasir',      // Harus tetap kasir
            'is_aktif' => true,
        ]);

        $kasir = User::where('username', 'siti_kasir')->first();
        $this->assertNotNull($kasir);
        $this->assertEquals($kios->id, $kasir->kios_id);
        $this->assertEquals('kasir', $kasir->role);
        $this->assertNull($kasir->email);
    }

    /**
     * 2. Username kasir yang sudah dipakai (termasuk di kios lain) ditolak.
     */
    public function test_username_kasir_yang_sudah_dipakai_global_ditolak(): void
    {
        $kiosA = Kios::factory()->create(['status' => 'aktif']);
        $adminA = User::factory()->create(['kios_id' => $kiosA->id, 'role' => 'admin']);

        $kiosB = Kios::factory()->create(['status' => 'aktif']);
        User::factory()->kasir()->create([
            'kios_id' => $kiosB->id,
            'username' => 'kasir_populer',
        ]);

        // Admin Kios A mencoba membuat kasir dengan username yang sama persis
        $response = $this->actingAs($adminA)->post(route('admin.kasir.store'), [
            'name' => 'Kasir Kios A',
            'username' => 'kasir_populer',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertDatabaseMissing('users', [
            'kios_id' => $kiosA->id,
            'name' => 'Kasir Kios A',
        ]);
    }

    /**
     * 3. Admin kios A tidak bisa melihat, mengubah, mereset, atau menonaktifkan kasir kios B (404).
     */
    public function test_admin_kios_a_tidak_bisa_mengakses_kasir_kios_b_404(): void
    {
        $kiosA = Kios::factory()->create(['status' => 'aktif']);
        $adminA = User::factory()->create(['kios_id' => $kiosA->id, 'role' => 'admin']);

        $kiosB = Kios::factory()->create(['status' => 'aktif']);
        $kasirB = User::factory()->kasir()->create(['kios_id' => $kiosB->id]);

        $this->actingAs($adminA);

        // 1. Edit kasir kios B -> 404
        $this->get(route('admin.kasir.edit', $kasirB))->assertNotFound();

        // 2. Update kasir kios B -> 404
        $this->put(route('admin.kasir.update', $kasirB), [
            'name' => 'Nama Diretas',
            'username' => 'diretas',
        ])->assertNotFound();

        // 3. Toggle kasir kios B -> 404
        $this->post(route('admin.kasir.toggle', $kasirB))->assertNotFound();

        // 4. Reset password kasir kios B -> 404
        $this->post(route('admin.kasir.resetPassword', $kasirB), [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ])->assertNotFound();

        // Pastikan data kasir B tidak berubah
        $kasirB->refresh();
        $this->assertNotEquals('Nama Diretas', $kasirB->name);
    }

    /**
     * 4. Daftar kasir hanya menampilkan kasir kios sendiri.
     */
    public function test_daftar_kasir_hanya_menampilkan_kasir_kios_sendiri(): void
    {
        $kiosA = Kios::factory()->create(['status' => 'aktif']);
        $adminA = User::factory()->create(['kios_id' => $kiosA->id, 'role' => 'admin']);
        $kasirA = User::factory()->kasir()->create([
            'kios_id' => $kiosA->id,
            'name' => 'Kasir Kios A Asli',
            'username' => 'kasir_a_asli',
        ]);

        $kiosB = Kios::factory()->create(['status' => 'aktif']);
        $kasirB = User::factory()->kasir()->create([
            'kios_id' => $kiosB->id,
            'name' => 'Kasir Rahasia Kios B',
            'username' => 'kasir_b_rahasia',
        ]);

        $response = $this->actingAs($adminA)->get(route('admin.kasir.index'));

        $response->assertOk();
        $response->assertSee('Kasir Kios A Asli');
        $response->assertSee('kasir_a_asli');
        $response->assertDontSee('Kasir Rahasia Kios B');
        $response->assertDontSee('kasir_b_rahasia');
    }

    /**
     * 5. Admin dapat menonaktifkan kasir, lalu kasir tersebut tidak bisa login
     *    dan sesi aktifnya langsung terlempar.
     */
    public function test_admin_dapat_menonaktifkan_kasir_dan_sesi_aktif_kasir_terlempar(): void
    {
        $kios = Kios::factory()->create(['status' => 'aktif']);
        $admin = User::factory()->create(['kios_id' => $kios->id, 'role' => 'admin']);
        $kasir = User::factory()->kasir()->create([
            'kios_id' => $kios->id,
            'username' => 'kasir_aktif',
            'password' => Hash::make('password123'),
            'is_aktif' => true,
        ]);

        // Admin menonaktifkan kasir
        $response = $this->actingAs($admin)->post(route('admin.kasir.toggle', $kasir));
        $response->assertRedirect();
        $kasir->refresh();
        $this->assertFalse($kasir->is_aktif);

        // Kasir mencoba login saat sudah dinonaktifkan
        $this->post(route('logout'));
        $loginResponse = $this->post(route('login'), [
            'login' => 'kasir_aktif',
            'password' => 'password123',
        ]);
        $loginResponse->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    /**
     * 6. Admin dapat mengaktifkan kembali kasir.
     */
    public function test_admin_dapat_mengaktifkan_kembali_kasir(): void
    {
        $kios = Kios::factory()->create(['status' => 'aktif']);
        $admin = User::factory()->create(['kios_id' => $kios->id, 'role' => 'admin']);
        $kasir = User::factory()->kasir()->create([
            'kios_id' => $kios->id,
            'username' => 'kasir_mati',
            'password' => Hash::make('password123'),
            'is_aktif' => false,
        ]);

        // Admin mengaktifkan kasir
        $this->actingAs($admin)->post(route('admin.kasir.toggle', $kasir));
        $kasir->refresh();
        $this->assertTrue($kasir->is_aktif);

        // Kasir sekarang bisa login
        $this->post(route('logout'));
        $loginResponse = $this->post(route('login'), [
            'login' => 'kasir_mati',
            'password' => 'password123',
        ]);
        $loginResponse->assertRedirect(route('kasir.index'));
        $this->assertAuthenticatedAs($kasir);
    }

    /**
     * 7. Reset password berhasil: kasir login dengan password baru, password lama gagal.
     */
    public function test_reset_password_kasir_berhasil_dan_password_lama_gagal(): void
    {
        $kios = Kios::factory()->create(['status' => 'aktif']);
        $admin = User::factory()->create(['kios_id' => $kios->id, 'role' => 'admin']);
        $kasir = User::factory()->kasir()->create([
            'kios_id' => $kios->id,
            'username' => 'kasir_reset',
            'password' => Hash::make('password_lama'),
            'is_aktif' => true,
        ]);

        // Admin melakukan reset password
        $response = $this->actingAs($admin)->post(route('admin.kasir.resetPassword', $kasir), [
            'password' => 'password_baru123',
            'password_confirmation' => 'password_baru123',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Logout admin
        $this->post(route('logout'));

        // Coba login dengan password lama -> Gagal
        $failResponse = $this->post(route('login'), [
            'login' => 'kasir_reset',
            'password' => 'password_lama',
        ]);
        $failResponse->assertSessionHasErrors();
        $this->assertGuest();

        // Coba login dengan password baru -> Berhasil
        $successResponse = $this->post(route('login'), [
            'login' => 'kasir_reset',
            'password' => 'password_baru123',
        ]);
        $successResponse->assertRedirect(route('kasir.index'));
        $this->assertAuthenticatedAs($kasir);
    }

    /**
     * 8. Kasir yang mencoba membuka /admin/kasir mendapat 403 Forbidden.
     */
    public function test_kasir_mengakses_admin_kasir_mendapat_403_forbidden(): void
    {
        $kios = Kios::factory()->create(['status' => 'aktif']);
        $kasir = User::factory()->kasir()->create(['kios_id' => $kios->id, 'is_aktif' => true]);

        $response = $this->actingAs($kasir)->get(route('admin.kasir.index'));
        $response->assertStatus(403);
    }

    /**
     * 9. Tidak ada route hapus kasir (destroy).
     */
    public function test_tidak_ada_route_hapus_kasir(): void
    {
        // Pastikan tidak ada route bernomor atau bernama admin.kasir.destroy
        $this->assertFalse(Route::has('admin.kasir.destroy'));

        // Pastikan tidak ada route DELETE pada prefix admin/kasir
        $routes = Route::getRoutes();
        $hasDeleteRoute = false;

        foreach ($routes as $route) {
            if (str_starts_with($route->uri(), 'admin/kasir') && in_array('DELETE', $route->methods())) {
                $hasDeleteRoute = true;
                break;
            }
        }

        $this->assertFalse($hasDeleteRoute, 'Route DELETE pada admin/kasir tidak boleh ada!');
    }

    /**
     * 10. Kartu "Kasir Aktif" di dashboard menghitung dengan benar dan hanya untuk kios sendiri.
     */
    public function test_kartu_kasir_aktif_di_dashboard_menghitung_benar_dan_hanya_kios_sendiri(): void
    {
        $kiosA = Kios::factory()->create(['status' => 'aktif', 'nama_kios' => 'Kios Unggul A']);
        $adminA = User::factory()->create(['kios_id' => $kiosA->id, 'role' => 'admin']);

        // Buat 3 kasir di Kios A: 2 aktif, 1 nonaktif
        User::factory()->kasir()->create(['kios_id' => $kiosA->id, 'is_aktif' => true]);
        User::factory()->kasir()->create(['kios_id' => $kiosA->id, 'is_aktif' => true]);
        User::factory()->kasir()->create(['kios_id' => $kiosA->id, 'is_aktif' => false]);

        // Buat 5 kasir aktif di Kios B
        $kiosB = Kios::factory()->create(['status' => 'aktif', 'nama_kios' => 'Kios Lain B']);
        User::factory()->count(5)->kasir()->create(['kios_id' => $kiosB->id, 'is_aktif' => true]);

        // Admin Kios A membuka dashboard
        $response = $this->actingAs($adminA)->get(route('admin.dashboard'));

        $response->assertOk();

        // View harus menerima variabel kasirAktifCount bernilai 2
        $response->assertViewHas('kasirAktifCount', 2);
        $response->assertViewHas('totalKasirCount', 3);

        // Teks jumlah kasir aktif 2 tampil di dashboard
        $response->assertSee('Jumlah Kasir Aktif');
        $this->assertEquals(2, $response->viewData('kasirAktifCount'));
        $this->assertEquals(3, $response->viewData('totalKasirCount'));
    }
}
