<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'user_id',
        'qty',
        'date',
        'note',
    ];

    // Relasi ke Item
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // Relasi ke User (penanggung jawab/pencatat)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
