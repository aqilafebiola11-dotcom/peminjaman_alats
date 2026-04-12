<?php

namespace Database\Seeders;

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Users
        User::create([
            'nama' => 'Administrator',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'nama' => 'Petugas Gudang',
            'email' => 'petugas@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'petugas',
        ]);

        User::create([
            'nama' => 'Budi Santoso',
            'email' => 'peminjam1@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'peminjam',
        ]);

        User::create([
            'nama' => 'Siti Rahayu',
            'email' => 'peminjam2@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'peminjam',
        ]);

        User::create([
            'nama' => 'Ahmad Wijaya',
            'email' => 'peminjam3@gmail.com',
            'password' => Hash::make('password'),
            'role' => 'peminjam',
        ]);

        // Create Kategori
        $kategoriElektronik = Kategori::create([
            'nama_kategori' => 'Elektronik',
            'keterangan' => 'Peralatan elektronik seperti laptop, proyektor, dll.',
        ]);

        $kategoriMekanik = Kategori::create([
            'nama_kategori' => 'Mekanik',
            'keterangan' => 'Peralatan mekanik seperti bor, gerinda, dll.',
        ]);

        $kategoriLabor = Kategori::create([
            'nama_kategori' => 'Laboratorium',
            'keterangan' => 'Peralatan laboratorium seperti mikroskop, pipet, dll.',
        ]);

        $kategoriOlahraga = Kategori::create([
            'nama_kategori' => 'Olahraga',
            'keterangan' => 'Peralatan olahraga seperti bola, raket, dll.',
        ]);

        $kategoriMultimedia = Kategori::create([
            'nama_kategori' => 'Multimedia',
            'keterangan' => 'Peralatan multimedia seperti kamera, mic, dll.',
        ]);

        // Create Alat
        $alatData = [
            // Elektronik
            ['id_kategori' => $kategoriElektronik->id_kategori, 'nama_alat' => 'Laptop ASUS', 'stok' => 10, 'kondisi' => 'Baik', 'status' => 'tersedia'],
            ['id_kategori' => $kategoriElektronik->id_kategori, 'nama_alat' => 'Proyektor Epson', 'stok' => 5, 'kondisi' => 'Baik', 'status' => 'tersedia'],
            ['id_kategori' => $kategoriElektronik->id_kategori, 'nama_alat' => 'Printer Canon', 'stok' => 3, 'kondisi' => 'Baik', 'status' => 'tersedia'],

            // Mekanik
            ['id_kategori' => $kategoriMekanik->id_kategori, 'nama_alat' => 'Bor Listrik Bosch', 'stok' => 8, 'kondisi' => 'Baik', 'status' => 'tersedia'],
            ['id_kategori' => $kategoriMekanik->id_kategori, 'nama_alat' => 'Gerinda Tangan', 'stok' => 6, 'kondisi' => 'Baik', 'status' => 'tersedia'],
            ['id_kategori' => $kategoriMekanik->id_kategori, 'nama_alat' => 'Mesin Las', 'stok' => 2, 'kondisi' => 'Baik', 'status' => 'tersedia'],

            // Laboratorium
            ['id_kategori' => $kategoriLabor->id_kategori, 'nama_alat' => 'Mikroskop', 'stok' => 15, 'kondisi' => 'Baik', 'status' => 'tersedia'],
            ['id_kategori' => $kategoriLabor->id_kategori, 'nama_alat' => 'Pipet Ukur', 'stok' => 20, 'kondisi' => 'Baik', 'status' => 'tersedia'],
            ['id_kategori' => $kategoriLabor->id_kategori, 'nama_alat' => 'Timbangan Digital', 'stok' => 5, 'kondisi' => 'Baik', 'status' => 'tersedia'],

            // Olahraga
            ['id_kategori' => $kategoriOlahraga->id_kategori, 'nama_alat' => 'Bola Voli', 'stok' => 10, 'kondisi' => 'Baik', 'status' => 'tersedia'],
            ['id_kategori' => $kategoriOlahraga->id_kategori, 'nama_alat' => 'Raket Badminton', 'stok' => 12, 'kondisi' => 'Baik', 'status' => 'tersedia'],
            ['id_kategori' => $kategoriOlahraga->id_kategori, 'nama_alat' => 'Bola Basket', 'stok' => 8, 'kondisi' => 'Baik', 'status' => 'tersedia'],

            // Multimedia
            ['id_kategori' => $kategoriMultimedia->id_kategori, 'nama_alat' => 'Kamera DSLR Canon', 'stok' => 4, 'kondisi' => 'Baik', 'status' => 'tersedia'],
            ['id_kategori' => $kategoriMultimedia->id_kategori, 'nama_alat' => 'Tripod', 'stok' => 6, 'kondisi' => 'Baik', 'status' => 'tersedia'],
            ['id_kategori' => $kategoriMultimedia->id_kategori, 'nama_alat' => 'Microphone Condenser', 'stok' => 5, 'kondisi' => 'Baik', 'status' => 'tersedia'],
        ];

        foreach ($alatData as $alat) {
            Alat::create($alat);
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('Admin    - email: admin@gmail.com, password: password');
        $this->command->info('Petugas  - email: petugas@gmail.com, password: password');
        $this->command->info('Peminjam - email: peminjam1@gmail.com, password: password');
    }
}
