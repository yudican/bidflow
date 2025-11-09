<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductNeed extends Model
{
    use HasFactory;
    protected $fillable = [
        'uid_lead',
        'product_id',
        'price',
        'qty',
        'status',
        'discount',
        'tax_id',
        'discount_id',
        'description',
        'user_created',
        'user_updated',
        'price_type',
        'qty_delivery',
        'copy_print',
        'qty_dibayar',
        'is_invoice',
        'invoice_code',
        'gp_submit_number',
        'due_date',
        'invoice_number',
        'delivery_number',
        'invoice_date',
        'submit_klikpajak',
        'no_faktur'
    ];

    protected $appends = ['contact_name', 'sales_name', 'payment_term', 'created_by_name', 'status_name', 'tax_amount', 'amount_invoiced', 'discount_amount', 'total', 'total_invoice', 'margin_price', 'price_product', 'subtotal', 'subtotal_invoice', 'tax_invoice', 'discount_percent', 'tax_percentage',  'ppn', 'role', 'prices', 'price_nego', 'u_of_m', 'product_name', 'final_price', 'discount_percentage', 'disabled_discount', 'disabled_price_nego', 'print_si_url', 'type_so', 'uid_delivery', 'stock_bins'];

    public static function generateInvoiceNumber()
    {
        $today = now()->format('d'); // Ambil tanggal hari ini
        $latestInvoice = self::whereNotNull('invoice_number')->orderBy('created_at', 'desc')->first();

        if (!$latestInvoice) {
            $number = 1;
        } else {
            // Parsing nomor terakhir
            $latestNumber = explode('/', $latestInvoice->invoice_number)[2];
            $number = intval($latestNumber) + 1;
        }

        // Format nomor dengan panjang 4 digit dan tambahkan 'INV/tgl/auto'
        $formattedNumber = str_pad($number, 4, '0', STR_PAD_LEFT);
        $invoiceNumber = 'INV/' . $today . '/' . $formattedNumber;

        return $invoiceNumber;
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

    // protected  $hide = ['status'];
    /**
     * Get the product that owns the ProductNeed
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(ProductVariant::class, 'product_id')->select(['id', 'name', 'product_id', 'sku']);
    }

    // lead master
    /**
     * Get the leadMaster that owns the ProductNeed
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function leadMaster()
    {
        return $this->belongsTo(LeadMaster::class, 'uid_lead', 'uid_lead');
    }

    /**
     * Get the orderLead that owns the ProductNeed
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderLead()
    {
        return $this->belongsTo(OrderLead::class, 'uid_lead', 'uid_lead');
    }

    public function orderManual()
    {
        return $this->belongsTo(OrderManual::class, 'uid_lead', 'uid_lead');
    }

    // public function discount()
    // {
    //     return $this->belongsTo(MasterDiscount::class, 'discount_id');
    // }

    public function tax()
    {
        return $this->belongsTo(MasterTax::class, 'tax_id');
    }

    public function getProductNameAttribute()
    {
        $product = ProductVariant::find($this->product_id, ['name']);
        if ($product) {
            return $product->name;
        }
        return '-';
    }

    public function getTypeSoAttribute()
    {
        $orderDelivery = OrderDelivery::where('product_need_id', $this->id)->first(['type_so']);
        if ($orderDelivery) {
            return $orderDelivery->type_so;
        }
        return '-';
    }
    public function getUidDeliveryAttribute()
    {
        $orderDelivery = OrderDelivery::where('product_need_id', $this->id)->first(['uid_delivery']);
        if ($orderDelivery) {
            return $orderDelivery->uid_delivery;
        }
        return '-';
    }

    /**
     * Get all of the masterBinStocks for the OrderManual
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function masterBinStocks()
    {
        return $this->hasMany(MasterBinStock::class, 'product_variant_id', 'product_id');
    }

    public function getRoleAttribute()
    {
        $order = OrderLead::where('uid_lead', $this->uid_lead)->first();
        if (!$order) {
            $order = OrderManual::where('uid_lead', $this->uid_lead)->first();
        }

        if (!$order) {
            $order = LeadMaster::where('uid_lead', $this->uid_lead)->first();
        }

        if ($order?->contact) {
            $user = User::find($order->contact);
            if ($user && $user->role) {
                return $user->role->role_type;
            }
        }

        return 'agent';
    }

    public function getPriceProductAttribute()
    {
        if ($this->price_type == 'manual') {
            return $this->price;
        }


        if ($this->product) {
            $price = $this->product->getPrice($this->role)['final_price'];
            // if ($this->price > 0) {
            //     $price = $this->price;
            // }
            return $price;
        }
        return 0;
    }

    public function getPricesAttribute()
    {
        if ($this->price_type == 'manual') {
            return [
                'basic_price' => $this->price,
                'final_price' => $this->price,
            ];
        }
        if ($this->product) {
            $price = $this->product->getPrice($this->role);
            return $price;
        }
        return [
            'basic_price' => 0,
            'final_price' => 0,
        ];
    }

    public function getDiscountAmountAttribute()
    {
        $curr_price = $this->price_nego;
        if ($this->discount > 0) {
            return $this->discount * $this->qty;
        }

        if ($this->discount_id) {
            $discount = MasterDiscount::find($this->discount_id, ['percentage']);
            if ($discount->percentage > 0) {
                $discount = $discount->percentage / 100;
                $subtotal = $curr_price;
                return floor($subtotal * $discount);
            }
        }

        return 0;
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->discount_amount > 0 && $this->price_nego > 0) {
            $discountPercent = ($this->discount_amount / $this->price_nego) * 100;
            // Format the result with two decimal places
            $discountPercentFormatted = number_format($discountPercent, 2);
            return "$discountPercentFormatted%";
        }
        return 0;
    }

    public function getTaxAmountAttribute()
    {
        $curr_price =  $this->price_nego - $this->discount_amount;
        if ($this->tax) {
            if ($this->tax->tax_percentage > 0) {
                $tax = $this->tax->tax_percentage / 100;
                return floor($curr_price * $tax);
            }
        }

        return 0;
    }
    public function getTaxInvoiceAttribute()
    {
        if ($this->tax) {
            if ($this->tax->tax_percentage > 0) {
                // if ($this->discount_amount > 0) {
                //     $tax = $this->tax->tax_percentage / 100;
                //     $price = ($curr_price * $qty) - $this->discount_amount;
                //     return floor($price * $tax);
                // } else {
                //     $tax = $this->tax->tax_percentage / 100;
                //     $curr_price = $this->price > 0 ? $this->price / $this->qty : 0;
                //     $price = $curr_price * $this->qty_delivery;
                //     return floor($price * $tax);
                // }
                $tax = $this->tax->tax_percentage / 100;
                $curr_price = $this->price_nego > 0 ? $this->price_nego / $this->qty : 0;
                $price = $curr_price * $this->qty_delivery;
                return floor($price * $tax);
            }
        }

        return 0;
    }

    public function getTotalAttribute()
    {
        // if ($this->discount) {
        //     $curr_price = $this->price_product;
        //     $price = $curr_price * $this->qty;
        //     return floor(($price - $this->discount_amount) + $this->tax_amount);
        // }

        $curr_price = $this->price;
        $price = $curr_price;
        return floor(($price - $this->discount_amount) + $this->tax_amount);
    }

    public function getTotalInvoiceAttribute()
    {
        // if ($this->discount) {
        //     $curr_price = $this->price_product;
        //     $price = $curr_price * $this->qty_delivery;
        //     return floor(($price - $this->discount_amount) + $this->tax_amount);
        // }

        $curr_price = $this->price > 0 ? $this->price / $this->qty : 0;
        $price = $curr_price * $this->qty_delivery;
        return floor(($price - $this->discount_amount) + $this->tax_amount);
    }

    public function getPriceNegoAttribute()
    {
        // if ($this->discount_amount > 0) {
        //     if ($this->product) {
        //         $price = $this->price_product * $this->qty;
        //         $final_price  = $price + $this->tax_amount - $this->discount_amount;
        //         return floor($final_price);
        //     }
        // }

        return $this->price ?? 0;
    }

    public function getFinalPriceAttribute()
    {
        // if ($this->discount_amount > 0) {
        //     if ($this->product) {
        //         $price = $this->price_product * $this->qty;
        //         $final_price  = $price + $this->tax_amount - $this->discount_amount;
        //         return floor($final_price);
        //     }
        // }

        // if ($this->price > 0) {
        //     return $this->price + $this->tax_amount;
        // }

        return $this->price_product * $this->qty;
    }

    public function getSubtotalAttribute()
    {
        // if ($this->discount > 0) {
        //     $curr_price = $this->price_product;
        //     return $curr_price * $this->qty;
        // }

        $curr_price = $this->price;
        return $curr_price;
    }

    public function getSubtotalInvoiceAttribute()
    {
        // if ($this->discount) {
        //     $curr_price = $this->price_product;
        //     return $curr_price * $this->qty_delivery;
        // }

        $curr_price = $this->price > 0 ? $this->price / $this->qty : 0;
        $price = $curr_price * $this->qty_delivery;
        return $price;
    }

    public function getMarginPriceAttribute()
    {
        $price = $this->product?->margin_price;

        return $price * $this->qty;
    }

    public function getDiscountPercentAttribute()
    {
        if ($this->discount_id) {
            $discount = MasterDiscount::find($this->discount_id, ['percentage']);
            return floor($discount->percentage / 100);
        }

        return 0;
    }

    public function getTaxPercentageAttribute()
    {
        if ($this->tax) {
            return $this->tax->tax_percentage / 100;
        }

        return 0;
    }

    public function getPpnAttribute()
    {
        if ($this->tax) {
            return $this->tax->tax_percentage;
        }

        return 0;
    }

    public function getUOfMAttribute()
    {
        $sku = SkuMaster::where('sku', $this->product?->sku)->where('status', 1)->first(['id', 'package_id']);

        return $sku?->package_name ?? '-';
    }

    public function getDisabledDiscountAttribute()
    {
        if ($this->price > 0) {
            return true;
        }

        return false;
    }

    public function getDisabledPriceNegoAttribute()
    {
        if ($this->discount_amount > 0) {
            return true;
        }

        return false;
    }

    public function getPrintSiUrlAttribute()
    {
        return route('print.si', ['uid_retur' => $this->uid_lead, 'product_need_ids' => $this->id]);
    }

    public function orderInfo($field)
    {
        if ($this->type_so == 'order-lead') {
            $orderLead = OrderLead::where('uid_lead', $this->uid_lead)->first(['contact', 'sales', 'user_created', 'payment_term', 'status']);
            if ($orderLead) {
                return $orderLead[$field];
            }

            return '-';
        }

        $orderManual = OrderManual::where('uid_lead', $this->uid_lead)->first(['contact', 'sales', 'user_created', 'payment_term', 'status']);
        if ($orderManual) {
            return $orderManual[$field];
        }

        return '-';
    }

    public function getContactNameAttribute()
    {
        return $this->orderInfo('contact_name');
    }

    public function getSalesNameAttribute()
    {
        return $this->orderInfo('sales_name');
    }

    public function getPaymentTermAttribute()
    {
        return $this->orderInfo('payment_term_name');
    }

    public function getCreatedByNameAttribute()
    {
        return $this->orderInfo('created_by_name');
    }

    public function getStatusNameAttribute()
    {
        return $this->orderInfo('status');
    }

    public function getAmountInvoicedAttribute()
    {
        return $this->orderInfo('amount_invoiced');
    }

    public function getStockBinsAttribute()
    {
        try {
            $order = OrderManual::where('uid_lead', $this->uid_lead)->first();
            $bin_list = MasterBinStock::where('master_bin_id', $order->master_bin_id)->where('product_variant_id', $this->product_id)->get();
            $stock = 0;
            foreach ($bin_list as $bin) {
                $stock += $bin->stock;
            }
            return $stock;
        } catch (\Exception $e) {
            return 0;
        }
    }
}
