<?php

namespace App\Models;

use App\Traits\Uuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadMaster extends Model
{
    //use Uuid;
    use HasFactory;
    //public $incrementing = false;
    protected $fillable = ['title', 'uid_lead', 'contact', 'sales', 'lead_type', 'status', 'approval_notes', 'brand_id', 'customer_need',  'is_negotiation', 'nego_value', 'status_negotiation', 'user_created', 'warehouse_id', 'payment_term', 'type_customer', 'company_id', 'address_id', 'master_bin_id', 'expired_at'];

    protected $appends = ['contact_name', 'contact_name_only', 'sales_name', 'created_by_name', 'brand_name', 'status_name', 'brand_ids', 'total_negotiation', 'margin_total', 'subtotal', 'tax_amount', 'discount_amount', 'amount', 'total', 'total_price', 'amount_ppn', 'created_on', 'company_name', 'warehouse_name'];
    // protected $dates = ['user_created', 'user_updated',];

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
    public function createUser()
    {
        return $this->belongsTo(User::class, 'user_created');
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
    public function leadNegotiations()
    {
        return $this->hasMany(LeadNegotiation::class, 'uid_lead', 'uid_lead');
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

    /**
     * The brands that belong to the LeadMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function brands()
    {
        return $this->belongsToMany(Brand::class, 'brand_lead_master');
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

    public function paymentTerm()
    {
        return $this->belongsTo(PaymentTerm::class, 'payment_term');
    }

    public function getContactNameAttribute()
    {
        $user = User::find($this->contact, ['name', 'id']);
        return $user ? $user->name . ' - ' . $user->role->role_name : '-';
    }
    public function getContactNameOnlyAttribute()
    {
        $user = User::find($this->contact, ['name']);
        return $user ? $user->name : '';
    }

    public function getCompanyNameAttribute()
    {
        $user = User::find($this->contact, ['id']);
        return $user ? $user->company_name : '-';
    }

    public function getSalesNameAttribute()
    {
        $user = User::find($this->sales, ['name']);
        return $user ? $user->name : '-';
    }

    public function getCreatedByNameAttribute()
    {
        $user = User::find($this->user_created, ['name']);
        return $user ? $user->name : '-';
    }

    public function getBrandNameAttribute()
    {
        if (count($this->brands) > 0) {
            return $this->brands()->groupBy('brands.id')->pluck('name')->implode(',');
        }
        return $this->brand?->name ?? '-';
    }

    public function getBrandIdsAttribute()
    {
        if (count($this->brands) > 0) {
            return $this->brands()->groupBy('brands.id')->pluck('brands.id')->toArray();
        }

        return $this->brand ? [$this->brand->id] : [];
    }

    public function getStatusNameAttribute()
    {
        switch ($this->status) {
            case 0:
                return 'Created';
                break;
            case 1:
                return 'Qualified';
                break;
            case 2:
                return 'Waiting Approval';
                break;
            case 3:
                return 'Unqualified';
                break;
            case 4:
                return 'Cancel By User';
                break;
            case 5:
                return 'Other';
                break;
            case 6:
                return 'Rejected';
                break;
            case 7:
                return 'Draft';
                break;
            case 9:
                return 'Pending';
                break;
            default:
                return 'Created';
                break;
        }
    }

    public function getTotalNegotiationAttribute()
    {
        $total = 0;
        foreach ($this->productNeeds as $productNeed) {
            $total += $productNeed->price_nego;
        }
        return $total;
    }

    public function getMarginTotalAttribute()
    {
        $total = 0;
        foreach ($this->productNeeds as $productNeed) {
            if ($productNeed->margin_price) {
                $total += $productNeed->margin_price;
            }
        }
        return $total;
    }

    public function getSubtotalAttribute()
    {
        $total = 0;

        // if ($this->status == 1) {
        //     foreach ($this->productNeeds as $productNeed) {
        //         $total += $productNeed->price_nego;
        //     }

        //     return $total;
        // }

        foreach ($this->productNeeds as $productNeed) {
            // $qty = $productNeed->qty;
            $total += $productNeed->subtotal;
        }

        return $total;
    }

    public function getDiscountAmountAttribute()
    {
        $total = 0;
        // if ($this->status == 1) {
        //     foreach ($this->productNeeds as $productNeed) {
        //         if ($productNeed->discount_percent > 0) {
        //             $total += $productNeed->price_nego * $productNeed->discount_percent;
        //         }
        //     }

        //     return $total;
        // }

        foreach ($this->productNeeds as $productNeed) {
            $total += $productNeed->discount_amount;
        }

        return $total;
    }

    public function getTaxAmountAttribute()
    {
        $total = 0;
        // if ($this->status == 1) {
        //     foreach ($this->productNeeds as $productNeed) {
        //         if ($productNeed->tax_percentage > 0) {
        //             if ($this->discount_amount > 0) {
        //                 $price_with_discount = $productNeed->price_nego + $this->discount_amount;
        //                 $total += $price_with_discount * $productNeed->tax_percentage;
        //             } else {
        //                 $total += $productNeed->price_nego * $productNeed->tax_percentage;
        //             }
        //         }
        //     }
        //     return $total;
        // }

        foreach ($this->productNeeds as $productNeed) {
            $total += $productNeed->tax_amount;
        }
        return $total;
    }

    public function getTotalPriceAttribute()
    {
        $total = 0;
        foreach ($this->productNeeds as $productNeed) {
            $total += $productNeed->total;
        }
        return $total;
    }

    public function getAmountAttribute()
    {
        $total = 0;
        foreach ($this->productNeeds as $key => $value) {
            $total += $value->subtotal - $value->discount_amount;
        }
        return $total + $this->kode_unik + $this->ongkir;
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

    public function getTotalAttribute()
    {
        return $this->amount + $this->amount_ppn;
    }

    public function getCreatedOnAttribute()
    {
        return Carbon::parse($this->created_at)->format('d M Y');
    }

    public function getWarehouseNameAttribute()
    {
        $warehouse = Warehouse::find($this->warehouse_id, ['name']);

        return $warehouse ? $warehouse->name : '-';
    }
}
