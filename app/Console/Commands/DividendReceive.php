<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Asset;
use App\Models\Dividend;
use App\Models\AssetsDividend;
use Illuminate\Support\Carbon;

class DividendReceive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dividend:receive';

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
        $assets = Asset::get();
        if (!empty($assets)) {
            foreach ($assets as $asset) {
                $dividend = Dividend::where('ticker_id', $asset->ticker_id)->first();
                if (!empty($dividend)) {
                    if ($dividend->ex_dividend_date == Carbon::today()->format('Y-m-d')) {
                        AssetsDividend::create([
                            'asset_id' => $asset->id,
                            'dividend_id' => $dividend->id,
                            'received_at' => $dividend->ex_dividend_date,
                            'amount' => $asset->quantity * $dividend->cash_amount
                        ]);
                    }
                }
            }
        }
    }
}
