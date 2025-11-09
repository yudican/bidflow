<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTransfer extends Model
{
    use HasFactory;
    protected $guarded  = [];

    protected $appends = ['contact_name', 'sales_name', 'master_bin_name'];


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
     * Get the inventoryProductStock that owns the OrderTransfer
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function inventoryProductStock()
    {
        return $this->belongsTo(InventoryProductStock::class, 'uid_inventory', 'uid_inventory');
    }


    /**
     * Get the masterBin that owns the InventoryProductStock
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function masterBin()
    {
        return $this->belongsTo(MasterBin::class);
    }

    public function userContact()
    {
        return $this->belongsTo(User::class, 'contact');
    }

    public function getContactNameAttribute()
    {
        $user = User::find($this->contact, ['name']);
        return $user ? $user->name : '-';
    }

    public function getSalesNameAttribute()
    {
        $user = User::find($this->sales, ['name']);
        return $user ? $user->name : '-';
    }

    public function getMasterBinNameAttribute()
    {
        $user = MasterBin::find($this->master_bin_id, ['name']);
        return $user ? $user->name : '-';
    }
}
