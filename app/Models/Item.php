<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'stock',
        'unit',
        'min_stock',
    ];

    protected $casts = [
        'stock' => 'integer',
        'min_stock' => 'integer',
        'category_id' => 'integer'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getIsLowStockAttribute()
    {
        return $this->stock <= $this->min_stock;
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock <= min_stock');
    }

    // Scope untuk filter berdasarkan category
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }
}
