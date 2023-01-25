<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Dividend;

class DividendUpcomingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $asset;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($asset)
    {
        $this->asset = $asset;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        sleep(15);
        $response = Http::get(config('polygon.api') . '/v3/reference/dividends', [
            'ticker' => $this->asset->ticker->ticker,
            'limit' => 1,
            'apiKey' => config('polygon.api_key')
        ]);
        if ($response['status'] == 'OK') {
            if ($response['results']) {
                $dividend = $response['results'][0];
                if (Dividend::where('ticker_id', $this->asset->ticker_id)
                ->where('ex_dividend_date', $dividend['ex_dividend_date'])
                ->doesntExist()) {
                    Dividend::create([
                        'ticker_id' => $this->asset->ticker_id,
                        'ex_dividend_date' => $dividend['ex_dividend_date'],
                        'pay_date' => $dividend['pay_date'],
                        'cash_amount' => $dividend['cash_amount']
                    ]);
                }
            }
        }
    }
}
