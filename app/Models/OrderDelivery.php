<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderDelivery extends Model
{
    use HasFactory;

    protected $fillable = [
        'uid_lead',
        'uid_delivery',
        'uid_invoice',
        'product_need_id',
        'user_id',
        'qty_delivered',
        'resi',
        'courier',
        'sender_name',
        'sender_phone',
        'delivery_date',
        'attachments',
        'status',
        'gp_submit_number',
        'type_so',
        'invoice_number',
        'delivery_number',
        'due_date',
        'invoice_date',
        'no_faktur',
        'submit_klikpajak',
        'is_invoice'
    ];

    protected $appends = ['product_name', 'sku', 'created_by_name', 'print_sj_url', 'courier_name', 'price', 'subtotal_invoice', 'tax_amount', 'price_product', 'status_invoice', 'tax_invoiced', 'discount_amount', 'total', 'sku', 'u_of_m'];

    protected $dates = [];


    public static function generateInvoiceNumber($type = 'order-manual', $number = 1)
    {
        $type_so = $type == 'order-manual' ? 2 : 3;
        $format_so = $type == 'order-lead' ? 1 : $type_so;
        $datePrefix = 'SI/' . date('Y') . '/' . $format_so . str_pad($number, 8, '0', STR_PAD_LEFT);  // Format: SO/2024/2304


        return $datePrefix;  // Gabungkan semua bagian
    }

    public static function generateDeliveryNumberNumber()
    {
        $today = now()->format('d/Y'); // Ambil tanggal hari ini
        $latestInvoice = self::whereNotNull('delivery_number')->orderBy('created_at', 'desc')->first();

        if (!$latestInvoice) {
            $number = 1;
        } else {
            // Parsing nomor terakhir
            $latestNumber = explode('/', $latestInvoice->delivery_number);
            if (count($latestNumber) > 3) {
                $number = intval($latestNumber[3]) + 1;
            } else {
                $number = intval($latestNumber[2]) + 1;
            }
        }

        // Format nomor dengan panjang 4 digit dan tambahkan 'INV/tgl/auto'
        $formattedNumber = str_pad($number, 4, '0', STR_PAD_LEFT);
        $invoiceNumber = 'DO/' . $today . '/' . $formattedNumber;

        return $invoiceNumber;
    }

    /**
     * Get the productNeed that owns the OrderDelivery
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productNeed()
    {
        return $this->belongsTo(ProductNeed::class);
    }

    /**
     * Get the product that owns the OrderDelivery
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cretedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the order that owns the OrderDelivery
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderManual()
    {
        return $this->belongsTo(OrderManual::class, 'uid_lead', 'uid_lead');
    }

    /**
     * Get the order that owns the OrderDelivery
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderLead()
    {
        return $this->belongsTo(OrderLead::class, 'uid_lead', 'uid_lead');
    }

    public function getProductNameAttribute()
    {
        $productNeed = ProductNeed::find($this->product_need_id, ['product_id']);
        return $productNeed ? $productNeed->product_name : '-';
    }

    public function getSkuAttribute()
    {
        $productNeed = ProductNeed::find($this->product_need_id, ['product_id']);
        return $productNeed ? $productNeed?->product?->sku : '-';
    }

    public function getUOfMAttribute()
    {
        $productNeed = ProductNeed::find($this->product_need_id, ['product_id']);
        return $productNeed ? $productNeed?->product?->u_of_m : '-';
    }

    public function getCourierNameAttribute()
    {
        $warehouse = Warehouse::find($this->courier, ['name']);
        return $warehouse ? $warehouse?->name : '-';
    }

    public function getCreatedByNameAttribute()
    {
        $user = User::find($this->user_id, ['name']);
        return $user ? $user->name : '-';
    }

    public function getPrintSjUrlAttribute()
    {
        return route('print.sj', ['uid_lead' => $this->uid_lead, 'delivery_id' => $this->uid_delivery]);
    }

    public function getPriceAttribute()
    {
        $productNeed = ProductNeed::find($this->product_need_id, ['product_id']);
        return $productNeed->price_product;
    }

    public function getPriceProductAttribute()
    {
        $productNeed = ProductNeed::find($this->product_need_id);
        $price_nego = $productNeed->price_nego > 0 ? $productNeed->price_nego : 0;
        $price = $price_nego > 0 ? $price_nego / $productNeed->qty : 0;
        return floor($price);
    }

    public function getSubtotalInvoiceAttribute()
    {
        return $this->price_product * $this->qty_delivered;
    }

    public function getTaxInvoicedAttribute()
    {
        $productNeed = ProductNeed::find($this->product_need_id);
        $tax_amount = $productNeed->tax_amount > 0 ? $productNeed->tax_amount : 0;
        $tax = $tax_amount > 0 ? $tax_amount / $productNeed->qty : 0;
        return floor($tax * $this->qty_delivered);
    }

    public function getTaxAmountAttribute()
    {
        $productNeed = ProductNeed::find($this->product_need_id, ['tax_id']);
        $subtotal = $this->subtotal_invoice - $this->discount_amount;
        if ($productNeed) {
            if ($productNeed->ppn > 0) {
                $tax = $productNeed->ppn / 100;
                return $subtotal * $tax;
            }
        }
    }

    public function getDiscountAmountAttribute()
    {
        $productNeed = ProductNeed::find($this->product_need_id);
        $discount_amount = $productNeed->discount_amount > 0 ? $productNeed->discount_amount : 0;
        $tax = $discount_amount > 0 ? $discount_amount / $productNeed->qty : 0;
        return floor($tax * $this->qty_delivered);
    }

    public function getTotalAttribute()
    {
        return ($this->subtotal_invoice - $this->discount_amount) + $this->tax_amount;
    }

    public function getStatusInvoiceAttribute()
    {
        $billings = DB::table('lead_billings')->where('uid_lead', $this->uid_lead)->select('status')->get();
        $total_approved = 0;
        foreach ($billings as $key => $value) {
            if ($value->status == 1) {
                $total_approved += 1;
            }
        }

        if (count($billings) < 1) {
            return 'Belum Bayar';
        }

        if ($total_approved > 0) {
            return 'Pembayaran Sudah Diterima';
        }

        if ($total_approved == 0) {
            return 'Menunggu Approval';
        }

        return 'Complete';
    }
}
