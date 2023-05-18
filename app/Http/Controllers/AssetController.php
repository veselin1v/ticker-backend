<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\Trade;
use App\Models\Ticker;
use App\Models\Dividend;
use App\Models\Portfolio;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use App\Http\Traits\AssetTrait;
use Auth;

class AssetController extends Controller
{
    use AssetTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $portfolioId = Portfolio::where('user_id', Auth::user()->id)->first()->id;

        if ($portfolioId) {
            $assets = Asset::where('portfolio_id', $portfolioId)->with('trades')->with('ticker')->get();
            if ($assets) {
                return response()->json($assets);
            }
        }

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
        if ($response['status'] == 'OK' && $response['results']) {
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
        $asset->ticker = Ticker::where('id', $asset->ticker_id)->select(['ticker', 'dividend_yield'])->first();
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
        $this->updateAsset($id);
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
