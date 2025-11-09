<?php

namespace App\Http\Livewire\Components;

use App\Jobs\GetGpTokenQueue;
use App\Models\CompanyAccount;
use App\Models\CompanyUser;
use App\Models\LogError;
use GuzzleHttp\Client;
use Livewire\Component;

class SwitchAccount extends Component
{
    public function render()
    {
        $company2 = CompanyAccount::where('status', 1)->first(['id']);
        $company = CompanyUser::where('user_id', auth()->user()->id)->first(['company_id']);
        return view('livewire.components.switch-account', [
            'account_id' => $company ? $company->company_id :  $company2->id
        ]);
    }

    public function handleSwitch($id)
    {
        if ($id > 0) {
            CompanyAccount::where('status', 1)->update(['status' => 0]);
            $account = CompanyAccount::find($id);
            $username = $account->account_code == '001' ? 'inv' : 'sa';
            $secretcode = $account->account_code == '001' ? 'PT. ANUGRAH INOVASI MAKMUR INDONESIA' : 'Flimty';
            $secretkey = $account->account_code == '001' ? 'uajQfPzUExgkNkD69UL5HE' : '4UhFUi3KyW7VBQ6Jeu9Mm';
            $clientcode = $account->account_code == '001' ? 'CLN00102' : 'CLN00084';

            if ($account->account_code == '001') {
                setSetting('GP_USERNAME_001', $username);
                setSetting('ETHIX_SECRETCODE_001', $secretcode);
                setSetting('ETHIX_SECRETKEY_001', $secretkey);
                setSetting('ETHIX_CLIENTCODE_001', $clientcode);
            } else {
                setSetting('GP_USERNAME_002', $username);
                setSetting('ETHIX_SECRETCODE_002', $secretcode);
                setSetting('ETHIX_SECRETKEY_002', $secretkey);
                setSetting('ETHIX_CLIENTCODE_002', $clientcode);
            }

            $account->update([
                'status' => $account->status == 1 ? 0 : 1
            ]);
            CompanyUser::updateOrCreate(['user_id' => auth()->user()->id], ['company_id' => $id, 'user_id' => auth()->user()->id]);
            $this->emit('handleSwitch', $id);
        } else {
            CompanyAccount::where('status', 1)->update(['status' => 0]);
            $account = CompanyAccount::find($id);
            $username = $account->account_code == '001' ? 'inv' : 'sa';
            $secretcode = $account->account_code == '001' ? 'PT. ANUGRAH INOVASI MAKMUR INDONESIA' : 'Flimty';
            $secretkey = $account->account_code == '001' ? 'uajQfPzUExgkNkD69UL5HE' : '4UhFUi3KyW7VBQ6Jeu9Mm';
            $clientcode = $account->account_code == '001' ? 'CLN00102' : 'CLN00084';


            if ($account->account_code == '001') {
                setSetting('GP_USERNAME_001', $username);
                setSetting('ETHIX_SECRETCODE_001', $secretcode);
                setSetting('ETHIX_SECRETKEY_001', $secretkey);
                setSetting('ETHIX_CLIENTCODE_001', $clientcode);
            } else {
                setSetting('GP_USERNAME_002', $username);
                setSetting('ETHIX_SECRETCODE_002', $secretcode);
                setSetting('ETHIX_SECRETKEY_002', $secretkey);
                setSetting('ETHIX_CLIENTCODE_002', $clientcode);
            }

            $account->update(['status' => 1]);
            CompanyUser::updateOrCreate(['user_id' => auth()->user()->id], ['company_id' => $id, 'user_id' => auth()->user()->id]);
            $this->emit('handleSwitch', null);
        }
        $this->emit('showAlert', ['msg' => 'Account switched successfully']);
    }
}
