<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Ticker;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;

class TickerPrice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticker:price {days?}';

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
        $days = $this->argument('days') ?? 0;
        $date = Carbon::today()->subDays($days)->toDateString();
        $response = json_decode(Http::get(config('polygon.api') . '/v2/aggs/grouped/locale/us/market/stocks/' . $date, [
            'apiKey' => config('polygon.api_key')
        ]));
        if ($response->status == 'OK' && isset($response->results)) {
            $days = 0;
            foreach ($response->results as $ticker) {
                $tick = Ticker::where('ticker', $ticker->T);
                if ($tick->exists()) {
                    $tick->update(['price_per_share' => $ticker->c]);
                }
            }
        } else {
            $days++;
            Artisan::call('ticker:price', ['days' => $days]);
        }
    }
}
