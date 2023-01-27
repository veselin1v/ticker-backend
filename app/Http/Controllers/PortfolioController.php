<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Portfolio;
use App\Models\Asset;
use App\Models\Ticker;

class PortfolioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $portfolio = Portfolio::where('user_id', Auth::user()->id)
        ->with('assets.trades')
        ->with('assets.ticker')
        ->first();
        return response()->json($portfolio);
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
        
        Portfolio::create([
            'user_id' => Auth::user()->id,
            'name' => $request['name']
        ]);

        return response()->json(['message' => 'The porfolio has been created successfully!']);
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
        $assets = Asset::where('portfolio_id', $id)->get();
        $equity = 0;
        $invested = 0;
        if (count($assets)) {
            foreach ($assets as $asset) {
                $tickerPrice = Ticker::where('id', $asset->ticker_id)->first()->price_per_share;
                Asset::where('id', $asset->id)->update([
                    'position_worth' => $tickerPrice * $asset->quantity,
                    'profit' => ($tickerPrice * $asset->quantity) - $asset->invested,
                    'roi' => (($tickerPrice * $asset->quantity) - $asset->invested) / $asset->invested * 100
                ]);
                $equity += $asset->position_worth;
                $invested += $asset->invested;
            }
    
            $profit = $equity - $invested;
            $roi = ($equity - $invested) / $invested * 100;
    
            Portfolio::where('id', $id)->update([
                'equity' => $equity,
                'invested' => $invested,
                'profit' => $profit,
                'roi' => $roi
            ]);
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
