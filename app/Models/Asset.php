<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes;

    public const CONDITIONS = [
        'baik'         => 'Baik',
        'rusak_ringan' => 'Rusak Ringan',
        'rusak_berat'  => 'Rusak Berat',
    ];

    public const STATUSES = [
        'tersedia'    => 'Tersedia',
        'dipinjam'    => 'Dipinjam',
        'maintenance' => 'Maintenance',
    ];

    protected $fillable = [
        'category_id','asset_code','name','brand','serial_number',
        'purchase_date','condition','status','photo','notes',
    ];

    protected $casts = ['purchase_date' => 'date'];

    public function category() { return $this->belongsTo(Category::class); }
    public function loans()    { return $this->hasMany(Loan::class); }

    protected function photoUrl(): Attribute
    {
        return Attribute::get(
            fn () => $this->photo ? asset('storage/' . $this->photo) : null
        );
    }
}
