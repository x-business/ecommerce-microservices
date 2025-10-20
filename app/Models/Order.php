<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_email',
        'customer_name',
        'total_amount',
        'status',
        'shipping_address',
        'billing_address',
        'payment_method',
        'notes'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2'
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items')
                    ->withPivot('quantity', 'unit_price', 'total_price')
                    ->withTimestamps();
    }
}
