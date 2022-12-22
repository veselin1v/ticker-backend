<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'balance'
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
