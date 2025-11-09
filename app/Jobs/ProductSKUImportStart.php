<?php

namespace App\Jobs;

use App\Imports\Product\ProductSKU;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class ProductSKUImportStart implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $file_path;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($file_path)
    {
        $this->file_path = $file_path;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $path = $this->file_path;
        Excel::import(new ProductSKU, $path);
    }
}
