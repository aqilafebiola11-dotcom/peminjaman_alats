<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Peminjaman extends Model
{
    use HasFactory;

    public $timestamps = false;


    protected $table = 'peminjaman';
    protected $primaryKey = 'id_peminjaman';

    protected $fillable = [
        'id_user',
        'tanggal_pinjam',
        'tanggal_kembali',
        'status',
        'disetujui_oleh',
    ];


    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'date',
            'tanggal_kembali' => 'date',
            'created_at' => 'datetime',
        ];
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = now();
        });
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }


    public function peminjam(): BelongsTo
    {
        return $this->user();
    }


    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }


    public function detailPeminjaman(): HasMany
    {
        return $this->hasMany(DetailPeminjaman::class, 'id_peminjaman');
    }


    public function pengembalian(): HasOne
    {
        return $this->hasOne(Pengembalian::class, 'id_peminjaman');
    }


    public function isPending(): bool
    {
        return $this->status === 'menunggu';
    }


    public function isApproved(): bool
    {
        return $this->status === 'disetujui';
    }


    public function isReturned(): bool
    {
        return $this->status === 'dikembalikan';
    }


    public function isRejected(): bool
    {
        return $this->status === 'ditolak';
    }


    public function approve(int $approverId): bool
    {
        if (!$this->isPending()) {
            return false;
        }


        foreach ($this->detailPeminjaman as $detail) {
            $alat = $detail->alat;
            $alat->stok -= $detail->jumlah_pinjam;

            if ($alat->stok <= 0) {
                $alat->status = 'dipinjam';
            } else {
                $alat->status = 'tersedia';
            }

            $alat->save();
        }

        $this->status = 'disetujui';
        $this->disetujui_oleh = $approverId;
        return $this->save();
    }


    public function reject(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        $this->status = 'ditolak';
        return $this->save();
    }

    public function setPending(): bool
    {
        if (! $this->isRejected()) {
            return false;
        }

        $this->status = 'menunggu';
        $this->disetujui_oleh = null;

        return $this->save();
    }


    public function returnItems(string $tanggalKembali, string $kondisiKembali): ?Pengembalian
    {
        if (!$this->isApproved()) {
            return null;
        }


        $returnDate = \Carbon\Carbon::parse($tanggalKembali);
        $plannedDate = $this->tanggal_kembali;
        $daysLate = $returnDate->diffInDays($plannedDate, false);
        $denda = $daysLate < 0 ? abs($daysLate) * 5000 : 0;


        foreach ($this->detailPeminjaman as $detail) {
            $alat = $detail->alat;
            $alat->stok += $detail->jumlah_pinjam;
            $alat->status = 'tersedia';
            $alat->save();
        }


        $pengembalian = Pengembalian::create([
            'id_peminjaman' => $this->id_peminjaman,
            'tanggal_kembali' => $tanggalKembali,
            'denda' => $denda,
            'kondisi_kembali' => $kondisiKembali,
        ]);


        $this->status = 'dikembalikan';
        $this->save();

        return $pengembalian;
    }

    public function getHariTerlambatAttribute(): int
    {
        if (!$this->isApproved()) {
            return 0;
        }
        $plannedDate = \Carbon\Carbon::parse($this->tanggal_kembali);
        $daysLate = now()->diffInDays($plannedDate, false);
        return $daysLate < 0 ? abs($daysLate) : 0;
    }

    public function getEstimasiDendaAttribute(): int
    {
        return $this->hari_terlambat * 5000;
    }
}
