<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Asset;

class CheckQuantity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request && $request['trade'] == 'sell') {
            $quantity = Asset::where('id', $request['asset_id'])->first()->quantity;
            if ($request['quantity'] > $quantity) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The maximum selling quantity for this asset is ' . $quantity . '.']
                );
            } else {
                return $next($request);   
            }
        }
    }
}
