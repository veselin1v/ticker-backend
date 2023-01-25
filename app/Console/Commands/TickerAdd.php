<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Ticker;
use Illuminate\Support\Facades\Artisan;

class TickerAdd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ticker:add {cursor?} {count?}';

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
            'cursor' => $this->argument('cursor') ?? null,
            'apiKey' => config('polygon.api_key')
        ]);

        $count = $this->argument('count') ?? 0;

        if ($response['status'] == 'OK') {
            foreach ($response['results'] as $ticker) {
                Ticker::updateOrCreate([
                    'ticker' => $ticker['ticker'],
                    'name'  => $ticker['name']
                ]);
            }
            if ($response['next_url']) {
                $url = parse_url($response['next_url']);
                if ($url['query']) {
                    $cursor = explode('=', $url['query']);
                    $count++;
                    if ($count == 5) {
                        $count = 0;
                        sleep(60);
                    }
                    Artisan::call('ticker:add', ['cursor' => $cursor[1], 'count' => $count]);
                }
            }
        }
    }
}
