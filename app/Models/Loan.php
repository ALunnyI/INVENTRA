<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    public const MAX_ACTIVE_PER_USER = 3;
    public const MAX_DURATION_DAYS   = 30;

    public const STATUS_LABELS = [
        'dipinjam'     => 'Dipinjam',
        'terlambat'    => 'Terlambat',
        'dikembalikan' => 'Dikembalikan',
    ];

    protected $fillable = [
        'asset_id', 'user_id', 'loan_date', 'due_date', 'returned_at', 'notes',
    ];

    protected $casts = [
        'loan_date'   => 'date',
        'due_date'    => 'date',
        'returned_at' => 'datetime',
    ];

    public function asset()
    {
        // withTrashed: riwayat tetap bisa menampilkan aset yang sudah di-soft-delete
        return $this->belongsTo(Asset::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('returned_at');
    }

    public function scopeOverdue($query)
    {
        return $query->whereNull('returned_at')->whereDate('due_date', '<', today());
    }

    public function getStatusAttribute(): string
    {
        if ($this->returned_at) {
            return 'dikembalikan';
        }

        return $this->due_date->lt(today()) ? 'terlambat' : 'dipinjam';
    }
}
