<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Ticker;

class TickerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticker:add';

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
        $response = Http::get(config('polygon.api') . '/v3/reference/tickers', [
            'active' => 'true',
            'limit' => 1000,
            'market' => 'stocks',
            'apiKey' => config('polygon.api_key')
        ]);
        if ($response['status'] == 'OK') {
            foreach ($response['results'] as $ticker) {
                Ticker::create([
                    'ticker' => $ticker['ticker'],
                    'name'  => $ticker['name']
                ]);
            }
        }
    }
}
