<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    protected $fillable = ['asset_id','user_id','loan_date','due_date','returned_at','notes'];

    protected $casts = [
        'loan_date' => 'date',
        'due_date' => 'date',
        'returned_at' => 'datetime',
    ];

    public function asset() { return $this->belongsTo(Asset::class); }
    public function user()  { return $this->belongsTo(User::class); }
}
