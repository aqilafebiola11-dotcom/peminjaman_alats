<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function download(Request $request, string $type): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user && $user->isStaff(), 403);

        $allowedTypes = ['alat', 'peminjaman', 'pengembalian'];
        abort_unless(in_array($type, $allowedTypes, true), 404);

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = $validated['from'] ?? null;
        $until = $validated['until'] ?? null;

        $rows = match ($type) {
            'alat' => Alat::query()
                ->with('kategori')
                ->when($from, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
                ->when($until, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
                ->orderBy('nama_alat')
                ->get(),
            'peminjaman' => Peminjaman::query()
                ->with(['user', 'approver'])
                ->when($from, fn ($query, $date) => $query->whereDate('tanggal_pinjam', '>=', $date))
                ->when($until, fn ($query, $date) => $query->whereDate('tanggal_pinjam', '<=', $date))
                ->orderByDesc('created_at')
                ->get(),
            'pengembalian' => Pengembalian::query()
                ->with(['peminjaman.user'])
                ->when($from, fn ($query, $date) => $query->whereDate('tanggal_kembali', '>=', $date))
                ->when($until, fn ($query, $date) => $query->whereDate('tanggal_kembali', '<=', $date))
                ->orderByDesc('tanggal_kembali')
                ->get(),
        };

        $title = match ($type) {
            'alat' => 'Laporan Alat',
            'peminjaman' => 'Laporan Peminjaman',
            'pengembalian' => 'Laporan Pengembalian',
        };

        $pdf = Pdf::loadView('reports.report-pdf', [
            'type' => $type,
            'title' => $title,
            'rows' => $rows,
            'printedBy' => $user->nama,
            'printedAt' => now(),
            'periodLabel' => $this->formatPeriodLabel($from, $until),
        ])->setPaper('a4', 'landscape');

        $fileName = sprintf('laporan-%s-%s.pdf', $type, now()->format('Ymd_His'));

        return $pdf->download($fileName);
    }

    protected function formatPeriodLabel(?string $from, ?string $until): string
    {
        if (! $from && ! $until) {
            return 'Semua periode';
        }

        if ($from && ! $until) {
            return 'Mulai ' . Carbon::parse($from)->translatedFormat('d F Y');
        }

        if (! $from && $until) {
            return 'Sampai ' . Carbon::parse($until)->translatedFormat('d F Y');
        }

        return Carbon::parse($from)->translatedFormat('d F Y') . ' s/d ' . Carbon::parse($until)->translatedFormat('d F Y');
    }

    /**
     * Stream the PDF inline for preview (Content-Disposition: inline).
     */
    public function stream(Request $request, string $type): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user && $user->isStaff(), 403);

        $allowedTypes = ['alat', 'peminjaman', 'pengembalian'];
        abort_unless(in_array($type, $allowedTypes, true), 404);

        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'until' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = $validated['from'] ?? null;
        $until = $validated['until'] ?? null;

        $rows = match ($type) {
            'alat' => Alat::query()
                ->with('kategori')
                ->when($from, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
                ->when($until, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
                ->orderBy('nama_alat')
                ->get(),
            'peminjaman' => Peminjaman::query()
                ->with(['user', 'approver'])
                ->when($from, fn ($query, $date) => $query->whereDate('tanggal_pinjam', '>=', $date))
                ->when($until, fn ($query, $date) => $query->whereDate('tanggal_pinjam', '<=', $date))
                ->orderByDesc('created_at')
                ->get(),
            'pengembalian' => Pengembalian::query()
                ->with(['peminjaman.user'])
                ->when($from, fn ($query, $date) => $query->whereDate('tanggal_kembali', '>=', $date))
                ->when($until, fn ($query, $date) => $query->whereDate('tanggal_kembali', '<=', $date))
                ->orderByDesc('tanggal_kembali')
                ->get(),
        };

        $title = match ($type) {
            'alat' => 'Laporan Alat',
            'peminjaman' => 'Laporan Peminjaman',
            'pengembalian' => 'Laporan Pengembalian',
        };

        $pdf = Pdf::loadView('reports.report-pdf', [
            'type' => $type,
            'title' => $title,
            'rows' => $rows,
            'printedBy' => $user->nama,
            'printedAt' => now(),
            'periodLabel' => $this->formatPeriodLabel($from, $until),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream(sprintf('laporan-%s-%s.pdf', $type, now()->format('Ymd_His')));
    }
}
