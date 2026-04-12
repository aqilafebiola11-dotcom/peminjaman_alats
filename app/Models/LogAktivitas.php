<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogAktivitas extends Model
{

    public $timestamps = false;


    protected $table = 'log_aktivitas';


    protected $primaryKey = 'id_log';

    protected $fillable = [
        'id_user',
        'aktivitas',
        'tanggal_aktifitas',
    ];


    protected function casts(): array
    {
        return [
            'tanggal_aktifitas' => 'datetime',
        ];
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->tanggal_aktifitas) {
                $model->tanggal_aktifitas = now();
            }
        });
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user');
    }


    public static function log(string $aktivitas, ?int $userId = null): self
    {
        return static::create([
            'id_user' => $userId ?? auth()->id(),
            'aktivitas' => $aktivitas,
            'tanggal_aktifitas' => now(),
        ]);
    }
}
