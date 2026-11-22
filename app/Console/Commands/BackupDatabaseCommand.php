<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stokify:backup-db';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat cadangan database MySQL ke storage/app/backup/ dan menyimpan 14 berkas terbaru';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("=== PEMBUATAN CADANGAN DATABASE STOKIFY ===");

        $backupDir = storage_path('app/backup');
        File::ensureDirectoryExists($backupDir);

        $timestamp = Carbon::now('Asia/Jayapura')->format('Ymd-Hi');
        $fileName = "stokify-{$timestamp}.sql";
        $filePath = $backupDir . DIRECTORY_SEPARATOR . $fileName;

        $dbConfig = config('database.connections.' . config('database.default'));
        $host = $dbConfig['host'] ?? '127.0.0.1';
        $port = $dbConfig['port'] ?? 3306;
        $database = $dbConfig['database'];
        $username = $dbConfig['username'];
        $password = $dbConfig['password'] ?? '';

        $this->line("Target berkas: storage/app/backup/{$fileName}");

        // Jalankan mysqldump
        $command = [
            'mysqldump',
            "--host={$host}",
            "--port={$port}",
            "--user={$username}",
        ];

        if ($password !== '' && $password !== null) {
            $command[] = "--password={$password}";
        }

        $command[] = '--single-transaction';
        $command[] = '--quick';
        $command[] = '--default-character-set=utf8mb4';
        $command[] = $database;

        $process = new Process($command);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error("Gagal menjalankan mysqldump: " . $process->getErrorOutput());
            return Command::FAILURE;
        }

        File::put($filePath, $process->getOutput());
        $fileSizeKb = round(File::size($filePath) / 1024, 2);

        $this->info("[SUKSES] Cadangan database berhasil dibuat ({$fileSizeKb} KB).");

        // Retensi: Hanya pertahankan 14 berkas backup terbaru
        $allBackups = File::glob($backupDir . DIRECTORY_SEPARATOR . 'stokify-*.sql');
        
        // Urutkan berdasarkan waktu modifikasi terbaru lebih dahulu
        usort($allBackups, function ($a, $b) {
            return filemtime($b) <=> filemtime($a);
        });

        $maxKeep = 14;
        $deletedCount = 0;

        if (count($allBackups) > $maxKeep) {
            $filesToDelete = array_slice($allBackups, $maxKeep);
            foreach ($filesToDelete as $oldFile) {
                File::delete($oldFile);
                $deletedCount++;
            }
        }

        $remainingCount = min(count($allBackups), $maxKeep);
        $this->line("Retensi berkas: {$remainingCount} berkas cadangan tersimpan (menghapus {$deletedCount} berkas lama).");

        // Peringatan Keamanan Cadangan Off-Site
        $this->newLine();
        $this->comment("--------------------------------------------------------------------------------");
        $this->comment("PERINGATAN PENTING:");
        $this->comment("Berkas cadangan ini tersimpan di hard disk lokal server.");
        $this->comment("Demi keamanan data usaha jika terjadi kerusakan fisik server atau bencana,");
        $this->comment("salin berkas backup ini ke media penyimpanan di luar server (off-site storage)");
        $this->comment("seperti cloud storage (S3/Google Drive) atau flashdisk eksternal secara berkala.");
        $this->comment("--------------------------------------------------------------------------------");

        return Command::SUCCESS;
    }
}
