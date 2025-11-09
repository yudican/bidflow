<?php

namespace App\Models;

use App\Traits\Uuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderManual extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['uid_lead', 'title', 'contact', 'sales', 'customer_need', 'user_created', 'user_updated', 'payment_term', 'brand_id', 'status', 'type_customer', 'warehouse_id', 'order_number', 'invoice_number', 'transfer_number', 'reference_number', 'shipping_type', 'address_id', 'notes', 'courier', 'status_penagihan', 'status_pengiriman', 'status_invoice', 'due_date', 'kode_unik', 'temp_kode_unik', 'ongkir', 'company_id', 'type', 'status_submit', 'attachment', 'print_status', 'resi_status', 'master_bin_id', 'gp_si_number', 'preference_number', 'status_ethix_submit', 'delivery_date', 'invoice_date', 'id_konsinyasi', 'expired_at', 'parent_id', 'subtotal', 'diskon', 'dpp', 'ppn', 'total', 'tax_percentage', 'shipping_method_id', 'import_log_id', 'input_type', 'order_type'];

    protected $dates = [];
    protected $appends = ['amount', 'amount_ppn', 'amount_ppn_invoiced', 'amount_invoiced', 'subtotal', 'subtotal_invoiced', 'tax_amount', 'tax_amount_invoiced', 'discount_amount', 'discount_amount_invoiced', 'contact_name', 'contact_name_only', 'sales_name', 'payment_term_name', 'created_by_name', 'courier_name', 'selected_address', 'amount_billing_approved', 'amount_deposite', 'company_name', 'contact_uid', 'attachment_url', 'warehouse_name', 'bin_name', 'total_qty', 'total_qty_delivery', 'total_qty_payment', 'ethix_items', 'payment_due_date', 'gp_numbers', 'contact_role'];


    public static function generateOrderNumber($manual = 2, $number = 1)
    {
        $datePrefix = 'SO/' . date('Y') . '/' . $manual . date('mdH');  // Format: SO/2024/2304
        $lastOrder = self::where('order_number', 'like', $datePrefix . '%')->latest()->first();
        $lastNumber = 1;  // Reset number setiap hari

        if ($lastOrder) {
            $lastNumber = intval(substr($lastOrder->order_number, -4)) + $number;  // Ambil 5 digit terakhir dan tambah 1
        }

        $formattedNumber = str_pad($lastNumber, 4, '0', STR_PAD_LEFT);  // Format number menjadi 5 digit

        return $datePrefix . $formattedNumber;  // Gabungkan semua bagian
    }

    public static function generateInvoiceNumber($manual = 2, $number = 1)
    {
        $datePrefix = 'SI/' . date('Y') . '/' . $manual . date('mdH');  // Format: SO/2024/2304
        $lastOrder = self::where('invoice_number', 'like', $datePrefix . '%')->latest()->first();
        $lastNumber = 1;  // Reset number setiap hari

        if ($lastOrder) {
            $lastNumber = intval(substr($lastOrder->invoice_number, -4)) + $number;  // Ambil 5 digit terakhir dan tambah 1
        }

        $formattedNumber = str_pad($lastNumber, 4, '0', STR_PAD_LEFT);  // Format number menjadi 5 digit

        return $datePrefix . $formattedNumber;  // Gabungkan semua bagian
    }
    /**
     * Get the contact that owns the LeadMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contactUser()
    {
        return $this->belongsTo(User::class, 'contact')->select(['id', 'name', 'email', 'telepon']);
    }

    public function getContactUidAttribute()
    {
        $user = User::find($this->contact, ['uid']);
        return $user ? $user->uid : null;
    }

    /**
     * Get the sales that owns the LeadMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function salesUser()
    {
        return $this->belongsTo(User::class, 'sales')->select(['id', 'name', 'email', 'telepon']);
    }

    /**
     * Get the sales that owns the LeadMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function addressUser()
    {
        return $this->belongsTo(AddressUser::class, 'address_id');
    }

    /**
     * Get the sales that owns the LeadMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createUser()
    {
        return $this->belongsTo(User::class, 'user_created')->select(['id', 'name', 'telepon']);
    }
    /**
     * Get the sales that owns the LeadMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function courierUser()
    {
        return $this->belongsTo(User::class, 'courier')->select(['id', 'name', 'telepon']);
    }

    /**
     * Get the brand that owns the LeadMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class)->select(['id', 'name', 'provinsi_id', 'kabupaten_id', 'kecamatan_id', 'kelurahan_id', 'kodepos', 'address']);
    }

    /**
     * Get all of the logPrintOrders for the OrderLead
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logPrintOrders()
    {
        return $this->hasMany(LogPrintOrder::class, 'uid_lead', 'uid_lead');
    }

    /**
     * Get all of the leadActivities for the LeadMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function leadActivities()
    {
        return $this->hasMany(LeadActivity::class, 'uid_lead', 'uid_lead');
    }

    /**
     * Get all of the reminders for the LeadMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reminders()
    {
        return $this->hasMany(LeadReminder::class, 'uid_lead', 'uid_lead');
    }

    /**
     * Get all of the leadActivities for the LeadMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'contact');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_term');
    }

    public function negotiations()
    {
        return $this->hasMany(LeadNegotiation::class, 'uid_lead', 'uid_lead');
    }

    /**
     * Get all of the billings for the OrderLead
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function billings()
    {
        return $this->hasMany(LeadBilling::class, 'uid_lead', 'uid_lead');
    }

    /**
     * Get all of the productNeeds for the LeadMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productNeeds()
    {
        return $this->hasMany(ProductNeed::class, 'uid_lead', 'uid_lead');
    }

    /**
     * Get all of the orderDelivery for the OrderLead
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderDelivery()
    {
        return $this->hasMany(OrderDelivery::class, 'uid_lead', 'uid_lead');
    }

    /**
     * Get all of the orderDeposites for the OrderLead
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderDeposites()
    {
        return $this->hasMany(OrderDeposit::class, 'uid_lead', 'uid_lead')->where('order_type', 'manual')->where('contact', $this->contact);
    }

    public function getAmountDepositeAttribute()
    {
        $total = 0;
        foreach ($this->orderDeposites as $key => $value) {
            $total += $value->amount;
        }
        return $total;
    }

    public function getAmountAttribute()
    {
        $total = 0;
        foreach ($this->productNeeds as $key => $value) {
            $total += $value->subtotal - $value->discount_amount;
        }
        return $total;
        // return $total;
    }
    public function getAmountInvoicedAttribute()
    {
        $total = 0;
        foreach ($this->orderDelivery as $key => $value) {
            if ($value->is_invoice == 1) {
                $total += $value->total;
            }
        }
        return $total;
        // return $total;
    }

    public function getTaxAmountAttribute()
    {
        $tax = 0;
        foreach ($this->productNeeds as $key => $value) {
            $tax += $value->tax_amount;
        }
        return $tax;
    }

    public function getTaxAmountInvoicedAttribute()
    {
        $tax = 0;
        foreach ($this->orderDelivery as $key => $value) {
            if ($value->is_invoice == 1) {
                $tax += $value->tax_amount;
            }
        }
        return $tax;
    }

    public function getDiscountAmountAttribute()
    {
        $discount = 0;
        foreach ($this->productNeeds as $key => $value) {
            $discount += $value->discount_amount;
        }
        return $discount;
    }


    public function getDiscountAmountInvoicedAttribute()
    {
        $discount = 0;
        foreach ($this->orderDelivery as $key => $value) {
            if ($value->is_invoice == 1) {
                $discount += $value->discount_amount;
            }
        }
        return $discount;
    }

    public function getTotalPriceAttribute()
    {
        $total = 0;
        foreach ($this->productNeeds as $key => $value) {
            $total += $value->total;
        }
        return $total;
    }

    public function getAmountPpnAttribute()
    {
        $ppn = $this->productNeeds()->first(['tax_id']);
        if ($ppn) {
            $ppn_percentage = $ppn->ppn > 0 ? $ppn->ppn / 100 : 0;
            return $this->amount * $ppn_percentage;
        }
        return $this->amount;
    }

    public function getAmountPpnInvoicedAttribute()
    {
        $tax = 0;
        foreach ($this->orderDelivery as $key => $value) {
            if ($value->is_invoice == 1) {
                $tax += $value->tax_amount;
            }
        }
        return $tax;
    }

    public function getContactNameAttribute()
    {
        $user = User::find($this->contact, ['name']);
        if ($user && $user->role) {
            return $user->name . ' - ' . $user->role->role_name;
        }

        return $user ? $user->name : '-';
    }

    public function getContactNameOnlyAttribute()
    {
        $user = User::find($this->contact, ['name']);
        return $user ? $user->name : '-';
    }

    public function getCompanyNameAttribute()
    {
        $user = CompanyAccount::find($this->company_id, ['account_name']);
        return $user ? $user->account_name : '-';
    }

    public function getSalesNameAttribute()
    {
        $user = User::find($this->sales, ['name']);
        return $user ? $user->name : '-';
    }

    public function getCourierNameAttribute()
    {
        $user = User::find($this->courier, ['name']);
        return $user ? $user->name : '-';
    }

    public function getPaymentTermNameAttribute()
    {
        $payment_term = PaymentTerm::find($this->payment_term, ['name']);
        return $payment_term ? $payment_term->name : '-';
    }

    public function getCreatedByNameAttribute()
    {
        $user = User::find($this->user_created, ['name']);
        return $user ? $user->name : '-';
    }

    public function getContactRoleAttribute()
    {
        $user = User::find($this->contact, ['id']);
        return $user ? $user->role->role_name : '-';
    }

    public function getSelectedAddressAttribute()
    {
        $address = $this->contactUser()->first()?->addressUsers()?->where('is_default', 1)->first();
        return $address ? $address->alamat_detail : '-';
    }

    public function getSubtotalAttribute()
    {
        $total = 0;
        foreach ($this->productNeeds as $key => $value) {
            $total += $value->subtotal;
        }
        return $total;
    }
    public function getSubtotalInvoicedAttribute()
    {
        $total = 0;
        foreach ($this->orderDelivery as $key => $value) {
            if ($value->is_invoice == 1) {
                $total += $value->subtotal_invoice;
            }
        }
        return $total;
    }

    public function getAmountBillingApprovedAttribute()
    {
        return $this->billings()->where('status', 1)->sum('total_transfer') ?? 0;
    }

    /**
     * Get the orderShipping associated with the OrderLead
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function orderShipping()
    {
        return $this->hasOne(OrderShipping::class, 'uid_lead', 'uid_lead')->where('order_type', 'manual');
    }



    public function getAttachmentUrlAttribute()
    {
        return $this->attachment ? getImage($this->attachment) : null;
    }

    public function getWarehouseNameAttribute()
    {
        $warehouse = Warehouse::find($this->warehouse_id);

        return $warehouse ? $warehouse->name : '-';
    }

    public function getBinNameAttribute()
    {
        $bin = MasterBin::find($this->master_bin_id);

        return $bin ? $bin->name : '-';
    }

    public function getTotalQtyAttribute()
    {
        return $this->productNeeds()->sum('qty');
    }

    public function getTotalQtyDeliveryAttribute()
    {
        return $this->productNeeds()->sum('qty_delivery');
    }

    public function getTotalQtyPaymentAttribute()
    {
        return $this->productNeeds()->sum('qty_dibayar');
    }

    public function getEthixItemsAttribute()
    {
        $ethix = EthixMaster::where('so_number', $this->order_number)->get();

        return $ethix;
    }

    public function getPaymentDueDateAttribute()
    {
        $dateCreated = Carbon::parse($this->created_at);
        $payment = $this->paymentTerm?->days_of ?? 0;
        return $dateCreated->addDays($payment + 7);
    }

    public function getGpNumbersAttribute()
    {
        $gp_numbers = $this->orderDelivery()->whereNotNull('gp_submit_number')->groupBy('gp_submit_number')->pluck('gp_submit_number')->implode(',');

        return $gp_numbers;
    }
}
