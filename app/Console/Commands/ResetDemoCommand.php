<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ResetDemoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stokify:reset-demo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mereset database dan mengisi ulang data contoh demo (Hanya untuk local/staging)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 1. Tolak keras jika environment adalah production
        if (app()->isProduction()) {
            $this->error("PERINGATAN: Perintah reset demo DITOLAK pada environment production demi melindungi data nyata!");
            return Command::FAILURE;
        }

        $this->warn("=== MERESET DATA DEMO STOKIFY ===");
        $this->line("Menjalankan migrasi ulang dan pengisian data contoh...");

        // 2. Jalankan migrate:fresh --seed
        $exitCode = Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            $this->error("Gagal mereset database. Kode status: {$exitCode}");
            return Command::FAILURE;
        }

        $this->info("[SUKSES] Database berhasil direset dan diisi ulang data demo.");
        $this->line("Akun Demo:");
        $this->line("- Admin Maju: admin_maju@kios.test / password123");
        $this->line("- Kasir Maju: kasir_maju / password123");
        $this->line("- Admin Sejahtera: admin_sejahtera@kios.test / password123");
        $this->line("- Kasir Sejahtera: kasir_sejahtera / password123");
        $this->line("- Akun Pengguna: hafidriski.ha@gmail.com / 12345678");

        return Command::SUCCESS;
    }
}
