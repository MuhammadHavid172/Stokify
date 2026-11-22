<?php

namespace Tests\Unit;

use App\Models\Kios;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KiosAndUserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_kios_and_user_relationship_and_helpers(): void
    {
        // 1. Buat Kios
        $kios = Kios::create([
            'nama_kios' => 'Kios Berkah Jaya',
            'alamat' => 'Jl. Merdeka No. 10',
            'no_telepon' => '08123456789',
            'status' => 'aktif',
        ]);

        $this->assertDatabaseHas('kios', [
            'nama_kios' => 'Kios Berkah Jaya',
            'status' => 'aktif',
        ]);

        // 2. Buat Admin User
        $admin = User::create([
            'kios_id' => $kios->id,
            'name' => 'Admin Toko',
            'username' => 'admin_berkah',
            'email' => 'admin@berkah.test',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_aktif' => true,
        ]);

        // 3. Buat Kasir User
        $kasir = User::create([
            'kios_id' => $kios->id,
            'name' => 'Kasir 1',
            'username' => 'kasir_berkah',
            'email' => null, // Email nullable
            'password' => bcrypt('password123'),
            'role' => 'kasir',
            'is_aktif' => true,
        ]);

        // 4. Verifikasi Helper User
        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isKasir());

        $this->assertTrue($kasir->isKasir());
        $this->assertFalse($kasir->isAdmin());

        // 5. Verifikasi Relasi belongsTo (User -> Kios)
        $this->assertEquals($kios->id, $admin->kios->id);
        $this->assertEquals('Kios Berkah Jaya', $admin->kios->nama_kios);

        // 6. Verifikasi Relasi hasMany (Kios -> Users)
        $this->assertCount(2, $kios->users);
        $this->assertTrue($kios->users->contains($admin));
        $this->assertTrue($kios->users->contains($kasir));
    }
}
