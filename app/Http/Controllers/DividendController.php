<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use App\Models\Portfolio;
use App\Models\Asset;
use App\Models\Dividend;
use App\Models\AssetsDividend;
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
            $receivedDividends = [];
    
            foreach ($assets as $asset) {
                $tickerDividends = Dividend::where('ticker_id', $asset->ticker_id)->get();
                if (!empty($tickerDividends)) {
                    foreach ($tickerDividends as $dividend) {
                        $ticker = Ticker::where('id', $dividend->ticker_id)->first()->ticker;
                        array_push($dividends, [
                            'description' => $ticker . ' $' . $dividend->cash_amount,
                            'dates' => $dividend->ex_dividend_date,
                            'color' => 'red'
                        ]);
                        if ($dividend->ex_dividend_date > Carbon::today()) {
                            array_push($upcomingDividends, [
                                'date' => $dividend->ex_dividend_date,
                                'date_format' => Carbon::parse($dividend->ex_dividend_date)->format('d M Y'),
                                'ticker' =>  $ticker,
                                'amount' => $dividend->cash_amount
                            ]);
                        }
                    }
                }
                $assetDividends = AssetsDividend::where('asset_id', $asset->id)->get();
                if (!empty($assetDividends)) {
                    foreach ($assetDividends as $dividend) {
                        array_push($receivedDividends, [
                            'ticker' => Ticker::where('id', $asset->ticker_id)->first()->ticker,
                            'amount' => $dividend->amount,
                            'received_at' => $dividend->received_at,
                            'received_at_format' => Carbon::parse($dividend->received_at)->format('d M Y')
                        ]);
                    }
                }
            }

            $date = array_column($upcomingDividends, 'date');
            $receivedAt = array_column($receivedDividends, 'received_at');
            array_multisort($upcomingDividends, SORT_ASC, $date);   
            array_multisort($receivedDividends, SORT_ASC, $receivedAt);   

            return response()->json([
                'dividends' => $dividends,
                'upcoming_dividends' => $upcomingDividends,
                'received_dividends' => $receivedDividends
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
