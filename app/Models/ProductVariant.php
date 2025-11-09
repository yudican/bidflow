<?php

namespace App\Models;

use App\Traits\Uuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductVariant extends Model
{
    //use Uuid;
    use HasFactory;

    //public $incrementing = false;

    protected $fillable = ['product_id', 'sku', 'sku_marketplace', 'package_id', 'variant_id', 'name', 'slug', 'description', 'image', 'agent_price', 'customer_price', 'discount_price', 'discount_percent', 'stock', 'weight', 'status', 'sku_variant', 'qty_bundling', 'deleted_at', 'sales_channel', 'is_bundling'];

    protected $dates = [];
    protected $appends = ['price', 'price_level', 'image_url', 'margin_price', 'package_name', 'variant_name', 'u_of_m', 'stock_off_market', 'final_stock', 'sales_channels', 'stock_warehouse', 'stock_bundling', 'stock_bins', 'sales_channels_name', 'sales_channel_uid', 'all_bin_stock'];

    /**
     * Get the product that owns the ProductVariant
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the package that owns the ProductVariant
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the variant that owns the ProductVariant
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }


    /**
     * Get the productMarginBottom that owns the ProductVariant
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function productMarginBottom()
    {
        return $this->hasOne(MarginBottom::class, 'product_variant_id');
    }

    /**
     * Get all of the prices for the ProductVariant
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    /**
     * The salesChannels that belong to the ProductVariant
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function salesChannels()
    {
        return $this->belongsToMany(SalesChannel::class, 'product_variant_channel', 'product_variant_id', 'channel_uid');
    }

    // salesChannelsName
    public function getSalesChannelsNameAttribute()
    {
        $salesChannels = $this->salesChannels()->pluck('sales_channels.channel_name')->toArray();
        return $salesChannels;
    }

    // salesChannelsUid
    public function getSalesChannelUidAttribute()
    {
        $salesChannels = $this->salesChannels()->pluck('sales_channels.channel_uid')->toArray();
        return $salesChannels;
    }

    /**
     * Get all of the productNeeds for the ProductVariant
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productNeeds()
    {
        return $this->hasMany(ProductNeed::class, 'product_id');
    }

    /**
     * Get all of the inventoryStock for the ProductVariant
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function inventoryStock()
    {
        return $this->hasMany(InventoryItem::class, 'product_id')->whereHas('inventoryStock', function ($query) {
            return $query->whereIn('status', ['ready', 'done']);
        })->where('type', 'stock');
    }

    /**
     * Get all of the productVariantStock for the ProductVariant
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productVariantStock()
    {
        $companya = CompanyAccount::whereStatus(1)->first();
        $company_account = auth()->check() ? auth()->user()->company_id : $companya?->id;
        return $this->hasMany(ProductVariantStock::class)->where('company_id', $company_account ?? 1);
    }

    /**
     * Get all of the masterBinStocks for the ProductVariant
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function masterBinStocks()
    {
        // $company_account = CompanyAccount::whereStatus(1)->first();
        return $this->hasMany(MasterBinStock::class);
    }

    public function getFinalStockAttribute()
    {
        $stock = $this->productVariantStock()->sum('qty');
        return $stock > 0 ? $stock : 0;
    }

    public function getStockOffMarketAttribute()
    {
        $stock = $this->productVariantStock()->sum('stock_of_market');
        return $stock > 0 ? $stock : 0;
    }

    /**
     * Get all of the bundlings for the ProductVariant
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bundlings()
    {
        return $this->hasMany(ProductVariantBundling::class, 'product_variant_id');
    }

    public function getPriceAttribute()
    {
        $level = null;
        if (auth()->check()) {
            $role = auth()->user()->role;
            $level  = Level::whereHas('roles', function ($query) use ($role) {
                $query->where('role_id', $role->id);
            })->first();

            if ($level) {
                $price = $this->prices()->where('level_id', $level->id)->where('product_id', $this->product_id)->first();

                if ($price) {
                    return [
                        'basic_price' => $price->basic_price,
                        'final_price' => $price->final_price,
                    ];
                }
            }
        }

        $price = $this->prices()->whereHas('level', function ($query) use ($level) {
            return $query->where('name', $level ? $level->name : 'Retail');
        })->first();

        if ($price) {
            return [
                'basic_price' => $price->basic_price,
                'final_price' => $price->final_price,
            ];
        }

        return [
            'basic_price' => 0,
            'final_price' => 0,
        ];
    }

    public function getPrice($role_user)
    {
        $role = Role::where('role_type', $role_user)->first();
        $level  = Level::whereHas('roles', function ($query) use ($role) {
            $query->where('role_id', $role->id);
        })->first();
        $price = $this->prices()->whereHas('level', function ($query) use ($level) {
            return $query->where('name', $level ? $level->name : 'Retail');
        })->first();
        if ($price) {
            return [
                'basic_price' => $price->basic_price,
                'final_price' => $price->final_price,
            ];
        }
        return [
            'basic_price' => 0,
            'final_price' => 0,
        ];
    }

    // get costummer_price attribute
    public function getPriceLevelAttribute()
    {
        $levels = Level::all()->map(function ($level) {
            $price = $this->prices()->whereHas('level', function ($query) use ($level) {
                return $query->where('name', $level->name);
            })->first();
            if ($price) {
                return [
                    'level_name' => $level->name,
                    'basic_price' => $price->basic_price,
                    'final_price' => $price->final_price,
                ];
            }
            return [
                'level_name' => $level->name,
                'basic_price' => 0,
                'final_price' => 0,
            ];
        });

        return $levels;
    }

    /**
     * Get all of the productStocks for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productStocks()
    {
        return $this->hasMany(ProductStock::class, 'product_id', 'product_id')->where('is_allocated', 1);
    }


    public function productImages()
    {
        return $this->hasMany(ProductImage::class, 'product_id')->where('type', 'product-variant');
    }

    // /**
    //  * Get all of the productStocks for the Product
    //  *
    //  * @return \Illuminate\Database\Eloquent\Relations\HasMany
    //  */

    // public function getFinalStockAttribute()
    // {
    //     $stock = $this->productStocks()->sum('stock');
    //     return $stock ?? 0;
    // }

    public function getImageUrlAttribute()
    {
        return $this->image ? getImage($this->image) : asset('assets/img/card.svg');
    }

    public function getMarginPriceAttribute()
    {
        $margin = $this->productMarginBottom()->first();
        if ($margin) {
            return $margin->margin;
        }
        return 0;
    }

    public function getVariantNameAttribute()
    {
        return $this->variant?->name ?? '-';
    }

    public function getPackageNameAttribute()
    {
        return $this->package?->name ?? '-';
    }

    public function getUOfMAttribute()
    {
        return $this->package?->name ?? '-';
    }

    public function getSalesChannelsAttribute()
    {
        $sales_channels = $this->sales_channel;
        if ($sales_channels) {
            $sales_channels = explode(',', $sales_channels);

            return $sales_channels;
        }

        return [];
    }

    // get stock_warehouse
    public function getStockWarehouseAttribute()
    {
        $product_ids = ProductVariantBundling::where('product_variant_id', $this->id)->get()->pluck('product_id')->toArray();
        $warehouses = [];

        $newBundlings = DB::table('vw_product_stocks_master as sm')
            ->join('warehouses as wh', 'sm.warehouse_id', '=', 'wh.id')
            ->whereIn('product_id', $product_ids)
            // ->where('sm.company_id', auth()?->user()?->company_id ?? 1)
            ->selectRaw('product_id, warehouse_id,warehouse_name, SUM(stock) as stock_total')
            ->groupBy('product_id',  'warehouse_id')
            ->get();

        foreach ($newBundlings as $productStock) {
            $warehouses[] = [
                'id' => $productStock->warehouse_id,
                'warehouse_id' => $productStock->warehouse_id,
                'warehouse_name' => $productStock->warehouse_name,
                'product_id' => $productStock->product_id,
                'stock' => $productStock->stock_total,
            ];
        }

        return $warehouses;
        // return DB::table('product_variant_stocks as ps')
        //     ->join('warehouses as wh', 'ps.warehouse_id', '=', 'wh.id')
        //     ->join('product_variants as pv', 'ps.product_variant_id', '=', 'pv.id')
        //     ->where('ps.product_variant_id', $this->id)
        //     ->where('ps.company_id', auth()?->user()?->company_id ?? 1)
        //     ->select('ps.warehouse_id', 'pv.product_id', 'wh.name as warehouse_name', DB::raw('SUM(tbl_ps.stock_of_market) as stock'))
        //     ->groupBy('ps.warehouse_id', 'wh.name')
        //     ->get()
        //     ->map(function ($stock) {
        //         return [
        //             'id' => $stock->warehouse_id,
        //             'warehouse_name' => $stock->warehouse_name,
        //             'stock' => $stock->stock,
        //             'warehouse_id' => $stock->warehouse_id,
        //             'product_id' => $stock->product_id,
        //         ];
        //     })->values();

        // $warehouses = [];
        // $products = $this->productVariantStock()->groupBy('warehouse_id')->select('*')->selectRaw("SUM(stock_of_market) as stock_total")->get();
        // foreach ($products as $productStock) {
        //     if ($this->is_bundling > 0) {
        //         $product_ids = ProductVariantBundling::where('product_variant_id', $this->id)->get()->pluck('product_id')->toArray();
        //         $newBundlings = ProductStock::whereIn('product_id', $product_ids)
        //             ->select('product_id', 'company_id', 'warehouse_id', 'stock')
        //             ->groupBy('product_id', 'warehouse_id', 'company_id')
        //             ->orderBy('stock', 'DESC')
        //             ->get();
        //         foreach ($newBundlings as $productStock) {
        //             $warehouses[$productStock->warehouse_id] = [
        //                 'id' => $productStock->warehouse_id,
        //                 'warehouse_id' => $productStock->warehouse_id,
        //                 'warehouse_name' => $productStock->warehouse_name,
        //                 'product_id' => $productStock->product_id,
        //                 'stock' => $productStock->stock,
        //             ];
        //         }

        //         $warehouses = array_values($warehouses);
        //     } else {
        //         if (($productStock)) {
        //             $warehouses[] = [
        //                 'id' => $productStock->warehouse_id,
        //                 'company_id' => $productStock->company_id,
        //                 'warehouse_name' => $productStock->warehouse_name,
        //                 'stock' => $productStock->stock_total,
        //             ];
        //         }
        //     }
        // }

        // return $warehouses;
    }

    public function getStockBundlingAttribute()
    {
        $product_ids = ProductVariantBundling::where('product_variant_id', $this->id)->get()->pluck('product_id')->toArray();
        $warehouses = [];

        $newBundlings = DB::table('vw_product_stocks_master as sm')
            ->join('warehouses as wh', 'sm.warehouse_id', '=', 'wh.id')
            ->whereIn('product_id', $product_ids)
            // ->where('sm.company_id', auth()?->user()?->company_id ?? 1)
            ->selectRaw('product_id, warehouse_id,warehouse_name, SUM(stock) as stock_total')
            ->groupBy('product_id',  'warehouse_id')
            ->get();

        foreach ($newBundlings as $productStock) {
            $warehouses[] = [
                'id' => $productStock->warehouse_id,
                'warehouse_id' => $productStock->warehouse_id,
                'warehouse_name' => $productStock->warehouse_name,
                'product_id' => $productStock->product_id,
                'stock' => $productStock->stock_total,
            ];
        }

        return $warehouses;
    }


    public function getStockBinsAttribute()
    {
        return DB::table('master_bin_stocks as mbs')
            ->join('master_bins as bin', 'mbs.master_bin_id', '=', 'bin.id')
            ->where('mbs.product_variant_id', $this->id)
            ->where('mbs.company_id', 1)
            ->where('mbs.stock_type', 'new')
            ->select('mbs.master_bin_id', 'bin.name as warehouse_name', DB::raw('SUM(tbl_mbs.stock) as stock'))
            ->groupBy('mbs.master_bin_id')
            ->get()
            ->map(function ($stock) {
                return [
                    'id' => $stock->master_bin_id,
                    'warehouse_name' => $stock->warehouse_name,
                    'stock' => $stock->stock,
                ];
            })->values();
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

    public function getAllBinStockAttribute()
    {
        $company_id = auth()->check() ? auth()->user()->company_id : 1;
        return DB::table('inventory_product_stocks as ips')
            ->join('master_bin_stocks as mbs', 'ips.master_bin_id', '=', 'mbs.master_bin_id')
            ->where('mbs.product_id', $this->product_id)
            ->where('ips.transfer_category', 'new')
            ->where('ips.company_id', $company_id)
            ->sum('mbs.stock');
    }
}
