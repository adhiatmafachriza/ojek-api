<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_id', 'driver_id', 'pickup_lat', 'pickup_long', 'destination_lat', 'destination_long', 'fee', 'status'
    ];

    protected $guarded = [];

    public function customer(){
        return $this->belongsTo(User::class, 'customer_id');
    }
}
