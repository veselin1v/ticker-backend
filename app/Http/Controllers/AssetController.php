<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\Trade;
use App\Models\Ticker;
use App\Models\Dividend;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $asset = Asset::where('portfolio_id', $request['portfolio_id'])
                ->where('ticker_id', $request['ticker_id'])->with('ticker');
        if (!$asset->exists()) {
            $id = Asset::create([
                'portfolio_id' => $request['portfolio_id'],
                'ticker_id' => $request['ticker_id']
            ])->id;
        } else {
            $id = $asset->first()->id;
        }
        Trade::create([
            'asset_id' => $id,
            'trade' => 'buy',
            'quantity' => $request['quantity'],
            'price_per_share' => $request['price'],
            'total_price' => $request['quantity'] * $request['price']
        ]);
        $this->update($id);

        $response = Http::get(config('polygon.api') . '/v3/reference/dividends', [
            'ticker' => $asset->first()->ticker->ticker,
            'limit' => 1,
            'apiKey' => config('polygon.api_key')
        ]);
        if (response['status'] == 'OK' && $response['results']) {
            $dividend = $response['results'][0];
            if (Dividend::where('ticker_id', $request['ticker_id'])
                ->where('ex_dividend_date', $dividend['ex_dividend_date'])
                ->doesntExist()) {
                    Dividend::create([
                        'ticker_id' => $request['ticker_id'],
                        'ex_dividend_date' => $dividend['ex_dividend_date'],
                        'pay_date' => $dividend['pay_date'],
                        'cash_amount' => $dividend['cash_amount']
                     ]);       
            }
        }
        return response()->json(['message' => 'The asset has been added!']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $asset = Asset::where('id', $id)->with('trades')->first();
        $asset->ticker = Ticker::where('id', $asset->ticker_id)->first()->ticker;
        foreach ($asset->trades as $trade) {
            $trade->created_at_format = Carbon::parse($trade->created_at)->format('d.m.Y H:i');
        }
        return $asset;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        $trades = Trade::where('asset_id', $id)->get();
        $quantity = 0;
        $invested = 0;
        foreach ($trades as $trade) {
            if ($trade->trade == 'buy') {
                $quantity += $trade->quantity;
                $invested += $trade->total_price;
            } else {
                $quantity -= $trade->quantity;
                $invested -= $trade->total_price;
            }
        }
        $asset = Asset::where('id', $id);
        $tickerPrice = Ticker::where('id', $asset->first()->ticker_id)->first()->price_per_share;
        $asset->update([
           'quantity' => $quantity,
           'invested' => $invested,
           'average_price' => $invested / $quantity,
           'position_worth' => $tickerPrice * $quantity,
           'profit' => ($tickerPrice * $quantity) - $invested,
           'roi' => (($tickerPrice * $quantity) - $invested) / $invested * 100
        ]);
    }

    // public function getTickerPrice($id) {
    //     $ticker = Asset::where('id', $id)->with(['ticker' => function ($query) {
    //             $query->select('id', 'ticker');
    //         }])
    //         ->first()->ticker;
    //     $response = Http::get('https://api.polygon.io/v2/aggs/ticker/'.$ticker->ticker.'/range/1/day/2022-12-21/2022-12-21?adjusted=true&sort=asc&limit=1&apiKey=QAeE2PfbtZ4SwEZLSUUJc5JxHSEogotK');
    //     if ($response['status'] == 'OK') {
    //         $closedPrice = $response['results'][0]['c'];
    //         Ticker::where('id', $ticker->id)->update(['price_per_share' => $closedPrice]);
    //         return $closedPrice;
    //     }
    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
