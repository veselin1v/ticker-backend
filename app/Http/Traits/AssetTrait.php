<?php
namespace App\Http\Traits;

use App\Models\Asset;
use App\Models\Trade;
use App\Models\Ticker;

trait AssetTrait 
{
    public function updateAsset($id) {
        $trades = Trade::where('asset_id', $id)->get();
        $asset = Asset::where('id', $id)->first();
        $lastBuyTrade = Trade::where('asset_id', $id)->where('trade', 'buy')->latest('created_at')->first();
        $quantity = 0;
        $invested = 0;
        $average_price = 0;
        foreach ($trades as $trade) {
            if ($trade->trade == 'buy') {
                $quantity += $trade->quantity;
                $invested += $trade->total_price;
                $average_price += $invested / $quantity;
            } else {
                $quantity -= $trade->quantity;
            }
        }
        $tickerPrice = Ticker::where('id', $asset->ticker_id)->first()->price_per_share;
        if ($quantity > 0) {
            $asset->update([
                'quantity' => $quantity,
                'invested' => $invested,
                'average_price' => $average_price,
                'position_worth' => $tickerPrice * $quantity,
                'profit' => ($tickerPrice * $quantity) - $invested,
                'roi' => (($tickerPrice * $quantity) - $invested) / $invested * 100
             ]);
        } else {
            $lastTrade = Trade::where('asset_id', $id)->where('trade', 'sell')->latest('created_at')->first();
            $asset->update([
                'quantity' => 0,
                'invested' => 0,
                'position_worth' => 0,
                'profit' => $lastTrade->total_price - $invested,
                'roi' => ($lastTrade->total_price - $invested) / $invested * 100
             ]);
        }
    }
}