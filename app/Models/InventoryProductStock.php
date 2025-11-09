<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryProductStock extends Model
{
    use HasFactory;
    protected $fillable = [
        'uid_inventory',
        'uid_lead',
        'reference_number',
        'warehouse_id',
        'destination_warehouse_id',
        'created_by',
        'vendor',
        'status',
        'received_date',
        'note',
        'received_by',
        'allocated_by',
        'inventory_type',
        'inventory_status',
        'product_id',
        'company_id',
        'status_ethix',
        'so_ethix',
        'post_ethix',
        'gp_transfer_number',
        'is_konsinyasi',
        'master_bin_id',
        'transfer_category'
    ];

    protected $appends = [
        'created_on',
        'product_name',
        'created_by_name',
        'received_by_name',
        'allocated_by_name',
        'warehouse_name',
        'warehouse_destination_name',
        'bin_destination_name',
        'contact_name',
        'role_name',
        'company_name',
        'total_qty',
        'selected_po',
        'vendor_name',
        'status_name',
        'received_number',
        'sku',
        'master_bin_name'
        // 'product_price'
    ];

    // inventory detail item
    public function detailItems()
    {
        return $this->hasMany(InventoryDetailItem::class, 'uid_inventory', 'uid_inventory');
    }

    public function items()
    {
        return $this->hasMany(InventoryItem::class, 'uid_inventory', 'uid_inventory');
    }

    public function historyAllocations()
    {
        return $this->hasMany(StockAllocationHistory::class, 'uid_inventory', 'uid_inventory');
    }

    /**
     * Get the warehouse that owns the InventoryProductStock
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the orderTransfer that owns the InventoryProductStock
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function orderTransfer()
    {
        return $this->belongsTo(OrderTransfer::class, 'uid_lead', 'uid_lead');
    }


    /**
     * Get the warehouse that owns the InventoryProductStock
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouseDestination()
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }

    /**
     * Get the userCreated that owns the InventoryProductStock
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userCreated()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getProductNameAttribute()
    {
        $product = Product::find($this->product_id, ['name']);
        return $product ? $product->name : '-';
    }

    public function getSkuAttribute()
    {
        $product = Product::find($this->product_id, ['sku']);
        return $product ? $product->sku : '-';
    }

    public function getCreatedOnAttribute()
    {
        return $this->created_at->format('d M Y');
    }

    public function getCreatedByNameAttribute()
    {
        $user = User::find($this->created_by, ['name']);
        return $user ? $user->name : '-';
    }

    public function getReceivedByNameAttribute()
    {
        $user = User::find($this->received_by, ['name']);
        return $user ? $user->name : '-';
    }

    public function getAllocatedByNameAttribute()
    {
        $user = User::find($this->allocated_by, ['name']);
        return $user ? $user->name : '-';
    }

    public function getWarehouseNameAttribute()
    {
        $warehouse = Warehouse::find($this->warehouse_id, ['name']);
        return $warehouse ? $warehouse->name : '-';
    }

    public function getWarehouseDestinationNameAttribute()
    {
        $warehouse = Warehouse::find($this->destination_warehouse_id, ['name']);
        return $warehouse ? $warehouse->name : '-';
    }

    public function getBinDestinationNameAttribute()
    {

        return $this->orderTransfer?->master_bin_name ?? '-';
    }

    public function getMasterBinNameAttribute()
    {
        $bin = MasterBin::find($this->master_bin_id, ['name']);
        return $bin ? $bin->name : '-';
    }

    public function getContactNameAttribute()
    {
        $contact = $this->orderTransfer?->contact;
        $user = User::where('id', $contact)->first(['name']);
        if ($user) {
            return $user->name;
        }

        return '-';
    }

    public function getRoleNameAttribute()
    {
        $contact = $this->orderTransfer?->contact;
        $user = User::where('id', $contact)->first(['id']);
        if ($user) {
            return $user->role?->role_name;
        }

        return '-';
    }

    public function getCompanyNameAttribute()
    {
        $company = PurchaseOrder::where('po_number', $this->reference_number)->first(['company_id']);
        return $company ? $company->company_name : '-';
    }

    public function getTotalQtyAttribute()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->qty;
        }
        return $total;
    }

    public function getSelectedPoAttribute()
    {
        $purchase = PurchaseOrder::with(['items'])->where('po_number', $this->reference_number)->first();
        if ($purchase) {
            return $purchase;
        }

        return null;
    }

    public function getVendorNameAttribute()
    {
        $vendor = Vendor::where('vendor_code', $this->vendor)->first(['name']);
        if ($vendor) {
            return $vendor->name;
        }

        return null;
    }
    public function getStatusNameAttribute()
    {
        switch ($this->status) {
            case 'done':
                return 'Success';
            case 'cancel':
                return 'Cancel';
            case 'waiting':
                return 'Waiting Approval';
            case 'reject':
                return 'Reject';
            default:
                return 'Draft';
        }
    }
    public function getReceivedNumberAttribute()
    {
        if (count($this->items) > 0) {
            return $this->items[0]['received_number'];
        }
        return '-';
    }

    public function masterBin()
    {
        return $this->belongsTo(MasterBin::class);
    }
}
