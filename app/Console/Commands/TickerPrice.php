<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Ticker;
use Illuminate\Support\Carbon;

class TickerPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticker:price';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $yesterday = Carbon::yesterday()->toDateString();
        $response = json_decode(Http::get(config('polygon.api') . '/v2/aggs/grouped/locale/us/market/stocks/' . $yesterday, [
            'apiKey' => config('polygon.api_key')
        ]));
        if ($response->status == 'OK') {
            foreach ($response->results as $ticker) {
                $tick = Ticker::where('ticker', $ticker->T);
                if ($tick->exists()) {
                    $tick->update(['price_per_share' => $ticker->c]);
                }
            }
        }
    }
}
