<?php

namespace Tests\Feature;

use App\Models\Concerns\BelongsToKios;
use App\Models\Kios;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Model dummy khusus untuk menguji trait BelongsToKios dan KiosScope.
 */
class DummyProduk extends Model
{
    use BelongsToKios;

    protected $table = 'dummy_produks';
    protected $fillable = ['kios_id', 'nama_barang'];
}

class TahapDuaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1. Registrasi kios baru berhasil membuat 1 kios dan 1 user dengan role admin
     *    dan kios_id yang sama, lalu redirect ke dashboard admin.
     */
    public function test_registrasi_kios_baru_berhasil_membuat_kios_dan_admin_lalu_redirect_ke_dashboard_admin(): void
    {
        $payload = [
            'nama_kios' => 'Kios Berkah Rezeki',
            'alamat' => 'Jl. Sentani No. 12, Jayapura',
            'no_telepon' => '081234567899',
            'name' => 'Budi Santoso',
            'username' => 'budi_berkah',
            'email' => 'budi@kiosberkah.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register'), $payload);

        // Harus redirect ke admin.dashboard
        $response->assertRedirect(route('admin.dashboard'));

        // Cek database kios
        $this->assertDatabaseHas('kios', [
            'nama_kios' => 'Kios Berkah Rezeki',
            'alamat' => 'Jl. Sentani No. 12, Jayapura',
            'no_telepon' => '081234567899',
            'status' => 'aktif',
        ]);

        $kios = Kios::where('nama_kios', 'Kios Berkah Rezeki')->first();
        $this->assertNotNull($kios);

        // Cek database user
        $this->assertDatabaseHas('users', [
            'kios_id' => $kios->id,
            'name' => 'Budi Santoso',
            'username' => 'budi_berkah',
            'email' => 'budi@kiosberkah.test',
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        // Cek bahwa user otomatis login
        $this->assertAuthenticated();
        $this->assertEquals($kios->id, auth()->user()->kios_id);
        $this->assertTrue(auth()->user()->isAdmin());
    }

    /**
     * 2. Registrasi dengan username yang sudah dipakai gagal validasi.
     */
    public function test_registrasi_dengan_username_duplikat_gagal_validasi(): void
    {
        // Buat user pertama
        $kios = Kios::factory()->create();
        User::factory()->create([
            'kios_id' => $kios->id,
            'username' => 'pemilik_toko',
        ]);

        // Coba daftarkan kios baru dengan username yang sama persis
        $response = $this->post(route('register'), [
            'nama_kios' => 'Toko Dua',
            'name' => 'Pemilik Lain',
            'username' => 'pemilik_toko', // Duplikat
            'email' => 'pemiliklain@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
        $this->assertDatabaseMissing('kios', ['nama_kios' => 'Toko Dua']);
    }

    /**
     * 2b. Registrasi dengan tepat 5 kolom utama (tanpa username/alamat/no_telepon) berhasil.
     */
    public function test_registrasi_kios_baru_hanya_dengan_lima_kolom_utama_berhasil(): void
    {
        $payload = [
            'nama_kios' => 'Kios Berkah Lima',
            'name' => 'Siti Aminah',
            'email' => 'siti@berkahlima.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register'), $payload);

        $response->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('kios', [
            'nama_kios' => 'Kios Berkah Lima',
            'status' => 'aktif',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Siti Aminah',
            'email' => 'siti@berkahlima.test',
            'username' => null,
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        $this->assertAuthenticated();
    }

    /**
     * 2c. Registrasi dengan email yang sudah dipakai gagal validasi.
     */
    public function test_registrasi_dengan_email_duplikat_gagal_validasi(): void
    {
        $kios = Kios::factory()->create();
        User::factory()->create([
            'kios_id' => $kios->id,
            'email' => 'pemilik@terdaftar.test',
        ]);

        $response = $this->post(route('register'), [
            'nama_kios' => 'Kios Dua',
            'name' => 'Pemilik Kedua',
            'email' => 'pemilik@terdaftar.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseMissing('kios', ['nama_kios' => 'Kios Dua']);
    }

    /**
     * 3. Form registrasi yang dikirim dengan input 'role' manual tetap menghasilkan
     *    user ber-role admin (role dari form diabaikan).
     */
    public function test_form_registrasi_dengan_input_role_manual_tetap_menghasilkan_role_admin(): void
    {
        $response = $this->post(route('register'), [
            'nama_kios' => 'Kios Hijau Amanah',
            'name' => 'Ahmad Kasir',
            'username' => 'ahmad_amanah',
            'email' => 'ahmad@amanah.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'kasir', // Mencoba memanipulasi role via form
        ]);

        $response->assertRedirect(route('admin.dashboard'));

        $user = User::where('username', 'ahmad_amanah')->first();
        $this->assertNotNull($user);
        $this->assertEquals('admin', $user->role, 'Role harus tetap di-hardcode sebagai admin!');
        $this->assertTrue($user->isAdmin());
    }

    /**
     * 4. Login admin berhasil redirect ke /admin/dashboard, login kasir berhasil redirect ke /kasir.
     */
    public function test_login_admin_redirect_ke_admin_dashboard_dan_kasir_redirect_ke_kasir(): void
    {
        $kios = Kios::factory()->create(['status' => 'aktif']);

        $admin = User::factory()->create([
            'kios_id' => $kios->id,
            'username' => 'admin_uji',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        $kasir = User::factory()->kasir()->create([
            'kios_id' => $kios->id,
            'username' => 'kasir_uji',
            'password' => Hash::make('secret123'),
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        // Login Admin
        $responseAdmin = $this->post(route('login'), [
            'username' => 'admin_uji',
            'password' => 'secret123',
        ]);
        $responseAdmin->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        // Logout
        $this->post(route('logout'));
        $this->assertGuest();

        // Login Kasir
        $responseKasir = $this->post(route('login'), [
            'username' => 'kasir_uji',
            'password' => 'secret123',
        ]);
        $responseKasir->assertRedirect(route('kasir.index'));
        $this->assertAuthenticatedAs($kasir);

        // Logout
        $this->post(route('logout'));
        $this->assertGuest();

        // Login Admin menggunakan Email
        $responseEmail = $this->post(route('login'), [
            'login' => $admin->email,
            'password' => 'secret123',
        ]);
        $responseEmail->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    /**
     * 5. Kasir yang mencoba membuka /admin/dashboard mendapat 403 Forbidden.
     *    Sebaliknya, admin boleh membuka /admin/dashboard dan /kasir.
     */
    public function test_kasir_mengakses_admin_dashboard_mendapat_403_forbidden_sedangkan_admin_bisa_keduanya(): void
    {
        $kios = Kios::factory()->create(['status' => 'aktif']);

        $admin = User::factory()->create([
            'kios_id' => $kios->id,
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        $kasir = User::factory()->kasir()->create([
            'kios_id' => $kios->id,
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        // 1. Kasir membuka /admin/dashboard -> 403 Forbidden
        $responseKasirToAdmin = $this->actingAs($kasir)->get(route('admin.dashboard'));
        $responseKasirToAdmin->assertStatus(403);

        // 2. Kasir membuka /kasir -> 200 OK
        $responseKasirToKasir = $this->actingAs($kasir)->get(route('kasir.index'));
        $responseKasirToKasir->assertOk();

        // 3. Admin membuka /admin/dashboard -> 200 OK
        $responseAdminToAdmin = $this->actingAs($admin)->get(route('admin.dashboard'));
        $responseAdminToAdmin->assertOk();

        // 4. Admin membuka /kasir -> 200 OK (Admin boleh menjaga kasir)
        $responseAdminToKasir = $this->actingAs($admin)->get(route('kasir.index'));
        $responseAdminToKasir->assertOk();
    }

    /**
     * 6. User dengan is_aktif = false tidak bisa login, dan jika sedang login lalu dinonaktifkan,
     *    request berikutnya logout otomatis.
     */
    public function test_user_nonaktif_tidak_bisa_login_dan_sesi_aktif_tertendang(): void
    {
        $kios = Kios::factory()->create(['status' => 'aktif']);

        $user = User::factory()->create([
            'kios_id' => $kios->id,
            'username' => 'user_nonaktif',
            'password' => Hash::make('password123'),
            'is_aktif' => false,
        ]);

        // Coba login saat nonaktif
        $response = $this->post(route('login'), [
            'username' => 'user_nonaktif',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'username' => 'Akun Anda dinonaktifkan. Hubungi admin kios.',
        ]);
        $this->assertGuest();

        // Sekarang aktifkan dulu, lalu login
        $user->update(['is_aktif' => true]);
        $this->actingAs($user);
        $this->get(route('admin.dashboard'))->assertOk();

        // Dinonaktifkan saat sesi sedang berjalan
        $user->update(['is_aktif' => false]);

        // Request berikutnya melalui middleware active harus menendang keluar
        $responseKick = $this->get(route('admin.dashboard'));
        $responseKick->assertRedirect(route('login'));
        $responseKick->assertSessionHasErrors([
            'username' => 'Akun Anda dinonaktifkan. Hubungi admin kios.',
        ]);
        $this->assertGuest();
    }

    /**
     * 7. User dari kios dengan status = nonaktif tidak bisa login dan tertendang jika sudah login.
     */
    public function test_user_dari_kios_nonaktif_tidak_bisa_login_dan_sesi_aktif_tertendang(): void
    {
        $kios = Kios::factory()->create(['status' => 'nonaktif']);

        $user = User::factory()->create([
            'kios_id' => $kios->id,
            'username' => 'user_kios_mati',
            'password' => Hash::make('password123'),
            'is_aktif' => true,
        ]);

        // Coba login saat kios berstatus nonaktif
        $response = $this->post(route('login'), [
            'username' => 'user_kios_mati',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'username' => 'Kios Anda sedang dinonaktifkan.',
        ]);
        $this->assertGuest();

        // Aktifkan kios untuk memulai sesi
        $kios->update(['status' => 'aktif']);
        $this->actingAs($user);
        $this->get(route('admin.dashboard'))->assertOk();

        // Kios dinonaktifkan di tengah sesi
        $kios->update(['status' => 'nonaktif']);

        // Request berikutnya melalui middleware active harus menendang keluar
        $responseKick = $this->get(route('admin.dashboard'));
        $responseKick->assertRedirect(route('login'));
        $responseKick->assertSessionHasErrors([
            'username' => 'Kios Anda sedang dinonaktifkan.',
        ]);
        $this->assertGuest();
    }

    /**
     * 8. Uji isolasi data (Global Scope):
     *    - Buat model sementara/dummy di dalam test yang memakai trait BelongsToKios
     *    - Buat 2 record milik kios A dan 1 record milik kios B
     *    - Saat login sebagai user kios A, query Model::all() hanya menghasilkan 2 record milik kios A
     *    - Saat simpan record baru tanpa mengisi kios_id, kolom terisi otomatis dengan ID kios milik user yang sedang login
     */
    public function test_isolasi_data_global_scope_belongstokios(): void
    {
        // 1. Buat tabel dummy sementara
        Schema::create('dummy_produks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kios_id')->constrained('kios')->cascadeOnDelete();
            $table->string('nama_barang');
            $table->timestamps();
        });

        try {
            $kiosA = Kios::factory()->create(['nama_kios' => 'Kios Sentosa A', 'status' => 'aktif']);
            $userA = User::factory()->create(['kios_id' => $kiosA->id, 'role' => 'admin', 'is_aktif' => true]);

            $kiosB = Kios::factory()->create(['nama_kios' => 'Kios Barokah B', 'status' => 'aktif']);
            $userB = User::factory()->create(['kios_id' => $kiosB->id, 'role' => 'admin', 'is_aktif' => true]);

            // 2. Buat record langsung tanpa scope login
            DummyProduk::withoutGlobalScopes()->create(['kios_id' => $kiosA->id, 'nama_barang' => 'Kecap Manis Kios A']);
            DummyProduk::withoutGlobalScopes()->create(['kios_id' => $kiosA->id, 'nama_barang' => 'Beras Pandan Kios A']);
            DummyProduk::withoutGlobalScopes()->create(['kios_id' => $kiosB->id, 'nama_barang' => 'Minyak Goreng Kios B']);

            $this->assertEquals(3, DummyProduk::withoutGlobalScopes()->count());

            // 3. Login sebagai user Kios A
            $this->actingAs($userA);

            // Query Model::all() terisolasi hanya untuk Kios A
            $produkA = DummyProduk::all();
            $this->assertCount(2, $produkA, 'User Kios A hanya boleh melihat 2 barang miliknya.');
            $this->assertTrue($produkA->every(fn ($item) => $item->kios_id === $kiosA->id));

            // 4. Simpan record baru tanpa mengisi kios_id -> terisi otomatis
            $produkBaruA = DummyProduk::create([
                'nama_barang' => 'Gula Pasir Otomatis Kios A',
            ]);

            $this->assertNotNull($produkBaruA->kios_id);
            $this->assertEquals($kiosA->id, $produkBaruA->kios_id, 'Kolom kios_id harus terisi otomatis dari Auth::user()->kios_id.');

            // Total barang Kios A sekarang 3
            $this->assertCount(3, DummyProduk::all());

            // 5. Beralih login sebagai user Kios B
            $this->actingAs($userB);

            // Query Model::all() terisolasi hanya untuk Kios B
            $produkB = DummyProduk::all();
            $this->assertCount(1, $produkB, 'User Kios B hanya boleh melihat 1 barang miliknya.');
            $this->assertEquals('Minyak Goreng Kios B', $produkB->first()->nama_barang);
            $this->assertEquals($kiosB->id, $produkB->first()->kios_id);

        } finally {
            Schema::dropIfExists('dummy_produks');
        }
    }
}
