<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\Share;
use App\Models\Ticker;
use Illuminate\Support\Facades\Http;

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
                ->where('ticker_id', $request['ticker_id']);
        if (!$asset->exists()) {
            $id = Asset::create([
                'portfolio_id' => $request['portfolio_id'],
                'ticker_id' => $request['ticker_id']
            ])->id;
        } else {
            $id = $asset->first()->id;
        }
        Share::create([
            'asset_id' => $id,
            'trade' => 'buy',
            'quantity' => $request['quantity'],
            'price_per_share' => $request['price'],
            'total_price' => $request['quantity'] * $request['price']
        ]);
        $this->update($id);
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
        //
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
        $shares = Share::where('asset_id', $id)->get();
        $quantity = 0;
        $invested = 0;
        foreach ($shares as $share) {
            $quantity += $share->quantity;
            $invested += $share->total_price;
        }
        $asset = Asset::where('id', $id);
        $tickerPrice = $this->getTickerPrice($id);
        $asset->update([
           'quantity' => $quantity,
           'invested' => $invested,
           'average_price' => $invested / $quantity,
           'position_worth' => $tickerPrice * $quantity,
           'profit' => ($tickerPrice * $quantity) - $invested,
           'roi' => (($tickerPrice * $quantity) - $invested) / $invested * 100
        ]);
    }

    public function getTickerPrice($id) {
        $ticker = Asset::where('id', $id)->with(['ticker' => function ($query) {
                $query->select('id', 'ticker');
            }])
            ->first()->ticker;
        $response = Http::get('https://api.polygon.io/v2/aggs/ticker/'.$ticker->ticker.'/range/1/day/2022-12-21/2022-12-21?adjusted=true&sort=asc&limit=1&apiKey=QAeE2PfbtZ4SwEZLSUUJc5JxHSEogotK');
        if ($response['status'] == 'OK') {
            $closedPrice = $response['results'][0]['c'];
            Ticker::where('id', $ticker->id)->update(['price_per_share' => $closedPrice]);
            return $closedPrice;
        }
    }

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