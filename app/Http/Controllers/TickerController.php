<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticker;

class TickerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search($name)
    {
        $tickers = Ticker::where('ticker', 'LIKE', '%'.$name.'%')
        ->orWhere('name', 'LIKE', '%'.$name.'%')
        ->get();
        return response()->json($tickers);
    }
}
