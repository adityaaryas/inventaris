<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function items ()
    {
        return $this->hasMany(Item::class);
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    // Accessor untuk menghitung jumlah items
    public function getItemsCountAttribute()
    {
        return $this->items()->count();
    }

    public function scopeWithItemsCountIf($query, $withItemsCount)
    {
        if ($withItemsCount == 'true') {
            $query->withCount('items');
        }

        return $query;
    }

    public function scopeWithItemsIf($query, $withItems)
    {
        if ($withItems == 'true') {
            $query->with('items');
        }

        return $query;
    }
    
}
