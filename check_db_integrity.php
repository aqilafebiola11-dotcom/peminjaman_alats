<?php
require 'vendor/autoload.php';
$app = require_once('bootstrap/app.php');
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== CHECKING DATABASE FOR ISSUES ===\n\n";

// Check Users table
$users = DB::table('users')->get();
echo "1. USERS TABLE:\n";
echo "   Total Records: " . count($users) . "\n";
foreach($users as $user) {
    $hasEmail = !empty($user->email);
    $hasPassword = !empty($user->password);
    echo "   ID: {$user->id_user}, Email: {$user->email} (valid: " . ($hasEmail ? "YES" : "NO") . "), Password: " . ($hasPassword ? "SET" : "EMPTY") . "\n";
}

// Check Peminjaman table
$peminjaman = DB::table('peminjaman')->get();
echo "\n2. PEMINJAMAN TABLE:\n";
echo "   Total Records: " . count($peminjaman) . "\n";
$invalidPeminjaman = 0;
foreach($peminjaman as $p) {
    $user = DB::table('users')->where('id_user', $p->id_user)->first();
    if (!$user) {
        echo "   ERROR: Peminjaman ID {$p->id_peminjaman} has invalid id_user {$p->id_user}\n";
        $invalidPeminjaman++;
    }
}
if ($invalidPeminjaman == 0) {
    echo "   ✓ All records have valid id_user\n";
}

// Check Pengembalian table
$pengembalian = DB::table('pengembalian')->get();
echo "\n3. PENGEMBALIAN TABLE:\n";
echo "   Total Records: " . count($pengembalian) . "\n";
$invalidPengembalian = 0;
foreach($pengembalian as $p) {
    $peminjaman = DB::table('peminjaman')->where('id_peminjaman', $p->id_peminjaman)->first();
    if (!$peminjaman) {
        echo "   ERROR: Pengembalian ID {$p->id_pengembalian} has invalid id_peminjaman {$p->id_peminjaman}\n";
        $invalidPengembalian++;
    }
}
if ($invalidPengembalian == 0) {
    echo "   ✓ All records have valid id_peminjaman\n";
}

// Check DetailPeminjaman table
$detailPeminjaman = DB::table('detail_peminjaman')->get();
echo "\n4. DETAIL_PEMINJAMAN TABLE:\n";
echo "   Total Records: " . count($detailPeminjaman) . "\n";
$invalidDetail = 0;
foreach($detailPeminjaman as $d) {
    $peminjaman = DB::table('peminjaman')->where('id_peminjaman', $d->id_peminjaman)->first();
    $alat = DB::table('alat')->where('id_alat', $d->id_alat)->first();
    if (!$peminjaman) {
        echo "   ERROR: DetailPeminjaman ID {$d->id_detail} has invalid id_peminjaman {$d->id_peminjaman}\n";
        $invalidDetail++;
    }
    if (!$alat) {
        echo "   ERROR: DetailPeminjaman ID {$d->id_detail} has invalid id_alat {$d->id_alat}\n";
        $invalidDetail++;
    }
}
if ($invalidDetail == 0) {
    echo "   ✓ All records have valid foreign keys\n";
}

// Check LogAktivitas table
$logAktivitas = DB::table('log_aktivitas')->get();
echo "\n5. LOG_AKTIVITAS TABLE:\n";
echo "   Total Records: " . count($logAktivitas) . "\n";
$invalidLog = 0;
foreach($logAktivitas as $l) {
    $user = DB::table('users')->where('id_user', $l->id_user)->first();
    if (!$user) {
        echo "   ERROR: LogAktivitas ID {$l->id_log} has invalid id_user {$l->id_user}\n";
        $invalidLog++;
    }
}
if ($invalidLog == 0) {
    echo "   ✓ All records have valid id_user\n";
}

// Check Alat table
$alats = DB::table('alat')->get();
echo "\n6. ALAT TABLE:\n";
echo "   Total Records: " . count($alats) . "\n";
$invalidAlat = 0;
foreach($alats as $a) {
    $kategori = DB::table('kategori')->where('id_kategori', $a->id_kategori)->first();
    if (!$kategori) {
        echo "   ERROR: Alat ID {$a->id_alat} has invalid id_kategori {$a->id_kategori}\n";
        $invalidAlat++;
    }
}
if ($invalidAlat == 0) {
    echo "   ✓ All records have valid id_kategori\n";
}

// Check Kategori table
$kategoris = DB::table('kategori')->get();
echo "\n7. KATEGORI TABLE:\n";
echo "   Total Records: " . count($kategoris) . "\n";

echo "\n=== END CHECK ===\n";
