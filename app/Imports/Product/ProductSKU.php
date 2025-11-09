<?php

namespace App\Imports\Product;

use App\Jobs\ProductBulkTempImport;
use App\Jobs\ProductTempImport;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\ComponentConcerns\ReceivesEvents;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;

class ProductSKU implements ToCollection, WithEvents
{
    public function startRow(): int
    {
        return 2;
    }
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {

        $datas = $collection->skip(1)->take(30);
        $user = Auth::guard('web')->user();
        $no = 1;
        $datas->map(function ($data) use ($user, $no) {
            $data['user_id'] = $user->id;
            ProductTempImport::dispatch($data)->onQueue('queue-log');
            $no++;
        });
        $success_total = getSetting('product_import_success_' . $user->id);
        $nextData = $collection->skip(31);
        ProductBulkTempImport::dispatch($nextData, $user, $success_total)->onQueue('queue-log');
    }

    public function registerEvents(): array
    {
        return [
            BeforeImport::class => function (BeforeImport $event) {
                // remove empty row
                // $event->reader->setReadFilter(new MyReadFilter());

                $totalRows = $event->getReader()->getTotalRows();
                $user = Auth::guard('web')->user();
                if (!empty($totalRows)) {
                    // echo $totalRows['Worksheet'];
                    setSetting('product_import_count_' . $user->id, max($totalRows) - 1);
                } else {
                    setSetting('product_import_count_' . $user->id, max($totalRows) - 1);
                }
                // setSetting('product_import_count_' . auth()->user()->id, $collection->count());
            }
        ];
    }
}
