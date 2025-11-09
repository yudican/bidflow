<?php

namespace App\Http\Controllers\Spa;

use App\Jobs\SubmitSIGpQueue;
use App\Exports\ContactExport;
use App\Http\Controllers\Controller;
use App\Exports\ContactSkuExport;
use App\Models\AddressUser;
use App\Models\Cases;
use App\Models\Company;
use App\Models\CompanyAccount;
use App\Models\Contact;
use App\Models\ContactDownline;
use App\Models\OrderLead;
use App\Models\OrderManual;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\TransactionAgent;
use App\Models\User;
use App\Models\UserPoint;
use App\Models\RedeemPoint;
use App\Models\UserVoucher;
use App\Models\OrderSubmitLog;
use App\Models\OrderSubmitLogDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ContactImport;
use App\Jobs\ImportContactQueue;
use App\Jobs\ImportSalesOrderQueue;
use App\Models\LogError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    public function index($user_id = null)
    {
        return view('spa.spa-index');
    }

    public function listContact(Request $request)
    {
        $search = $request->search;
        $roles = $request->roles;
        $status = $request->status;
        $createdBy = $request->createdBy;
        $user = auth()->user();
        $role = $user->role->role_type;
        $contact =  User::query();
        if ($search) {
            $contact->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
                $query->orWhere('uid', 'like', "%$search%");
                $query->orWhere('email', 'like', "%$search%");
                $query->orWhereHas('roles', function ($query) use ($search) {
                    $query->where('role_name', 'like', "%$search%");
                });
            });
        }
        if ($roles) {
            $contact->whereHas('roles', function ($query) use ($roles) {
                $query->whereIn('role_id', $roles);
            });
        }

        if ($status) {
            $contact->whereIn('status', $status);
        }

        if ($createdBy) {
            $contact->where('created_by', $createdBy);
        }

        if (!in_array($role, ['superadmin', 'adminsales', 'leadwh', 'leadsales', 'admin', 'finance'])) {
            $contact->where('created_by', $user->id);
        }

        if (in_array($role, ['leadcs'])) {
            $contact->orWhereHas('roles', function ($query) {
                $query->whereIn('role_type', ['mitra', 'member', 'subagent']);
            });
        }

        $contacts = $contact->orderBy('users.created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => tap($contacts, function ($contacts) {
                return $contacts->getCollection()->transform(function ($item) {
                    return [
                        'id' => $item['id'],
                        'uid' => $item['uid'],
                        'name' => $item['name'],
                        'email' => $item['email'],
                        'telepon' => $item['telepon'],
                        'created_by_name' => $item['created_by_name'],
                        'role' => $item['role'],
                        'created_at' => $item['created_at'],
                        'amount_detail' => $item['amount_detail'],
                    ];
                });
            }),
            'message' => 'List Contact'
        ]);
    }

    public function syncGpData()
    {
        $client = new Client();
        $company = CompanyAccount::find(auth()->user()->company_id || 1, ['account_code']);
        try {
            $response = $client->request('GET', getSetting('GP_URL') . '/MasterData/GetCustomerAll', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . getSetting('GP_TOKEN_' . $company->account_code),
                ],
            ]);

            $responseJSON = json_decode($response->getBody(), true);
            if (in_array($responseJSON['code'], [200, 201])) {
                foreach ($responseJSON['data'] as $key => $value) {
                    $data = [
                        'uid' => $value['id'],
                        'name' => $value['name'],
                        'email' => $value['email'] ?? $value['id'] . '@fis.dev',
                        'telepon' => $value['telepon'],

                    ];
                    $user = User::create(['uid' => $value['id']], $data);
                    $role = Role::where('role_type', 'member')->first();
                    $user->brands()->sync(1);
                    $user->teams()->sync(1, ['role' => $role->role_type]);
                    $user->roles()->sync($user->id);
                }
            }
        } catch (\Throwable $th) {
            LogError::updateOrCreate(['id' => 1], [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'action' => 'Get contact GP',
            ]);
        }
    }

    public function syncGp(Request $request, $user_id)
    {
        $client = new Client();
        try {
            $response = $client->request('POST', getSetting('GP_URL') . '/MasterData/SyncCustomerFlag', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . getSetting('GP_TOKEN'),
                ],
                'body' => json_encode([
                    'CUSTOMER_ID' => $request->uid
                ])
            ]);

            $responseJSON = json_decode($response->getBody(), true);
            setSetting('response_sync', json_encode($responseJSON));
            $user = User::find($user_id);
            $user->update(['sync_gp' => 1]);
            return response()->json([
                'message' => 'Sync Gp Berhasil'
            ]);
        } catch (\Throwable $th) {
            LogError::updateOrCreate(['id' => 1], [
                'message' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'action' => 'error sync',
            ]);

            return response()->json([
                'message' => 'Sync Gp Gagal'
            ], 400);
        }
    }

    public function detailContact($user_id)
    {
        $contact =  User::with(['addressUsers', 'company', 'brand', 'brands', 'userCreated', 'company.businessEntity', 'contactDownlines', 'checkbook'])->where('id', $user_id)->first();

        // total debt
        $total_order_lead = 0;
        $total_order_manual = 0;
        $total_invoice = 0;
        $total_amount = 0;
        $list_order = [];
        $debt_order_leads = OrderLead::whereContact($user_id)->where('status', 2)->get();
        $debt_order_manuals = OrderManual::whereContact($user_id)->where('status', 2)->get();

        $list_order = array_merge($debt_order_leads->toArray(), $debt_order_manuals->toArray());

        $total_invoice = $debt_order_leads->count() + $debt_order_manuals->count();
        $total_amount = $debt_order_leads->sum('amount_billing_approved') + $debt_order_manuals->sum('amount_billing_approved');
        $total_order_lead = $debt_order_leads->sum('amount');
        $total_order_manual = $debt_order_manuals->sum('amount');
        $total_debt = $total_order_lead + $total_order_manual;
        $poin_get = UserPoint::where('user_id', $user_id)->sum('point');
        $poin_use = RedeemPoint::where('user_id', $user_id)->sum('point');
        $contact['total_poin'] = $poin_get - $poin_use;
        $contact['referal_list'] = User::where('referal_by', $user_id)->get();
        $contact['voucher_list'] = UserVoucher::leftjoin('vouchers', 'vouchers.id', 'voucher_id')->where('user_id', $user_id)->get();
        $contact['redeem_point'] = UserPoint::where('user_id', $user_id)->get();
        return response()->json([
            'status' => 'success',
            'data' => $contact,
            'order_lead' => [
                'list' => $list_order,
                'total_debt' => $total_debt,
                'total_invoice_active' => $total_invoice,
                'total_invoice_amount' => $total_amount,
            ]
        ]);
    }

    public function contactTransaction($user_id)
    {
        $user = User::find($user_id);
        $role = $user->role->role_type;
        if (in_array($role, ['agent', 'subagent'])) {
            $transaction =  TransactionAgent::with(['user', 'paymentMethod'])->where('user_id', $user->id)->whereIn('status', [1, 2, 3, 7])->whereIn('status_delivery', [1, 2, 3, 21])->orderBy('created_at', 'desc')->get();
            return response()->json([
                'status' => 'success',
                'data' => $transaction
            ]);
        }

        $transaction =  Transaction::with(['user', 'paymentMethod'])->where('user_id', $user->id)->whereIn('status', [1, 2, 3, 7])->whereIn('status_delivery', [1, 2, 3, 21])->orderBy('created_at', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $transaction
        ]);
    }

    public function contactTransactionHistory($user_id)
    {
        $user = User::find($user_id);
        $role = $user->role->role_type;
        if (in_array($role, ['agent', 'subagent'])) {
            $transaction =  TransactionAgent::with(['user', 'paymentMethod'])->where('user_id', $user->id)->whereIn('status_delivery', [4, 5, 6, 7])->whereIn('status', [4, 5, 6, 7])->orderBy('created_at', 'desc')->get();
            return response()->json([
                'status' => 'success',
                'data' => $transaction
            ]);
        }

        $transaction =  Transaction::with(['user', 'paymentMethod'])->where('user_id', $user->id)->whereIn('status_delivery', [4, 5, 6, 7])->whereIn('status', [4, 5, 6, 7])->orderBy('created_at', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $transaction
        ]);
    }

    public function contactHistoryCase($user_id)
    {
        $cases = Cases::with(['contactUser', 'createdUser', 'typeCase', 'priorityCase', 'sourceCase', 'categoryCase', 'statusCase'])->where('contact', $user_id)->orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $cases
        ]);
    }

    public function getVoucher(Request $request)
    {
        $user_id = $request->user_id;
        $vouchers = UserVoucher::with('voucher')->where('user_id', $user_id)->paginate(10);
        return response()->json([
            'status' => 'success',
            'data' => $vouchers
        ]);
    }

    public function updateRateLimit(Request $request, $user_id)
    {
        $user = User::find($user_id);

        if ($user) {
            $user->update(['rate_limit' => $request->rate_limit]);
            return response()->json([
                'status' => 'success',
                'message' => 'Rate Limit Berhasil Disimpan'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Contact Tidak Ditemukan'
        ], 400);
    }

    public function updateProfileContact(Request $request)
    {
        $user = User::find($request->user_id);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->telepon = formatPhone($request->telepon);
        $user->gender = $request->gender;
        $user->bod = $request->bod;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        if ($request->profile_image) {
            if (!$request->hasFile('profile_image')) {
                return response()->json([
                    'error' => true,
                    'message' => 'File not found',
                    'status_code' => 400,
                ], 400);
            }
            $file = $request->file('profile_image');
            if (!$file->isValid()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Image file not valid',
                    'status_code' => 400,
                ], 400);
            }

            $file = Storage::disk('s3')->put('upload/user', $request->profile_image, 'public');
            if ($user->profile_photo_path) {
                if (Storage::disk('s3')->exists($user->profile_photo_path)) {
                    Storage::disk('s3')->delete($user->profile_photo_path);
                }
            }

            $user->profile_photo_path = $file;
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Update Profile Success'
        ]);
    }

    public static function generateReferralCode()
    {
        $referralCode = Str::upper(Str::random(5));
        $isDuplicate = User::where('referal_code', $referralCode)->exists();
        while ($isDuplicate) {
            $referralCode = Str::upper(Str::random(5));
            $isDuplicate = User::where('referal_code', $referralCode)->exists();
        }

        return $referralCode;
    }

    public function storeContact(Request $request)
    {
        $checkmail = User::where('email', $request->email);
        if ($request->user_id) {
            $checkmail->where('id', '!=', $request->user_id);
        }
        if ($checkmail->first()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email sudah terdaftar'
            ], 400);
        }

        // check company email
        // if ($request->company_email) {
        //     $checkCompanyEmail = Company::where('email', $request->company_email);
        //     if ($request->user_id) {
        //         $checkCompanyEmail->where('user_id', '!=', $request->user_id);
        //     }
        //     if ($checkCompanyEmail->first()) {
        //         return response()->json([
        //             'status' => 'error',
        //             'message' => 'Company Email sudah terdaftar',
        //             'type' => 'company_email'
        //         ], 400);
        //     }
        // }

        // check company name
        // if ($request->company_name) {
        //     $checkCompanyName = Company::where('name', 'like', '%' . $request->company_name . '%');
        //     if ($request->user_id) {
        //         $checkCompanyName->where('user_id', '!=', $request->user_id);
        //     }

        //     if ($checkCompanyName->first()) {
        //         return response()->json([
        //             'status' => 'error',
        //             'message' => 'Company name sudah terdaftar',
        //             'type' => 'company_name'
        //         ], 400);
        //     }
        // }

        $brand_id = json_decode($request->brand_id, true);
        if (is_array($brand_id)) {
            if (count($brand_id) == 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Brand tidak boleh kosong'
                ], 400);
            }
        }

        try {
            DB::beginTransaction();
            $role = Role::find($request->role_id);
            $sales_channel = json_decode($request->sales_channel, true);

            $dataContact = [
                'name'  => $request->name,
                'uid' => $request->uid,
                'email'  => $request->email,
                'telepon'  => formatPhone($request->telepon),
                'gender'  => $request->gender,
                'bod'  => $request->bod,
                'appendix'  => $request->appendix,
                'checkbook_id'  => $request->checkbook_id,
                'brand_id'  => isset($brand_id[0]) ? $brand_id[0] : 1,
                'created_by' => auth()->user()->id,
                'sales_channel'  => implode(',', $sales_channel),
                'referal_code' => $this->generateReferralCode()
            ];
            if (!$request->user_id) {
                $dataContact['password'] = Hash::make('admin123');
            }
            $user = User::updateOrCreate(['id'  => $request->user_id], $dataContact);
            $user->brands()->sync($brand_id);
            $user->teams()->sync(1, ['role' => $role->role_type]);
            $user->roles()->sync($request->role_id);

            $data = [
                'name'  => $request->company_name ?? null,
                'address'  => $request->company_address ?? null,
                'nik'  => $request->nik ?? null,
                'npwp'  => $request->npwp ?? null,
                'npwp_name'  => $request->npwp_name ?? null,
                'email'  => $request->company_email ?? null,
                'phone'  => $request->company_telepon ? formatPhone($request->company_telepon) : null,
                'brand_id'  => isset($brand_id[0]) ? $brand_id[0] : 1,
                'owner_name'  => $request->owner_name ?? null,
                'owner_phone'  => $request->owner_phone ? formatPhone($request->owner_phone) : null,
                'pic_name'  => $request->pic_name ?? null,
                'pic_phone'  => $request->pic_phone ? formatPhone($request->pic_phone) : null,
                'status'  => 1,
                'user_id' => $user->id,
                'business_entity' => $request->business_entity ?? null,
                'layer_type' => $request->layer_type ?? null,
                'nib' => $request->nib ?? null,
                'need_faktur' => $request->need_faktur ?? null,
            ];

            if ($request->file_nib) {
                $file = $this->uploadImage($request, 'file_nib');
                $data['file_nib'] = $file;
            }

            Company::updateOrCreate(['user_id' => $user->id], $data);

            // Trigger orca
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->send('POST', 'https://brcd-testing.flimty.co/api/fis/trigger/users', [
                'body' => '{}', // Kirim JSON kosong sebagai string
            ]);

            DB::commit();
            return response()->json([
                'status' => 'error',
                'message' => 'Contact berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => $th->getMessage(),
                'status' => 'error',
                'message' => 'Contact gagal disimpan',
            ], 400);
        }
    }

    public function getUserCreatedBy(Request $request)
    {
        $user = auth()->user();
        $role = $user->role->role_type;
        $users = User::where('name', 'like', '%' . $request->search . '%');
        if (!in_array($role, ['superadmin', 'adminsales', 'leadwh', 'leadsales'])) {
            $users->where('created_by', $user->id);
        }

        $userData = $users->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'nama' => $item->name
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $userData
        ]);
    }

    public function blackListUser($user_id)
    {
        $user = User::find($user_id);
        $user->status = $user->status == 1 ? 0 : 1;
        $user->save();
        $status = $user->status == 1 ? 'aktifkan' : 'nonaktifkan';
        return response()->json([
            'status' => 'success',
            'message' => "User berhasil di $status"
        ]);
    }

    public function saveAddress(Request $request)
    {
        $dataAddress = [
            'type'  => $request->type,
            'nama'  => $request->nama,
            'alamat'  => $request->alamat,
            'provinsi_id'  => $request->provinsi_id,
            'kabupaten_id'  => $request->kabupaten_id,
            'kecamatan_id'  => $request->kecamatan_id,
            'kelurahan_id'  => $request->kelurahan_id,
            'kodepos'  => $request->kodepos,
            'telepon'  => formatPhone($request->telepon),
            'user_id'  => $request->user_id,
        ];

        $dataAddress['is_default'] = 0;
        $address = AddressUser::where('user_id', $request->user_id)->count();
        if ($address == 1) {
            $dataAddress['is_default'] = 1;
        } else {
            $address = AddressUser::where('id', $request->address_id)->first(['is_default']);
            $dataAddress['is_default'] = $address->is_default ?? 0;
        }

        AddressUser::updateOrCreate(['id' => $request->address_id], $dataAddress);

        return response()->json([
            'status' => 'success',
            'message' => 'Alamat berhasil disimpan'
        ]);
    }

    public function setDefaultAddress(Request $request)
    {
        $addresses = AddressUser::where('user_id', $request->user_id)->get();
        foreach ($addresses as $key => $address) {
            if ($address->id == $request->address_id) {
                $address->update(['is_default' => 1]);
            } else {
                $address->update(['is_default' => 0]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Alamat berhasil disimpan'
        ]);
    }

    public function getMemberDownline(Request $request, $user_id)
    {
        $search = $request->search;
        $downline_id = ContactDownline::where('user_id', $user_id)->pluck('company_id')->toArray();
        $user = User::query();
        if ($search) {
            $user->where('name', 'like', "%$search%");
        }
        $user->whereHas('company', function ($query) use ($downline_id) {
            $query->whereNotIn('id', $downline_id)->where('layer_type', 'sub-distributor');
        });

        $data = $user->limit($request->limit ?? 5)->get()->map(function ($item) {
            return [
                'id' => $item->company->id,
                'nama' => $item->name
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    public function saveMember(Request $request, $user_id)
    {
        ContactDownline::updateOrCreate([
            'user_id' => $user_id,
            'company_id' => $request->company_id
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Contact berhasil disimpan'
        ]);
    }

    public function deleteMember($downline_id)
    {
        $member = ContactDownline::find($downline_id);
        $member->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Contact berhasil dihapus'
        ]);
    }

    public function deleteContact($address_id)
    {
        $contact = AddressUser::find($address_id);
        $contact->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Contact berhasil dihapus'
        ]);
    }

    public function export()
    {
        $file_name = 'FIS-Data_Contact-' . date('d-m-Y') . '.xlsx';

        // Excel::store(new ContactExport(null), $file_name, 's3', null, [
        //     'visibility' => 'public',
        // ]);
        // return response()->json([
        //     'status' => 'success',
        //     'data' => Storage::disk('s3')->url($file_name),
        //     'message' => 'List Convert'
        // ]);
        Excel::store(new ContactExport(null), $file_name, 'public');
        return response()->json([
            'status' => 'success',
            'data' => asset('storage/' . $file_name),
            'message' => 'List Notification'
        ]);
    }

    public function importOrder(Request $request)
    {
        $user = auth()->user();
        $submitLog = OrderSubmitLog::create([
            'submited_by' => $user?->id,
            'type_si' => 'import-so-' . $request->type,
            'vat' => 0,
            'tax' => 0,
            'ref_id' => null,
            'company_id' => $user->company_id
        ]);
        $file = $request->file('file');
        if (!$request->hasFile('file')) {
            return response()->json([
                'data' => [],
                'message' => 'File tidak diupload'
            ], 400);
        }


        if (!$file->isValid()) {
            return response()->json([
                'data' => [],
                'message' => 'File tidak valid'
            ], 400);
        }

        $fileUpload = $this->uploadFile($request, 'file', $submitLog->id);
        if ($submitLog) {
            try {
                DB::beginTransaction();

                $data = Excel::toArray([], $file);
                $headers = $data[0][0];

                // Ambil data setelah header
                $rows = array_slice($data[0], 1);

                // Pemetaan data berdasarkan header
                $mappedData = [];
                foreach ($rows as $row) {
                    // Check if the row is not empty and contains the required columns
                    if (!empty(array_filter($row))) {
                        // Pemetaan hanya jika cs_code (or any other critical column) exists
                        if (isset($row[1]) && !empty($row[1])) {
                            $mappedRow = [];

                            foreach ($headers as $key => $header) {
                                // Assign the row data to the corresponding header key
                                $mappedRow[$header] = $row[$key] ?? null; // Use null for missing values
                            }

                            $codeSO = $mappedRow['cs_code'];

                            // Jika cs_code sudah ada, tambahkan item ke dalam array 'items'
                            if (isset($mappedData[$codeSO])) {
                                $mappedData[$codeSO]['items'][] = $mappedRow;
                            } else {
                                // Jika cs_code belum ada, buat array baru dengan 'items' berisi item pertama
                                $mappedData[$codeSO] = array_merge($mappedRow, ['items' => [$mappedRow]]);
                            }
                        }
                    }
                }

                // Mengubah array associatif menjadi array numerik jika diperlukan
                $result = array_values($mappedData);

                removeSetting('import-so-' . $request->type . '-' . $user->id);
                removeSetting('import-so-' . $request->type . '-' . $user->id . '-progress');
                setSetting('import-so-' . $request->type . '-' . $user->id, count($result));
                // setSetting('import-so-' . $submitLog->id, json_encode($result));

                ImportContactQueue::dispatch($submitLog->id, $request->type, $fileUpload)->onQueue('queue-import');
                foreach ($result as $key => $item) {
                    // Only pass necessary data
                    ImportSalesOrderQueue::dispatch($item, $request->type, $key, $user->id, $submitLog->id, $fileUpload)->onQueue('queue-import');
                }

                DB::commit();
                return response()->json([
                    'data' => [],
                    'message' => 'success'
                ]);
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json([
                    'data' => [],
                    'message' => 'gagal import data',
                    'error' => $th->getMessage(),
                ], 400);
            }
        }

        return response()->json([
            'data' => [],
            'message' => 'gagal import data 2'
        ], 400);
    }

    public function uploadFile($request, $path = 'file', $reff_id)
    {
        if (!$request->hasFile($path)) {
            OrderSubmitLogDetail::updateOrCreate([
                'order_submit_log_id' => $reff_id,
                'order_id' => $reff_id
            ], [
                'order_submit_log_id' => $reff_id,
                'order_id' => $reff_id,
                'status' => 'failed',
                'error_message' => 'File Tidak Ditemukan'
            ]);
        }
        $file = $request->file($path);
        if (!$file->isValid()) {
            OrderSubmitLogDetail::updateOrCreate([
                'order_submit_log_id' => $reff_id,
                'order_id' => $reff_id
            ], [
                'order_submit_log_id' => $reff_id,
                'order_id' => $reff_id,
                'status' => 'failed',
                'error_message' => 'File Tidak Valid'
            ]);
        }
        // Get original filename and prepare path
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = pathinfo($originalName, PATHINFO_FILENAME);

        // Create unique filename while preserving original name
        $uniqueFilename = $filename . '_' . time() . '.' . $extension;

        // Upload to S3 with original filename
        $uploaded = Storage::disk('s3')->putFileAs(
            $path,
            $file,
            $uniqueFilename,
            'public'
        );
        // $file = Storage::disk('s3')->put($path, $request[$path], 'public');
        return $uploaded;
    }

    public function importOld(Request $request)
    {
        try {
            $request->validate([
                'attachment' => 'required|mimes:xlsx,xls',
            ]);

            $file = $request->file('attachment');

            // $exec = Excel::import(new ContactImport(), $file);

            $import = new ContactImport();
            Excel::import($import, $file);

            // $rowsWithLocationNotMatch = @$import->getRowsWithLocationNotMatch();

            // if (!empty($rowsWithLocationNotMatch)) {
            //     return response()->json([
            //         'status' => 'failed',
            //         'data' => $rowsWithLocationNotMatch,
            //         'message' => 'Input lokasi tidak sesuai',
            //     ], 200);
            // }

            // $rowsWithUserExist = @$import->getRowsWithUserExist();

            // if (!empty($rowsWithUserExist)) {
            //     return response()->json([
            //         'status' => 'failed',
            //         'data' => $rowsWithUserExist,
            //         'message' => 'Email sudah terdaftar',
            //     ], 200);
            // }

            // $rowsWithCodeSpace = @$import->getRowsWithCodeSpace();

            // if (!empty($rowsWithCodeSpace)) {
            //     return response()->json([
            //         'status' => 'failed',
            //         'data' => $rowsWithCodeSpace,
            //         'message' => 'CS Code tidak boleh memiliki spasi',
            //     ], 200);
            // }

            return response()->json(['message' => 'Data berhasil diimpor'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage()], 500);
        }
    }

    public function submitGP(Request $request)
    {
        if ($request->items[0] == "") {
            return response()->json([
                'message' => 'Data customer code masih kosong',
                'status' => 'failed',
            ]);
        }
        $contact = User::whereIn('id', $request->items)->get();
        $company = CompanyAccount::find(auth()->user()->company_id, ['account_code']);
        $header = [];
        $line = [];
        $data_submit = [];
        foreach ($contact as $key => $item) {
            if ($item->status_gp == "submited") {
                return response()->json([
                    'message' => 'Data contact sudah pernah di submit',
                    'status' => 'failed',
                ]);
            }

            $data_submit[$item->uid]['headers'][] =
                [
                    'ID' => $item->uid,
                    'NAME' => $item->name,
                    'EMAIL' => $item->email,
                    'TELEPON' => $item->telepon,
                ];
        }

        $submitLog = OrderSubmitLog::create([
            'submited_by' => auth()->user()->id,
            'type_si' => 'customer-contact',
            'vat' => '',
            'tax' => '',
        ]);

        $body = [];
        $ref_ids = [];
        foreach ($data_submit as $key => $value) {
            $ref_ids[] = $key;
            $body[] = json_encode([
                'header' => $value['headers']
            ]);
        }

        $isUseQueue = getSetting('GP_SUBMIT_QUEUE');
        foreach ($body as $key => $body_value) {
            if ($isUseQueue) {
                SubmitSIGpQueue::dispatch($request->type, $submitLog->id, $body_value, $request->ids)->onQueue('queue-log');
            } else {
                $order_log_id = $submitLog->id;
                $submitLog->update(['ref_id' => $ref_ids[$key]]);
                setSetting('GP_BODY_' . $order_log_id, $body_value);

                try {
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => getSetting('GP_URL') . '/Customer/CustomerEntry',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => $body_value,
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json',
                            'Authorization: Bearer ' . getSetting("GP_TOKEN_" . $company->account_code)
                        ),
                    ));

                    $response = curl_exec($curl);

                    curl_close($curl);

                    $responseJSON = json_decode($response, true);
                    // check is string
                    if (!$responseJSON && is_string($response)) {
                        setSetting('GP_RESPONSE_ERROR_CONTACT_' . $order_log_id, $response);
                        foreach ($contact as $key => $purchase) {
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id
                            ], [
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id,
                                'status' => 'failed',
                                'error_message' => $response
                            ]);
                        }

                        return;
                    }

                    // Check if any error occured
                    if (curl_errno($curl)) {
                        setSetting('GP_RESPONSE_ERROR_CONTACT_' . $order_log_id, curl_error($curl));
                        foreach ($contact as $key => $purchase) {
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id
                            ], [
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id,
                                'status' => 'failed',
                                'error_message' => curl_error($curl)
                            ]);
                        }

                        return;
                    }

                    setSetting('GP_RESPONSE_CONTACT_' . $order_log_id, json_encode($responseJSON));
                    if (isset($responseJSON['code'])) {
                        if ($responseJSON['code'] == 200) {
                            foreach ($contact as $key => $invoice) {
                                if (!$invoice->status_gp) {
                                    $invoice->update(['status_gp' => 'submited']);
                                }

                                OrderSubmitLogDetail::updateOrCreate([
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $invoice->id
                                ], [
                                    'order_submit_log_id' => $order_log_id,
                                    'order_id' => $invoice->id,
                                    'status' => 'success',
                                    'error_message' => null
                                ]);
                            }

                            return;
                        }
                    }

                    if (isset($responseJSON['desc'])) {
                        foreach ($contact as $key => $purchase) {
                            OrderSubmitLogDetail::updateOrCreate([
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id
                            ], [
                                'order_submit_log_id' => $order_log_id,
                                'order_id' => $purchase->id,
                                'status' => 'failed',
                                'error_message' => $responseJSON['desc']
                            ]);
                        }
                    }
                } catch (ClientException $e) {
                    $response = $e->getResponse();
                    $responseBodyAsString = $response->getBody()->getContents();
                    setSetting('GP_RESPONSE_ERROR_CONTACT_' . $order_log_id, $responseBodyAsString);
                    foreach ($contact as $key => $purchase) {
                        OrderSubmitLogDetail::updateOrCreate([
                            'order_submit_log_id' => $order_log_id,
                            'order_id' => $purchase->id
                        ], [
                            'order_submit_log_id' => $order_log_id,
                            'order_id' => $purchase->id,
                            'status' => 'failed',
                            'error_message' => $responseBodyAsString
                        ]);
                    }
                }
            }
        }


        return response()->json([
            'message' => 'Data sedang dalam proses submit',
            'status' => 'success',
        ]);
    }

    public function uploadImage($request, $path)
    {
        if (!$request->hasFile($path)) {
            return response()->json([
                'error' => true,
                'message' => 'File not found',
                'status_code' => 400,
            ], 400);
        }
        $file = $request->file($path);
        if (!$file->isValid()) {
            return response()->json([
                'error' => true,
                'message' => 'Image file not valid',
                'status_code' => 400,
            ], 400);
        }
        $file = Storage::disk('s3')->put('upload/contact', $request[$path], 'public');
        return $file;
    }
}
