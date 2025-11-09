<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreatePopaketOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'popaket:order';

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
        setSetting('CreatePopaketOrder', 'CreatePopaketOrder');
        return Command::SUCCESS;
    }
}
