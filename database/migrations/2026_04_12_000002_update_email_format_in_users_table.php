<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Update email values to proper email format
        DB::table('users')->where('email', 'admin')->update(['email' => 'admin@peminjaman.local']);
        DB::table('users')->where('email', 'petugas')->update(['email' => 'petugas@peminjaman.local']);        
        DB::table('users')->where('email', 'peminjam1')->update(['email' => 'peminjam1@peminjaman.local']);
        DB::table('users')->where('email', 'peminjam2')->update(['email' => 'peminjam2@peminjaman.local']);
    }

    public function down(): void
    {
        // Revert to old values
        DB::table('users')->where('email', 'admin@peminjaman.local')->update(['email' => 'admin']);
        DB::table('users')->where('email', 'petugas@peminjaman.local')->update(['email' => 'petugas']);
        DB::table('users')->where('email', 'peminjam1@peminjaman.local')->update(['email' => 'peminjam1']);
        DB::table('users')->where('email', 'peminjam2@peminjaman.local')->update(['email' => 'peminjam2']);
    }
};
