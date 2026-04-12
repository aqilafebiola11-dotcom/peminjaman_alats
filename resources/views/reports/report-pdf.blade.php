<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
        }

        .header {
            margin-bottom: 16px;
        }

        .title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .meta {
            font-size: 11px;
            color: #374151;
            margin-bottom: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            font-weight: 700;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $title }}</div>
        <div class="meta">Dicetak oleh: {{ $printedBy }}</div>
        <div class="meta">Tanggal cetak: {{ $printedAt->format('d-m-Y H:i') }}</div>
        <div class="meta">Periode data: {{ $periodLabel }}</div>
    </div>

    <table>
        @if ($type === 'alat')
            <thead>
                <tr>
                    <th style="width: 40px;">No</th>
                    <th>Nama Alat</th>
                    <th>Kategori</th>
                    <th style="width: 70px;" class="text-right">Stok</th>
                    <th>Kondisi</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $index => $row)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row->nama_alat }}</td>
                        <td>{{ $row->kategori?->nama_kategori ?? '-' }}</td>
                        <td class="text-right">{{ $row->stok }}</td>
                        <td>{{ $row->kondisi }}</td>
                        <td>{{ ucfirst($row->status) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Tidak ada data untuk periode yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        @elseif ($type === 'peminjaman')
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th>Peminjam</th>
                    <th style="width: 110px;">Tgl Pinjam</th>
                    <th style="width: 120px;">Rencana Kembali</th>
                    <th style="width: 95px;">Status</th>
                    <th>Disetujui Oleh</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row->id_peminjaman }}</td>
                        <td>{{ $row->user?->nama ?? $row->user?->email ?? '-' }}</td>
                        <td>{{ optional($row->tanggal_pinjam)->format('d-m-Y') }}</td>
                        <td>{{ optional($row->tanggal_kembali)->format('d-m-Y') }}</td>
                        <td>{{ ucfirst($row->status) }}</td>
                        <td>{{ $row->approver?->nama ?? $row->approver?->email ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Tidak ada data untuk periode yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        @else
            <thead>
                <tr>
                    <th style="width: 80px;">ID</th>
                    <th style="width: 110px;">ID Peminjaman</th>
                    <th>Peminjam</th>
                    <th style="width: 110px;">Tgl Kembali</th>
                    <th style="width: 110px;" class="text-right">Denda</th>
                    <th>Kondisi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row->id_pengembalian }}</td>
                        <td>{{ $row->id_peminjaman }}</td>
                        <td>{{ $row->peminjaman?->user?->nama ?? $row->peminjaman?->user?->email ?? '-' }}</td>
                        <td>{{ optional($row->tanggal_kembali)->format('d-m-Y') }}</td>
                        <td class="text-right">{{ number_format($row->denda, 0, ',', '.') }}</td>
                        <td>{{ $row->kondisi_kembali ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Tidak ada data untuk periode yang dipilih.</td>
                    </tr>
                @endforelse
            </tbody>
        @endif
    </table>
</body>
</html>
