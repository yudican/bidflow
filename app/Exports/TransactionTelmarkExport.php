<?php

namespace App\Exports;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TransactionTelmarkExport implements FromView, ShouldAutoSize
{
    protected $request;
    protected $title;
    public function __construct($request, $title = 'ExportConverData')
    {
        $this->request = $request;
        $this->title = $title;
    }

    public function view(): View
    {
        $request = $this->request;
        $search = $request->search;
        $created_at = $request->created_at;
        $status = $request->status;
        $label_status = $request->status_label;
        $ekspedisi = $request->ekspedisi;
        $status_delivery = $request->status_delivery;
        $payment_method = $request->payment_method_id;
        $type = $request->type;
        $action = $request->action;
        $user_create = $request->user_create;
        $user = auth()->user();
        $role = $user->role;
        $stage = $request->stage;
        $transaction = Transaction::query()->whereType($type);

        if ($action == 'withdrawal') {
            if ($role->role_type == 'ahligizi') {
                $transaction->whereHas('userCreated', function ($query) {
                    return $query->whereHas('roles', function ($query) {
                        return $query->where('role_type', 'ahligizi');
                    });
                });
            }
            $transaction->where('status_delivery', 4)->where('status', 7);
        } else {
            if ($stage == 'new-order') {
                if ($role->role_type == 'ahligizi') {
                    $transaction->whereHas('userCreated', function ($query) {
                        return $query->whereHas('roles', function ($query) {
                            return $query->where('role_type', 'ahligizi');
                        });
                    });
                }
                $transaction->where('status', 0)->where('status_delivery', 0);
            } else {
                // stage
                // stage 1 - Waiting for payment
                if ($stage === 'waiting-payment') {
                    $transaction->where('status', 1);
                }

                // stage 2 - Waiting for confirmation
                if ($stage === 'waiting-confirmation') {
                    $transaction->where('status', 2);
                }

                // stage 3 - Payment confirmed
                if ($stage === 'confirm-payment') {
                    $transaction->where('status', 3);
                }

                // stage 4 - On Process
                if ($stage === 'on-process') {
                    $transaction->where('status', 7)->where('status_delivery', 1);
                }

                // stage 5 - Ready to ship
                if ($stage === 'ready-to-ship') {
                    $transaction->whereIn('status', [3, 7])->where('status_delivery', 21);
                }

                // stage 6 - On Delivery
                if ($stage === 'on-delivery') {
                    $transaction->where('status_delivery', 3);
                }

                // stage 7 - Delivered
                if ($stage === 'delivered') {
                    $transaction->where('status_delivery', 4)->where('status', 7);
                }

                if ($stage === 'report-transaction') {
                    // $transaction->where('status_delivery', 4)->where('status', 7)->whereNull('completed_at');
                }

                // stage 8 - Cancelled
                if ($stage === 'cancelled') {
                    $transaction->whereIn('status', [4, 6]);
                }
            }
        }

        if ($search) {
            $transaction->where(function ($subquery) use ($search) {
                $subquery->where('id_transaksi', 'like', "%$search%");
                $subquery->orWhereHas('user', function ($query) use ($search) {
                    $query->where('users.name', 'like', "%$search%");
                });
                $subquery->orWhereHas('userCreated', function ($query) use ($search) {
                    $query->where('users.name', 'like', "%$search%");
                });
            });
        }

        if ($label_status) {
            $final_status = $label_status == 10 ? 0 : $label_status;
            $transaction->where('status_label', $final_status);
        }

        if ($ekspedisi) {
            $transaction->whereHas('shippingType', function ($query) use ($ekspedisi) {
                $query->where('shipping_type_name', 'like', '%' . $ekspedisi . '%');
            });
        }


        // end stage
        if (in_array($role->role_type, ['agent', 'subagent'])) {
            $transaction->where('user_id', $user->id);
        }

        // end stage
        if (in_array($role->role_type, ['agent-telmark', 'agent-telmar', 'telmark-supervisor'])) {
            $transaction->where('user_create', $user->id);
        }

        if ($payment_method) {
            $transaction->where('payment_method_id', $payment_method);
        }

        if ($status) {
            $transaction->whereIn('status', $status);
        }

        if ($status_delivery) {
            $transaction->whereIn('status_delivery', $status_delivery);
        }

        if ($user_create) {
            if (in_array($role->role_type, ['agent-telmar', 'agent-telmark'])) {
                $transaction->where('user_create', $user->id);
            } else {
                $transaction->where('user_create', $user_create);
            }
        }

        if ($created_at) {
            // Assuming $created_at is an array with two elements: start date and end date
            $startDate = $created_at[0];
            $endDate = $created_at[1];

            $startDate = Carbon::parse($startDate)->format('Y-m-d');
            $endDate = Carbon::parse($endDate)->addDay(1)->format('Y-m-d');

            $transaction->whereBetween('created_at', [$startDate, $endDate]);
        }

        $transactions = $transaction->orderBy('created_at', 'desc')->get();
        $lead_data = [];
        foreach ($transactions as $key => $value) {
            $userData = $value->data_user;

            if (is_string($userData)) {
                $user = json_decode($userData);
            } else {
                $user = (object) $userData;
            }

            // merge value same value
            $lead_data[$key]['id_transaksi']       = $value->id_transaksi;
            $lead_data[$key]['payment_method_name']     = $value?->payment_method_name;
            $lead_data[$key]['shipping_method']     = $value?->shipping_type_name ?? '-';
            $lead_data[$key]['created_by'] = $user->name ?? '-';
            $lead_data[$key]['status'] = $value->final_status;
            $lead_data[$key]['created_date'] = formatTanggalIndonesia($value->created_at);
            $lead_data[$key]['product'] = $value->transactionDetail()->get()->map(function ($item) {
                return [
                    'product_name' => @$item->product_name,
                    'sku' => @$item->productVariant?->sku,
                    'price' => $item->price,
                    'qty' => $item->qty,
                    'subtotal' => $item->subtotal,
                ];
            });
            // dd($lead_data[$key]['created_by']); die;

        }
        return view('export.transaction-telmark', [
            'data' => $lead_data,
        ]);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
}
