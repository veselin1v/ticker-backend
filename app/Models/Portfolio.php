<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'equity',
        'invested',
        'roi',
        'annual_dividend',
        'dividend_yield'
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }
}
