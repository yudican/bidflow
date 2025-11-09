<?php

namespace App\Models;

use App\Traits\Uuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    //use Uuid;
    use HasFactory;

    protected $table = 'sales_return_masters';
    //public $incrementing = false;

    protected $fillable = [
        'uid_retur', 'sr_number', 'order_number', 'brand_id', 'contact', 'sales', 'payment_terms', 'warehouse_id', 'shipping_address', 'warehouse_address', 'notes', 'total', 'status', 'due_date', 'kode_unik', 'temp_kode_unik', 'ongkir', 'company_id'
    ];

    protected $dates = [];
    protected $appends = ['amount', 'subtotal', 'tax_amount', 'discount_amount', 'status_return', 'amount_billing_approved', 'amount_deposite'];
    /**
     * Get the contact that owns the LeadMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contactUser()
    {
        return $this->belongsTo(User::class, 'contact');
    }

    /**
     * Get the sales that owns the LeadMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function salesUser()
    {
        return $this->belongsTo(User::class, 'sales');
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
        return $this->belongsTo(User::class, 'user_created');
    }
    /**
     * Get the sales that owns the LeadMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function courierUser()
    {
        return $this->belongsTo(User::class, 'courier');
    }

    /**
     * Get the brand that owns the LeadMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
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
        return $this->belongsTo(PaymentTerm::class, 'payment_terms');
    }

    /**
     * Get all of the billings for the SalesReturn
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function billings()
    {
        return $this->hasMany(LeadBilling::class, 'uid_lead', 'uid_retur');
    }

    // /**
    //  * Get all of the productNeeds for the LeadMaster
    //  *
    //  * @return \Illuminate\Database\Eloquent\Relations\HasMany
    //  */
    // public function productNeeds()
    // {
    //     return $this->hasMany(SalesReturnItem::class, 'uid_retur', 'uid_retur');
    // }

    /**
     * Get all of the returnItems for the SalesReturn
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function returnItems()
    {
        return $this->hasMany(SalesReturnItem::class, 'uid_retur', 'uid_retur');
    }

    /**
     * Get the returnResi that owns the SalesReturn
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function returResi()
    {
        return $this->hasOne(ReturResi::class, 'uid_retur', 'uid_retur');
    }

    public function getAmountBillingApprovedAttribute()
    {
        return $this->billings()->where('status', 1)->sum('total_transfer') ?? 0;
    }

    /**
     * Get all of the orderDeposites for the OrderLead
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderDeposites()
    {
        return $this->hasMany(OrderDeposit::class, 'uid_lead', 'uid_retur')->where('order_type', 'sales-retur')->where('contact', $this->contact);
    }

    public function getAmountDepositeAttribute()
    {
        $total = 0;
        foreach ($this->orderDeposites as $key => $value) {
            $total += $value->amount;
        }
        return $total;
    }

    public function getStatusReturnAttribute()
    {
        switch ($this->status) {
            case 0:
                return 'Draft';
            case 1:
                return 'Verify';
            case 2:
                return 'Delivery';
            case 3:
                return 'To Invoice';
            case 4:
                return 'Completed';

            default:
                return 'Canceled';
        }
    }

    public function getAmountAttribute()
    {
        $total = 0;
        foreach ($this->returnItems as $key => $value) {
            $total += $value->total;
        }
        return $total + $this->kode_unik + $this->ongkir;
    }

    public function getTaxAmountAttribute()
    {
        $tax = 0;
        foreach ($this->returnItems as $key => $value) {
            $tax += $value->tax_amount;
        }
        return $tax;
    }

    public function getDiscountAmountAttribute()
    {
        $discount = 0;
        foreach ($this->returnItems as $key => $value) {
            $discount += $value->discount_amount;
        }
        return $discount;
    }
    public function getSubtotalAttribute()
    {
        $total = 0;
        foreach ($this->returnItems as $key => $value) {
            $total += $value->subtotal;
        }
        return $total;
    }
}
