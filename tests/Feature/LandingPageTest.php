<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    /**
     * Memastikan halaman landing dapat diakses dan merender konten dengan benar.
     */
    public function test_landing_page_can_be_rendered(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // Judul halaman & Meta
        $response->assertSee('<title>Stokify - Sistem Kasir & Manajemen Stok Kios</title>', false);
        $response->assertSee('Stokify adalah sistem informasi manajemen kios multi-tenant');

        // Navigasi & Auth links
        $response->assertSee(route('login'));
        $response->assertSee(route('register'));
        $response->assertSee('Masuk');
        $response->assertSee('Daftar Kios');

        // Section IDs
        $response->assertSee('id="beranda"', false);
        $response->assertSee('id="fitur"', false);
        $response->assertSee('id="cara-kerja"', false);
        $response->assertSee('id="untuk-siapa"', false);
        $response->assertSee('id="kontak"', false);

        // Hero
        $response->assertSee('Kelola Kios Lebih Rapi');
        $response->assertSee('Stok Selalu Akurat');
        $response->assertSee('Kasir cepat, stok otomatis, dan laporan siap cetak dalam satu aplikasi untuk kios Anda.');
        $response->assertSee('Daftar Kios Gratis');
        $response->assertSee('Lihat Fitur');

        // Mengapa Memilih Stokify & 3 Poin Kunci
        $response->assertSee('Mengapa Memilih Stokify?');
        $response->assertSee('Kasir Cepat');
        $response->assertSee('Input belanjaan pembeli dan kembalian terhitung otomatis.');
        $response->assertSee('Stok Otomatis');
        $response->assertSee('Stok terpotong saat terjual dan bertambah saat barang masuk.');
        $response->assertSee('Data Kios Aman');
        $response->assertSee('Setiap kios memiliki ruang data sendiri yang terpisah.');
        $response->assertSee('Real-time');
        $response->assertSee('100% Terpisah');
        $response->assertSee('PDF');

        // 6 Fitur Unggulan
        $response->assertSee('1. Transaksi Kasir');
        $response->assertSee('2. Buka/Tutup Shift');
        $response->assertSee('3. Peringatan Stok Menipis');
        $response->assertSee('4. Laporan PDF');
        $response->assertSee('5. Barang Terlaris');
        $response->assertSee('6. Kelola Akun Kasir');

        // Untuk Siapa
        $response->assertSee('Untuk Kios dan Toko Kecil');
        $response->assertSee('Kios Kelontong / Sembako');
        $response->assertSee('Warung dan Toko Harian');
        $response->assertSee('Toko dengan Banyak Kasir');

        // Cara Kerja
        $response->assertSee('Mulai dalam 3 Langkah');
        $response->assertSee('Daftarkan Kios Anda');
        $response->assertSee('Isi Barang dan Tambah Kasir');
        $response->assertSee('Mulai Berjualan');

        // CTA & Footer
        $response->assertSee('Siap Merapikan Kios Anda?');
        $response->assertSee('halo@stokify.test');
        $response->assertSee('© 2026 Stokify. Dibuat sebagai proyek Sistem Informasi Manajemen.');
    }

    /**
     * Memastikan route login Breeze dapat diakses tanpa error.
     */
    public function test_login_route_is_accessible(): void
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
    }

    /**
     * Memastikan route register Breeze dapat diakses tanpa error.
     */
    public function test_register_route_is_accessible(): void
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
    }
}
