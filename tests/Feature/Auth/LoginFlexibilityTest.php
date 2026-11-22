<?php

namespace Tests\Feature\Auth;

use App\Models\Kios;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginFlexibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_bisa_dengan_email_username_maupun_nama_kios(): void
    {
        $kios = Kios::create([
            'nama_kios' => 'Kios Apiid',
            'status' => 'aktif',
        ]);

        $admin = User::create([
            'kios_id' => $kios->id,
            'name' => 'Hafid Riski',
            'username' => 'hafid_riski',
            'email' => 'hafidriski.ha@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        // 1. Login menggunakan Nama Kios "Kios Apiid"
        $responseKios = $this->post(route('login'), [
            'login' => 'Kios Apiid',
            'password' => '12345678',
        ]);
        $responseKios->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        auth()->logout();

        // 2. Login menggunakan Email
        $responseEmail = $this->post(route('login'), [
            'login' => 'hafidriski.ha@gmail.com',
            'password' => '12345678',
        ]);
        $responseEmail->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        auth()->logout();

        // 3. Login menggunakan Username
        $responseUsername = $this->post(route('login'), [
            'login' => 'hafid_riski',
            'password' => '12345678',
        ]);
        $responseUsername->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);

        auth()->logout();

        // 4. Login menggunakan Nama Usaha tanpa awalan Kios ("Apiid")
        $responseNamaUsaha = $this->post(route('login'), [
            'login' => 'Apiid',
            'password' => '12345678',
        ]);
        $responseNamaUsaha->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    /**
     * Memastikan jika email atau nama usaha tidak terdaftar, sistem menampilkan pesan spesifik.
     */
    public function test_login_memberikan_pesan_spesifik_jika_email_atau_nama_usaha_tidak_terdaftar(): void
    {
        $response = $this->post(route('login'), [
            'login' => 'email.tidak.ada@gmail.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors([
            'login' => 'Email atau Nama Usaha tidak terdaftar.',
        ]);
        $this->assertGuest();
    }

    /**
     * Memastikan jika user terdaftar tetapi kata sandi salah, sistem menampilkan pesan bahwa kata sandi salah.
     */
    public function test_login_memberikan_pesan_spesifik_jika_password_salah(): void
    {
        $kios = Kios::create([
            'nama_kios' => 'Kios Amanah',
            'status' => 'aktif',
        ]);

        User::create([
            'kios_id' => $kios->id,
            'name' => 'Owner Amanah',
            'email' => 'owner@amanah.test',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        $response = $this->post(route('login'), [
            'login' => 'owner@amanah.test',
            'password' => 'passwordsalah',
        ]);

        $response->assertSessionHasErrors([
            'login' => 'Kata sandi yang Anda masukkan salah.',
        ]);
        $this->assertGuest();
    }
}
