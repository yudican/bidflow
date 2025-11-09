<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class SyncAccurateData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:accurate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync data from Accurate API every hour';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting data synchronization from Accurate API...');

        try {
            // URL dan token API Accurate
            $url = 'https://zeus.accurate.id/accurate/api/purchase-order/list.do?fields=id%2Cnumber%2C%2CapprovalStatus%2CavailableDownPayment%2Cbranch%2CcashDiscPercent%2CcashDiscount%2CcharField1%2CcharField10%2CcharField2%2CcharField3%2CcharField4%2CcharField5%2CcharField6%2CcharField7%2CcharField8%2CcharField9%2CcreatedByUserName%2Ccurrency%2CdateField1%2CdateField2%2Cdescription%2CdppAmount%2Cfob%2Cid%2CinclusiveTax%2ClastUpdate%2Cnumber%2CnumericField1%2CnumericField10%2CnumericField2%2CnumericField3%2CnumericField4%2CnumericField5%2CnumericField6%2CnumericField7%2CnumericField8%2CnumericField9%2CorderPrintedTime%2CpaymentTerm%2CprintedByUser%2Crate%2CshipDate%2Cshipment%2Cstatus%2CstatusName%2Ctax1Amount%2Ctax2Amount%2Ctax3Amount%2Ctax4Amount%2Ctaxable%2CtotalAmount%2CtotalDownPayment%2CtotalDownPaymentUsed%2CtotalExpense%2CtransDate%2Cvendor';
            $token = 'Bearer aat.NTA.eyJ2IjoxLCJ1Ijo4MDQ5MzQsImQiOjE1OTYwNjQsImFpIjo1MTk2MCwiYWsiOiIyMTQxMmMzNS0wYmI2LTRiMDgtOWY4Mi03YjJhNzg0NDcwNzgiLCJhbiI6Ik9SQ0EgRkxJTUdST1VQIiwiYXAiOiI3MzU3ZTZjNC0xOGJmLTRiYjUtYTM5My05OTVjNWViZGIyYzQiLCJ0IjoxNzM3MDAzNzI5MzY3fQ.l95oW0P0BvFZlyg56v2mUMRryVBGJfpKO82FZQiiSRAhTyE6Tfrgwgc4LOqaK0VgDsAgkbANdJGD8bGJLsIYzct82oNjGcS+kbbYy8O3CKi0BUATKgy2JGBJ5KOun6Wq0WQE0e7e0S+LRytI+FK8FubdFOjZREsbdCl3Z9aoIVrYu57MhB7TIMREaQ2z1LtBExdXBVM6jrg=.Y4Wpfa+BUAvelo9jGxoVcsXZh91ny7ASrcR0PVPMyJc';

            // Panggil API
            $response = Http::withToken($token)->get($url);

            if ($response->successful()) {
                $data = $response->json()['d'] ?? []; // Ambil data dari response

                // Simpan data ke database
                foreach ($data as $item) {
                    DB::table('tbl_purchase_order_accurate')->updateOrInsert(
                        ['id' => $item['id']], // Primary key untuk data unik
                        [
                            'number' => $item['number'],
                            'approvalStatus' => $item['approvalStatus'],
                            'totalAmount' => $item['totalAmount'],
                            'vendor' => $item['vendor'],
                            'branch' => $item['branch'],
                            'createdByUserName' => $item['createdByUserName'],
                            'transDate' => $item['transDate'],
                            'lastUpdate' => $item['lastUpdate'],
                            'status' => $item['status'],
                        ]
                    );
                }

                $this->info('Data synchronized successfully.');
            } else {
                $this->error('Failed to fetch data from Accurate API.');
                $this->error('Response: ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('An error occurred: ' . $e->getMessage());
        }
    }
}
