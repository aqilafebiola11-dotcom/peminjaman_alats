<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function printResult(bool $ok, string $label, string $detail = ''): void
{
    $status = $ok ? 'PASS' : 'FAIL';
    echo sprintf("[%s] %s", $status, $label);

    if ($detail !== '') {
        echo " => {$detail}";
    }

    echo PHP_EOL;
}

echo "=============================================" . PHP_EOL;
echo "TEST FLOW PEMINJAMAN (DITOLAK/PENDING/SETUJU)" . PHP_EOL;
echo "=============================================" . PHP_EOL;

$passed = 0;
$failed = 0;

DB::beginTransaction();

try {
    $petugas = User::query()
        ->whereIn('user_role', ['petugas', 'admin'])
        ->first();

    $peminjam = User::query()
        ->where('user_role', 'peminjam')
        ->first();

    if (! $petugas || ! $peminjam) {
        throw new RuntimeException('User petugas/admin atau peminjam tidak ditemukan.');
    }

    $alat = Alat::query()
        ->where('status', 'tersedia')
        ->where('stok', '>', 0)
        ->first();

    if (! $alat) {
        $kategori = Kategori::query()->first();

        if (! $kategori) {
            $kategori = Kategori::create([
                'nama_kategori' => 'Test Kategori',
                'keterangan' => 'Kategori untuk test flow peminjaman.',
            ]);
        }

        $alat = Alat::create([
            'id_kategori' => $kategori->id_kategori,
            'nama_alat' => 'Alat Test Flow',
            'stok' => 10,
            'kondisi' => 'Baik',
            'status' => 'tersedia',
        ]);
    }

    $createLoan = function (string $tanggalKembali) use ($peminjam, $alat): Peminjaman {
        $loan = Peminjaman::create([
            'id_user' => $peminjam->id_user,
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_kembali' => $tanggalKembali,
            'status' => 'menunggu',
        ]);

        $loan->detailPeminjaman()->create([
            'id_alat' => $alat->id_alat,
            'jumlah_pinjam' => 1,
        ]);

        return $loan->fresh();
    };

    $pendingLoan = $createLoan(now()->addDays(2)->toDateString());
    $ok = $pendingLoan->status === 'menunggu';
    printResult($ok, 'Status awal pengajuan adalah pending/menunggu', "status={$pendingLoan->status}");
    $ok ? $passed++ : $failed++;

    $rejectedLoan = $createLoan(now()->addDays(3)->toDateString());
    $okReject = $rejectedLoan->reject();
    $rejectedLoan->refresh();
    $ok = $okReject && $rejectedLoan->status === 'ditolak';
    printResult($ok, 'Petugas dapat menolak peminjaman', "status={$rejectedLoan->status}");
    $ok ? $passed++ : $failed++;

    $okPending = $rejectedLoan->setPending();
    $rejectedLoan->refresh();
    $ok = $okPending && $rejectedLoan->status === 'menunggu';
    printResult($ok, 'Petugas dapat ubah status ditolak menjadi pending', "status={$rejectedLoan->status}");
    $ok ? $passed++ : $failed++;

    $stokSebelum = $alat->fresh()->stok;
    $okApprove = $rejectedLoan->approve($petugas->id_user);
    $rejectedLoan->refresh();
    $stokSesudah = $alat->fresh()->stok;
    $ok = $okApprove
        && $rejectedLoan->status === 'disetujui'
        && (int) $rejectedLoan->disetujui_oleh === (int) $petugas->id_user
        && $stokSesudah === ($stokSebelum - 1);

    printResult(
        $ok,
        'Petugas dapat menyetujui peminjaman pending dan stok berkurang',
        "status={$rejectedLoan->status}, stok={$stokSebelum}->{$stokSesudah}"
    );
    $ok ? $passed++ : $failed++;

    $loanLate = $createLoan(now()->subDays(2)->toDateString());
    $loanLate->approve($petugas->id_user);

    $returnDate = now()->toDateString();
    $pengembalian = $loanLate->returnItems($returnDate, 'Menunggu pemeriksaan petugas');
    $loanLate->refresh();

    $expectedDenda = 2 * 5000;
    $ok = $pengembalian !== null
        && (int) $pengembalian->denda === $expectedDenda
        && $loanLate->status === 'dikembalikan';

    printResult(
        $ok,
        'Denda keterlambatan dihitung otomatis oleh sistem saat pengembalian',
        'denda=' . ($pengembalian?->denda ?? 'null')
    );
    $ok ? $passed++ : $failed++;

    echo PHP_EOL;
    echo "TOTAL PASS: {$passed}" . PHP_EOL;
    echo "TOTAL FAIL: {$failed}" . PHP_EOL;

    if ($failed === 0) {
        echo 'KESIMPULAN: Semua flow berhasil sesuai skenario.' . PHP_EOL;
    } else {
        echo 'KESIMPULAN: Ada flow yang belum sesuai, cek output FAIL di atas.' . PHP_EOL;
    }
} catch (Throwable $e) {
    echo '[ERROR] ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
} finally {
    DB::rollBack();
}
