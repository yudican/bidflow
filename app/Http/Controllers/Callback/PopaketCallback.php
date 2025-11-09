<?php

namespace App\Http\Controllers\Callback;

use App\Http\Controllers\Controller;
use App\Models\LogError;
use Illuminate\Http\Request;

class PopaketCallback extends Controller
{
    public function getCallbackTracking(Request $request)
    {
        LogError::updateOrCreate(['id' => 1], [
            'message' => 'Response Po Paket Callback',
            'trace' => $request->all(),
            'action' => 'PopaketCallback (getCallbackTracking)',
        ]);
        return true;
    }
    public function getCallbackAwb(Request $request)
    {
        LogError::updateOrCreate(['id' => 1], [
            'message' => 'Response Po Paket Callback AWB',
            'trace' => $request->all(),
            'action' => 'PopaketCallback (getCallbackAwb)',
        ]);
        return true;
    }
}
