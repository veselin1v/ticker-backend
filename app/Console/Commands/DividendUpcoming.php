<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Asset;
use App\Jobs\DividendUpcomingJob;

class DividendUpcoming extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dividend:upcoming';

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
        $assets = Asset::groupBy('ticker_id')->with('ticker')->get();
        if (count($assets)) {
            foreach ($assets as $asset) {
                DividendUpcomingJob::dispatch($asset);
            }
        }
    }
}
