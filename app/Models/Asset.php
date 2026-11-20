<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    public const DEFAULT_CODE_PREFIX = 'AST';

    public const CONDITIONS = [
        'baik' => 'Baik',
        'rusak_ringan' => 'Rusak Ringan',
        'rusak_berat' => 'Rusak Berat',
    ];

    public const STATUSES = [
        'tersedia' => 'Tersedia',
        'dipinjam' => 'Dipinjam',
        'maintenance' => 'Maintenance',
    ];

    protected $fillable = [
        'category_id', 'asset_code', 'name', 'brand', 'serial_number',
        'purchase_date', 'condition', 'status', 'photo', 'notes',
    ];

    protected $casts = ['purchase_date' => 'date'];

    public static function nextCodeFor(Category $category): string
    {
        $prefix = $category->code_prefix ?: self::DEFAULT_CODE_PREFIX;

        // withTrashed: kode aset yang sudah dihapus tidak boleh dipakai ulang
        $highest = static::withTrashed()
            ->where('asset_code', 'like', $prefix . '-%')
            ->pluck('asset_code')
            ->map(fn (string $code) => preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)$/i', $code, $match)
                ? (int) $match[1]
                : 0)
            ->max() ?? 0;

        return sprintf('%s-%03d', $prefix, $highest + 1);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    protected function photoUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->photo ? asset('storage/'.$this->photo) : null
        );
    }
}
