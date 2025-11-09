<?php

namespace App\Exports;

use App\Models\LeadMaster;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LeadSkuExport implements FromView, ShouldAutoSize
{
    protected $items;
    protected $title;
    public function __construct($items = null, $title = 'ExportConverData')
    {
        $this->items = $items;
        $this->title = $title;
    }



    public function view(): View
    {
        $leads = LeadMaster::whereHas('productNeeds');
        if (is_array($this->items)) {
            $leads->whereIn('uid_lead', $this->items);
        }
        $new_leads = $leads->get();
        $lead_data = [];

        foreach ($new_leads as $key => $value) {
            // merge value same value
            $lead_data[$key]['title'] = $value->title;
            $lead_data[$key]['contact_name'] = $value->contact_name;
            $lead_data[$key]['sales_name'] = $value->sales_name;
            $lead_data[$key]['created_by_name'] = $value->created_by_name;
            $lead_data[$key]['brand_name'] = $value->brand_name;
            $lead_data[$key]['created_at'] = $value->created_at;
            $lead_data[$key]['product_needs'] = $value->productNeeds()->get()->map(function ($item) {
                return [
                    'product_name' => $item->product_name,
                    'price' => $item->prices['final_price'],
                    'qty' => $item->qty,
                    'tax_amount' => $item->tax_amount,
                    'discount_amount' => $item->discount_amount,
                    'subtotal' => $item->subtotal,
                    'price_nego' => $item->price_nego,
                    'total_price' => $item->total,
                ];
            });
        }
        return view('export.lead-master', [
            'data' => $lead_data,
        ]);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
}
