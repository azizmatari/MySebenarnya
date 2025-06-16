<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ResetAgencyTypes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'agency:reset-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset agency types to default values by removing custom types file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $typesFile = storage_path('app/agency_types.json');

        if (File::exists($typesFile)) {
            File::delete($typesFile);
            $this->info('Agency types have been reset to default values');
        } else {
            $this->info('Agency types are already at default values');
        }

        return Command::SUCCESS;
    }
}
