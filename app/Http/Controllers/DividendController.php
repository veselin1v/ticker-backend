<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Portfolio;
use App\Models\Asset;
use App\Models\Dividend;
use App\Models\Ticker;
use Illuminate\Support\Carbon;

class DividendController extends Controller
{
    public function index()
    {
        $portfolio = Portfolio::where('user_id', Auth::user()->id)->first();
        if ($portfolio) {
            $assets = Asset::where('portfolio_id', $portfolio->id)->get();
            $dividends = [];
            $upcomingDividends = [];
    
            foreach ($assets as $asset) {
                $assetDividends = Dividend::where('ticker_id', $asset->ticker_id)->get();
                if (!empty($assetDividends)) {
                    foreach ($assetDividends as $dividend) {
                        $ticker = Ticker::where('id', $dividend->ticker_id)->first()->ticker;
                        array_push($dividends, [
                            'description' => $ticker . ' $' . $dividend->cash_amount,
                            'dates' => $dividend->ex_dividend_date,
                            'color' => 'red'
                        ]);
                        if ($dividend->ex_dividend_date > Carbon::today()) {
                            array_push($upcomingDividends, [
                                'date' => Carbon::parse($dividend->ex_dividend_date)->format('d M Y'),
                                'ticker' =>  $ticker,
                                'amount' => $dividend->cash_amount
                            ]);
                        }
                    }
                }
            }

            return response()->json([
                'dividends' => $dividends,
                'upcoming_dividends' => $upcomingDividends
            ]);
        }
    }

    public function store($ticker) {
        $response = Http::get(config('polygon.api') . '/v3/reference/dividends', [
            'ticker' => $ticker,
            'limit' => 1,
            'apiKey' => config('polygon.api_key')
        ]);
        dd($response);
        if ($response['status'] == 'OK') {
            foreach ($response['results'] as $ticker) {
                // Ticker::create([
                //     'ticker' => $ticker['ticker'],
                //     'name'  => $ticker['name']
                // ]);
            }
        }
    }
}
