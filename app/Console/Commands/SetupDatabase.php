<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use PDO;

class SetupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'servisin:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat database MySQL servisin secara otomatis dan menjalankan migrasi serta seeder';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('===================================================');
        $this->info('  SETUP DATABASE SERVISIN UNTUK LARAGON / MYSQL');
        $this->info('===================================================');

        $host = config('database.connections.mysql.host', '127.0.0.1');
        $port = config('database.connections.mysql.port', '3306');
        $database = config('database.connections.mysql.database', 'servisin');
        $username = config('database.connections.mysql.username', 'root');
        $password = config('database.connections.mysql.password', '');

        $this->info("1. Menghubungkan ke MySQL Server ({$host}:{$port})...");

        try {
            $pdo = new PDO("mysql:host={$host};port={$port}", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->info("2. Membuat basis data '{$database}' jika belum ada...");
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $this->info("   Basis data '{$database}' berhasil dibuat / siap digunakan!");

        } catch (Exception $e) {
            $this->error("Gagal menghubungkan ke MySQL: " . $e->getMessage());
            $this->warn("Pastikan MySQL di Laragon sudah dalam kondisi 'Start / Running'.");
            return Command::FAILURE;
        }

        $this->info("3. Menjalankan migrasi dan seeding data awal...");
        $exitCode = Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);

        $this->line(Artisan::output());

        if ($exitCode === 0) {
            $this->info('===================================================');
            $this->info('  SETUP BERHASIL! APLIKASI SERVISIN SIAP DIGUNAKAN');
            $this->info('===================================================');
            $this->line('Akun Login Bawaan:');
            $this->line(' - Pemilik : pemilik / password123');
            $this->line(' - Staf    : staf / password123');
            $this->line('Silakan refresh halaman web di browser Anda.');
            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }
}
