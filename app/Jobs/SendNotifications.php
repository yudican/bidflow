<?php

namespace App\Jobs;

use App\Models\Brand;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;
        $view = isset($data['view']) ? $data['view'] : 'email-template';
        $transaction = null;
        $brand = null;
        if (isset($data['transaction_id'])) {
            $transaction = Transaction::find($data['transaction_id']);
        }
        if ($transaction && $transaction->data_brand) {
            $brand =  is_array($transaction->data_brand) ? $transaction->data_brand : json_decode($transaction->data_brand, true);
        }

        $defaultBrand = Brand::find(8);

        $cc = isset($data['cc']) ? $data['cc'] : [];
        $body = isset($data['body']) ? $data['body'] : '';
        $date = isset($data['date']) ? $data['date'] : '';
        $type = isset($data['type']) ? $data['type'] : '';
        $email = isset($data['email']) ? $data['email'] : null;
        $actionUrl = isset($data['actionUrl']) ? $data['actionUrl'] : '';
        $invoiceId  = isset($data['invoice']) ? $data['invoice'] : '';
        $price = isset($data['price']) ? $data['price'] : '';
        $payment_method = isset($data['payment_method']) ? $data['payment_method'] : '';
        $title = isset($data['title']) ? $data['title'] : '';
        $invoice = $transaction ? $transaction->id_transaksi : $invoiceId;
        $brand_name = $transaction ? $transaction->brand->name : $defaultBrand->name;
        $brand_email = $transaction ? $transaction->brand->email : $defaultBrand->email;
        $brand_phone = $transaction ? $transaction->brand->phone : $defaultBrand->phone;
        $brand_logo = $transaction ? getImage($transaction->brand->logo) : getImage($defaultBrand->logo);

        if ($brand) {
            $brand_name = $brand['brand_name'];
            $brand_email = $brand['brand_email'];
            $brand_phone = $brand['brand_phone'];
            $brand_logo = $brand['brand_logo'];
        }

        setSetting('EMAIL_SEND_email', $email);
        if ($email) {
            try {
                Mail::send('email.crm.' . $view, [
                    'body' => $body,
                    'date' => $date,
                    'type' => $type,
                    'actionUrl' => $actionUrl,
                    'invoice' => $invoice,
                    'price' => $price,
                    'payment_method' => $payment_method,
                    'transaction' => $transaction,
                    'brand' => $brand ?? $defaultBrand,
                    'brand_name' => $brand_name,
                    'brand_email' => $brand_email,
                    'brand_phone' => $brand_phone,
                    'brand_logo' => $defaultBrand->logo ? getImage($defaultBrand->logo) : $brand_logo,
                ], function ($message) use ($data, $cc, $title, $email) {
                    $message->from(getSetting('MAIL_FROM_ADDRESS'), getSetting('MAIL_FROM_NAME'));
                    $message->to($email);
                    if (count($cc) > 0) {
                        $message->cc($cc);
                    }
                    $message->subject($title);
                });

                setSetting('EMAIL_SEND_email_success', $email);
            } catch (\Throwable $th) {
                setSetting('EMAIL_SEND_email_error', $th->getMessage());
                //throw $th;
            }
        }
    }
}
