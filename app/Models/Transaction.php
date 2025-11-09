<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
	use HasFactory;
	protected $guarded = [];
	protected $appends = ['status_transaction', 'status_delivery_transaction', 'user_name', 'user_email', 'user_phone', 'brand_name', 'voucher_name', 'payment_method_name', 'shipping_type_name', 'awb_number', 'status_name', 'status_delivery_name', 'final_status', 'create_by_name', 'qty_total', 'user_info', 'subtotal', 'ppn', 'total', 'transaction_url', 'bagi_hasil', 'weight_total'];

	public static function generateInvoiceNumberNumber()
	{
		$today = now()->format('Y/'); // Ambil tanggal hari ini
		$latestInvoice = self::whereNotNull('invoice_number')->orderBy('invoice_date', 'desc')->first();
		$number = 1;
		if ($latestInvoice) {
			// Parsing nomor terakhir
			$latestNumber = explode('/', $latestInvoice->invoice_number);
			if (count($latestNumber) > 3) {
				$number = intval($latestNumber[3]) + 1;
			} else {
				$number = intval($latestNumber[2]) + 1;
			}
		}

		// Format nomor dengan panjang 4 digit dan tambahkan 'INV/tgl/auto'
		$formattedNumber = str_pad($number, 8, '0', STR_PAD_LEFT);
		$invoiceNumber = 'INV/' . $today . $formattedNumber;

		return $invoiceNumber;
	}

	public static function getBadgeCount($stage, $type = 'general')
	{
		$transaction = self::query()->whereType($type);

		switch ($stage) {
			case 'waiting-payment':
				$transaction->where('status', 1);
				break;

			case 'waiting-confirmation':
				$transaction->where('status', 2);
				break;

			case 'confirm-payment':
				$transaction->where('status', 3);
				break;

			case 'on-process':
				$transaction->where('status', 7)->where('status_delivery', 1);
				break;

			case 'ready-to-ship':
				$transaction->whereIn('status', [3, 7])->where('status_delivery', 21);
				break;

			case 'on-delivery':
				$transaction->where('status_delivery', 3);
				break;

			case 'delivered':
				$transaction->where('status_delivery', 4)->where('status', 7);
				break;
			case 'returned':
				$transaction->where('status_delivery', 5)->where('status', 7);
				break;

			case 'complete':
				$transaction->where('status_delivery', 4)->where('status', 7)->whereNull('completed_at');
				break;

			case 'cancelled':
				$transaction->whereIn('status', [4, 6]);
				break;
			case 'report-transaction':
				$transaction->where('status', '>', 0);
				break;

			case 'new-order':
				$transaction->where('status', 0)->where('status_delivery', 0);
				break;

			default:
				return 0;
		}

		if (Auth::check()) {
			$user = Auth::user();
			$role = $user->role;
			if (in_array($role->role_type, ['agent-telmar'])) {
				$transaction->where('user_create', $user->id);
			}
		}

		return $transaction->count();
	}

	/**
	 * Get the brand that owns the Transaction
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function brand()
	{
		return $this->belongsTo(Brand::class);
	}

	/**
	 * Get the product that owns the Transaction
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function product()
	{
		return $this->belongsTo(Product::class);
	}

	/**
	 * Get the user that owns the Transaction
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function user()
	{
		return $this->belongsTo(User::class);
	}


	/**
	 * Get the user that owns the Transaction
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function userCreated()
	{
		return $this->belongsTo(User::class, 'user_create');
	}

	/**
	 * Get the paymentMethod that owns the Transaction
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function paymentMethod()
	{
		return $this->belongsTo(PaymentMethod::class);
	}

	/**
	 * Get all of the confirmPayment for the Transaction
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function confirmPayment()
	{
		return $this->hasOne(ConfirmPayment::class, 'transaction_id')->latest();
	}

	/**
	 * Get all of the transactionDetail for the Transaction
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */

	public function transactionDetail()
	{
		return $this->hasMany(TransactionDetail::class, 'invoice_id', 'id_transaksi');
	}

	/**
	 * Get the voucher that owns the Transaction
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function voucher()
	{
		return $this->belongsTo(Voucher::class);
	}

	// comment rating
	/**
	 * Get all of the commentRating for the Transaction
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function commentRating()
	{
		return $this->hasMany(CommentRating::class, 'transaction_id');
	}

	/**
	 * Get the addressUser that owns the Transaction
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function addressUser()
	{
		return $this->belongsTo(AddressUser::class);
	}

	/**
	 * Get all of the logPrint for the Transaction
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function logPrint()
	{
		return $this->hasMany(LogPrintOrder::class, 'uid_lead', 'id_transaksi'); // with =, not contain
	}

	/**
	 * Get all of the logs for the Transaction
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function logs()
	{
		return $this->hasMany(LogApproveFinance::class, 'transaction_id')->orderBy('created_at', 'asc');
	}

	/**
	 * Get the shippingType that owns the Transaction
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function shippingType()
	{
		return $this->belongsTo(ShippingType::class);
	}

	/**
	 * Get the shipper that owns the Transaction
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function shipperWarehouse()
	{
		return $this->belongsTo(Warehouse::class, 'shipper_address_id');
	}

	public function getWeightTotalAttribute()
	{
		if ($this->weight > 0) {
			return $this->weight;
		}
		$weight_total = 0;
		foreach ($this->transactionDetail as $key => $detail) {
			$weight_total += $detail->productVariant?->weight;
		}

		return $weight_total > 0 ? round($weight_total / 1000) : 0;
	}

	public static function totalAllRevenueThisWeek($sdc = '', $serviceId = 0)
	{

		$results = DB::select(
			"SELECT DATE(created_at) as dtstat, IFNULL(SUM(amount_to_pay),0) as total_gross
                FROM tbl_transactions
                WHERE
                    brand_id={$sdc} AND
                    created_at BETWEEN DATE(NOW()) - INTERVAL 30 DAY AND NOW()
                GROUP BY DATE(created_at) ORDER BY DATE(created_at)"
		);

		$today = 0;
		$yesterday1 = 0;
		$yesterday2 = 0;
		$yesterday3 = 0;
		$yesterday4 = 0;
		$yesterday5 = 0;
		$yesterday6 = 0;
		$yesterday7 = 0;
		$yesterday8 = 0;
		$yesterday9 = 0;
		$yesterday10 = 0;
		$yesterday11 = 0;
		$yesterday12 = 0;
		$yesterday13 = 0;
		$yesterday14 = 0;
		$yesterday15 = 0;
		$yesterday16 = 0;
		$yesterday17 = 0;
		$yesterday18 = 0;
		$yesterday19 = 0;
		$yesterday20 = 0;
		$yesterday21 = 0;
		$yesterday22 = 0;
		$yesterday23 = 0;
		$yesterday24 = 0;
		$yesterday25 = 0;
		$yesterday26 = 0;
		$yesterday27 = 0;
		$yesterday28 = 0;
		$yesterday29 = 0;
		$yesterday30 = 0;

		foreach ($results as $tmp) {
			if ($tmp->dtstat == date("Y-m-d"))
				$today += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 24)))
				$yesterday1 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 48)))
				$yesterday2 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 72)))
				$yesterday3 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 96)))
				$yesterday4 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 120)))
				$yesterday5 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 144)))
				$yesterday6 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 168)))
				$yesterday7 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 192)))
				$yesterday8 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 216)))
				$yesterday9 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 240)))
				$yesterday10 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 264)))
				$yesterday11 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 288)))
				$yesterday12 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 312)))
				$yesterday13 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 336)))
				$yesterday14 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 360)))
				$yesterday15 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 384)))
				$yesterday16 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 408)))
				$yesterday17 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 432)))
				$yesterday18 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 456)))
				$yesterday19 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 480)))
				$yesterday20 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 504)))
				$yesterday21 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 528)))
				$yesterday22 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 552)))
				$yesterday23 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 576)))
				$yesterday24 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 600)))
				$yesterday25 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 624)))
				$yesterday26 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 648)))
				$yesterday27 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 672)))
				$yesterday28 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 696)))
				$yesterday29 += $tmp->total_gross;
			else if ($tmp->dtstat == date("Y-m-d", time() - (60 * 60 * 720)))
				$yesterday30 += $tmp->total_gross;
		}

		$day[0] = $yesterday30;
		$day[1] = $yesterday29;
		$day[2] = $yesterday28;
		$day[3] = $yesterday27;
		$day[4] = $yesterday26;
		$day[5] = $yesterday25;
		$day[6] = $yesterday24;
		$day[7] = $yesterday23;
		$day[8] = $yesterday22;
		$day[9] = $yesterday21;
		$day[10] = $yesterday20;
		$day[11] = $yesterday19;
		$day[12] = $yesterday18;
		$day[13] = $yesterday17;
		$day[14] = $yesterday16;
		$day[15] = $yesterday15;
		$day[16] = $yesterday14;
		$day[17] = $yesterday13;
		$day[18] = $yesterday12;
		$day[19] = $yesterday11;
		$day[20] = $yesterday10;
		$day[21] = $yesterday9;
		$day[22] = $yesterday8;
		$day[23] = $yesterday7;
		$day[24] = $yesterday6;
		$day[25] = $yesterday5;
		$day[26] = $yesterday4;
		$day[27] = $yesterday3;
		$day[28] = $yesterday2;
		$day[29] = $yesterday1;
		$day[30] = $today;

		return $day;
	}

	/**
	 * Get the label associated with the Transaction
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function label()
	{
		return $this->hasOne(TransactionLabel::class, 'id_transaksi', 'id_transaksi');
	}

	/**
	 * Get the awb associated with the Transaction
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function awb()
	{
		return $this->hasOne(TransactionAwb::class, 'id_transaksi', 'id_transaksi');
	}

	/**
	 * Get the awb associated with the Transaction
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function status()
	{
		return $this->hasMany(TransactionStatus::class, 'id_transaksi', 'id_transaksi')->orderBy('created_at', 'desc');
	}

	/**
	 * Get the awb associated with the Transaction
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function deliveryStatus()
	{
		return $this->hasMany(TransactionDeliveryStatus::class, 'id_transaksi', 'id_transaksi')->orderBy('created_at', 'desc');
	}

	public function getStatusTransactionAttribute()
	{
		$status = $this->status()->first();
		if ($status) {
			return $status->status;
		}

		return null;
	}

	public function getStatusDeliveryTransactionAttribute()
	{
		$status = $this->deliveryStatus()->first();
		if ($status) {
			return $status->status;
		}

		return null;
	}

	public function getAwbNumberAttribute()
	{
		$awb = $this->awb;
		if ($awb) {
			return $awb->awb_number;
		}

		return null;
	}

	public function getStatusNameAttribute()
	{

		switch ($this->status) {
			case 1:
				return 'Waiting Payment';
				break;
			case 2:
				return 'On Progress';
				break;
			case 3:
				return 'Success';
				break;
			case 4:
				return 'Cancel By System';
				break;
			case 5:
				return 'Cancel By User';
				break;
			case 6:
				return 'Cancel By Admin';
				break;
			case 7:
				return 'Admin Process';
				break;
			default:
				return 'Waiting Payment';
				break;
		}
	}

	public function getStatusDeliveryNameAttribute()
	{
		switch ($this->status) {
			case 1:
				return 'Waiting Payment';
				break;
			case 2:
				return 'On Progress';
				break;
			case 3:
				return 'Success';
				break;
			case 4:
				return 'Cancel By System';
				break;
			case 5:
				return 'Cancel By User';
				break;
			case 6:
				return 'Cancel By Admin';
				break;
			case 7:
				return 'Admin Process';
				break;
			default:
				return 'Waiting Payment';
				break;
		}
	}

	public function getUserNameAttribute()
	{
		$user = User::find($this->user_id, ['name']);

		if ($user) {
			return $user->name;
		}

		return '-';
	}

	public function getUserEmailAttribute()
	{
		$user = User::find($this->user_id, ['email']);

		if ($user) {
			return $user->email;
		}

		return '-';
	}

	public function getUserPhoneAttribute()
	{
		$user = User::find($this->user_id, ['telepon']);

		if ($user) {
			return $user->telepon;
		}

		return '-';
	}

	public function getPaymentMethodNameAttribute()
	{
		$payment = PaymentMethod::find($this->payment_method_id, ['nama_bank']);

		if ($payment) {
			return $payment->nama_bank;
		}

		return '-';
	}


	public function getShippingTypeNameAttribute()
	{
		$payment = ShippingType::find($this->shipping_type_id, ['shipping_type_name']);

		if ($payment) {
			return $payment->shipping_type_name;
		}

		return '-';
	}

	public function getBrandNameAttribute()
	{
		$brand = Brand::find($this->brand_id, ['name']);

		if ($brand) {
			return $brand->name;
		}

		return '-';
	}

	public function getVoucherNameAttribute()
	{
		$voucher = Voucher::find($this->voucher_id, ['title']);

		if ($voucher) {
			return $voucher->title;
		}

		return '-';
	}

	public function getFinalStatusAttribute()
	{
		$status = $this->status;
		$status_delivery = $this->status_delivery;

		if ($status == 1) {
			return 'Menunggu Pembayaran';
		}

		if ($status == 2) {
			return 'Menunggu Konfirmasi';
		}

		if ($status == 3) {
			return 'Pembayaran Diverifikasi';
		}

		if (in_array($status, [4, 5])) {
			return 'Dibatalkan User';
		}

		if (in_array($status, [6])) {
			return 'Dibatalkan Otomatis';
		}

		if ($status == 7) {
			if ($status_delivery == 0) {
				return 'Pembayaran Diverifikasi';
			}
			if ($status_delivery == 4) {
				if ($this->completed_at) {
					return 'Pesanan Selesai';
				}
				return 'Pesanan Terkirim';
			}
			if ($status_delivery == 5) {
				return 'Dikembalikan';
			}
			if ($status_delivery == 6) {
				return 'Pengiriman Gagal';
			}
			if (in_array($status_delivery, [1, 2])) {
				return 'Sedang Disiapkan';
			}
			if (in_array($status_delivery, [3, 21])) {
				return 'Proses Pengiriman';
			}
			return 'Pembayaran Diverifikasi';
		}


		if ($status == 0 && $status_delivery == 0) {
			return 'Order Baru';
		}

		return 'Pembatalan Otomatis';
	}

	public function getCreateByNameAttribute()
	{
		$user = User::find($this->user_create, ['name']);

		if ($user) {
			return $user->name;
		}

		return '-';
	}

	public function getQtyTotalAttribute()
	{
		$qty = 0;
		$details = $this->transactionDetail;
		foreach ($details as $detail) {
			$qty += $detail->qty;
		}

		return $qty;
	}

	public function getUserInfoAttribute()
	{
		$user = User::find($this->user_id, [
			'name',
			'telepon',
			'email',
		]);

		if ($user) {
			return [
				'name' => $user->name,
				'phone' => $user->telepon,
				'email' => $user->email,
			];
		}

		return null;
	}

	public function getSubtotalAttribute()
	{
		$subtotal = 0;
		$details = $this->transactionDetail;
		foreach ($details as $detail) {
			$subtotal += $detail->subtotal;
		}

		return $subtotal;
	}

	public function getPpnAttribute()
	{
		return ($this->subtotal - $this->diskon) * 0.11;
	}

	public function getTotalAttribute()
	{
		$total = $this->nominal + $this->ppn - $this->deduction;
		return $total;
	}

	public function getTransactionUrlAttribute()
	{
		return getSetting('FRONTEND_URL') . '/transaction/' . $this->id_transaksi;
	}

	public function getPph21($amount = 0)
	{
		$pphs = MasterPph::all();
		$percent = 5;
		foreach ($pphs as $pph) {
			if ($amount <= $pph->pph_amount) {
				$percent = $pph->pph_percentage ? $pph->pph_percentage : 1;
			} else if ($amount >= $pph->pph_amount && $amount <= $pph->pph_amount) {
				$percent = $pph->pph_percentage ? $pph->pph_percentage : 1;
			}
		}

		return $percent / 100;
	}

	public function getBagiHasilAttribute()
	{
		$nominal = $this->nominal;
		$fee = $nominal * 0.25;
		$dpp = $fee / 1.11;
		$ppn = $dpp * 0.11;
		$total_pembagian = $dpp * 0.7;
		$ign = $dpp * 0.3;
		$pph21 = $total_pembagian * $this->getPph21($total_pembagian);
		$nutrisionist_amount = $total_pembagian - $pph21;

		return [
			'dpp' => $dpp,
			'ppn' => $ppn,
			'total_pembagian' => $total_pembagian,
			'ign' => $ign,
			'pph21' => $pph21,
			'nutrisionist_amount' => $nutrisionist_amount,
		];
	}

	public function getDataUserAddressAttribute($value)
	{
		return json_decode($value, true);
	}

	public function getDataBrandAttribute($value)
	{
		return json_decode($value, true);
	}

	public function getDataPaymentMethodAttribute($value)
	{
		return json_decode($value, true);
	}

	public function getDataUserAttribute($value)
	{
		return json_decode($value, true);
	}

	public function getDataVoucherAttribute($value)
	{
		return json_decode($value, true);
	}

	public function getDataWarehouseAttribute($value)
	{
		return json_decode($value, true);
	}

	public function getFinalAddressAttribute($value)
	{
		return json_decode($value, true);
	}

	public function getAdminFeeAttribute($value)
	{
		if ($value > 0) {
			return $value;
		}

		if (in_array($this->paymentMethod->payment_channel, ['gopay', 'shopeepay', 'qris'])) {
			return floor($this->nominal * 0.02);
		} else {
			return 4000;
		}
	}
}
