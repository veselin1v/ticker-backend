<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetsDividend extends Model
{
    protected $fillable = [
        'asset_id',
        'dividend_id',
        'received_at',
        'amount'
    ];

    public function asset()
    {
        return $this->hasOne(Asset::class, 'id', 'asset_id');
    }

    public function dividend()
    {
        return $this->hasOne(Dividend::class, 'id', 'dividend_id');
    }
}
