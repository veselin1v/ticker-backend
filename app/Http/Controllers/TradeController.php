<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Portfolio;
use App\Models\Trade;
use Auth;

class TradeController extends Controller
{
    public function store(Request $request)
    {
        Trade::create([
            'asset_id' => $request['asset_id'],
            'trade' => $request['trade'],
            'quantity' => $request['quantity'],
            'price_per_share' => $request['price_per_share'],
            'total_price' => $request['quantity'] * $request['price_per_share']
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'The trade has been added successfully!'
        ]);
    }

}
