<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory;

    protected $appends = [
        'price',
        'final_stock',
        'variant_stock',
        'image_url',
        'variant_id',
        'category_name',
        'category_ids',
        'brand_name',
        'u_of_m',
        'stock_warehouse',
        'sales_channels',
        'stock_bundling',
        'stock_bins',
        'carton_name',
        'carton_vendor_code',
        'carton_vendor',
        'all_bin_stock'
    ];

    protected $fillable = [
        'category_id',
        'code',
        'brand_id',
        'name',
        'slug',
        'description',
        'image',
        'agent_price',
        'customer_price',
        'discount_price',
        'discount_percent',
        'stock',
        'weight',
        'is_varian',
        'status',
        'product_like',
        'deleted_at',
        'sku',
        'product_carton_id',
    ];

    /**
     * Relations
     */
    public function category()
    {
        return $this->belongsTo(Category::class)->select(['id', 'name']);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function productImages()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function inventoryItems()
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function inventoryDetailItems()
    {
        return $this->hasMany(InventoryDetailItem::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function productStocks()
    {
        return $this->hasMany(ProductStock::class)->where('is_allocated', 1)->where('company_id', 1);
    }

    /**
     * Attributes
     */
    public function getVariantStockAttribute()
    {
        return $this->variants->sum('stock');
    }

    public function getPriceAttribute()
    {
        $price = ['basic_price' => 0, 'final_price' => 0];
        foreach ($this->variants as $variant) {
            if ($variant->price['basic_price'] > 0 && $variant->price['final_price'] > 0) {
                $price = $variant->price;
                break;
            }
        }
        return $price;
    }

    public function getPriceLevelAttribute()
    {
        $variant = $this->variants->first();
        return $variant ? $variant->price_level : ['level_name' => 'Retail', 'basic_price' => 0, 'final_price' => 0];
    }

    public function getUOfMAttribute()
    {
        $sku = SkuMaster::where('sku', $this->sku)->where('status', 1)->first();
        return $sku ? $sku->package_name : '-';
    }

    public function getCartonNameAttribute()
    {
        $prod = ProductCarton::find($this->product_carton_id);
        return $prod ? $prod->product_name : '-';
    }

    public function getCartonVendorCodeAttribute()
    {
        $prod = ProductCarton::find($this->product_carton_id);
        return $prod ? $prod->vendor_id : '-';
    }

    public function getCartonVendorAttribute()
    {
        $prod = ProductCarton::find($this->product_carton_id);
        if (!empty($prod->vendor_id)) {
            $vendor = Vendor::where('vendor_code', $prod->vendor_id)->first();
            return $vendor ? $vendor->name : '-';
        }
        return '-';
    }

    public function getFinalStockAttribute()
    {
        return $this->productStocks->sum('stock');
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? getImage($this->image) : asset('assets/img/card.svg');
    }

    public function getVariantIdAttribute()
    {
        $variant = $this->variants->first();
        return $variant ? $variant->id : null;
    }

    public function getBrandNameAttribute()
    {
        return $this->brand->name ?? '-';
    }

    public function getCategoryNameAttribute()
    {
        return $this->categories->pluck('name')->implode(', ');
    }

    public function getCategoryIdsAttribute()
    {
        return $this->categories->pluck('id')->toArray();
    }

    public function getStockWarehouseAttribute()
    {
        return DB::table('product_stocks as ps')
            ->join('warehouses as wh', 'ps.warehouse_id', '=', 'wh.id')
            ->where('ps.is_allocated', 1)
            ->where('ps.product_id', $this->id)
            ->where('ps.company_id', auth()?->user()?->company_id ?? 1)
            ->select('ps.warehouse_id', 'wh.name as warehouse_name', DB::raw('SUM(tbl_ps.stock) as stock'))
            ->groupBy('ps.warehouse_id', 'wh.name')
            ->get()
            ->map(function ($stock) {
                return [
                    'id' => $stock->warehouse_id,
                    'warehouse_name' => $stock->warehouse_name,
                    'stock' => $stock->stock,
                ];
            })->values();
        // return $this->productStocks
        //     ->groupBy('warehouse_id')
        //     ->map(function ($stock) {
        //         return [
        //             'id' => $stock->first()->warehouse_id,
        //             'warehouse_name' => $stock->first()->warehouse_name,
        //             'stock' => $stock->sum('stock'),
        //         ];
        //     })->values();
    }

    public function getStockBinsAttribute()
    {
        return DB::table('master_bin_stocks as mbs')
            ->join('master_bins as bin', 'mbs.master_bin_id', '=', 'bin.id')
            ->join('product_variants as pv', 'pv.id', '=', 'mbs.product_variant_id')
            ->where('mbs.product_id', $this->id)
            ->where('mbs.company_id', 1)
            ->where('mbs.stock_type', 'new')
            ->where('pv.status', 1)
            ->whereNull('pv.deleted_at')
            ->where('pv.qty_bundling', 1)
            ->select('mbs.master_bin_id', 'bin.name as warehouse_name', 'mbs.stock')
            ->groupBy('mbs.master_bin_id')
            ->get()
            ->map(function ($stock) {
                return [
                    'id' => $stock->master_bin_id,
                    'warehouse_name' => $stock->warehouse_name,
                    'stock' => $stock->stock,
                ];
            })->values();
        // DB::raw('SUM(tbl_mbs.stock) as stock')
        // return $this->productStocks
        //     ->groupBy('master_bin_id')
        //     ->map(function ($stock) {
        //         return [
        //             'id' => $stock->first()->warehouse_id,
        //             'warehouse_name' => $stock->first()->warehouse_name,
        //             'stock' => $stock->sum('stock'),
        //         ];
        //     })->values();
    }

    public function getStockBundlingAttribute()
    {
        return ProductVariantBundling::where('product_id', $this->id)
            ->with('productStock')
            ->get()
            ->map(function ($bundling) {
                $productStock = $bundling->productStock->first();
                return [
                    'id' => $productStock?->warehouse_id,
                    'warehouse_name' => $productStock?->warehouse_name,
                    'stock' => $productStock?->stock,
                ];
            });
    }

    public function getSalesChannelsAttribute()
    {
        return $this->variants->flatMap->sales_channels->unique()->values();
    }

    public function getAllBinStockAttribute()
    {
        $company_id = auth()->check() ? auth()->user()->company_id : 1;
        return DB::table('inventory_product_stocks as ips')
            ->join('master_bin_stocks as mbs', 'ips.master_bin_id', '=', 'mbs.master_bin_id')
            ->where('mbs.product_id', $this->id)
            ->where('mbs.stock_type', 'new')
            ->where('ips.transfer_category', 'new')
            ->where('ips.company_id', $company_id)
            ->sum('mbs.stock');
    }

    public function masterPoints()
    {
        return $this->hasMany(MasterPoint::class, 'product_id');
    }
}
