<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AgentDomainManagementController extends Controller
{
    public function index($agent_domain_id = null)
    {
        return view('spa.spa-index');
    }

    public function listAgentDomain()
    {
        $domains = Domain::all();

        return response()->json([
            'data' => $domains,
            'message' => 'success'
        ]);
    }
    public function listAgentByDomain(Request $request)
    {
        return response()->json([
            'data' => User::whereHas('agentDetail')->paginate($request->perpage),
            'message' => 'success'
        ]);

        return response()->json([
            'data' => [],
            'message' => 'success'
        ]);
    }
    public function saveAgentDomain(Request $request)
    {
        $data = [
            'name'  => $request->name,
            'description'  => $request->description,
            'status'  => $request->status,
            'url'  => $request->url,
            'color'  => $request->color,
            'back_color'  => $request->back_color,
            'fb_pixel'  => $request->fb_pixel
        ];

        if ($request->icon) {
            if (!$request->hasFile('icon')) {
                return response()->json([
                    'error' => true,
                    'message' => 'File not found',
                    'status_code' => 400,
                ], 400);
            }
            $file = $request->file('icon');
            if (!$file->isValid()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Image file not valid',
                    'status_code' => 400,
                ], 400);
            }

            $file = Storage::disk('s3')->put('upload/domain/icon', $request->icon, 'public');
            $data['icon'] = $file;
        }

        Domain::create($data);

        return response()->json([
            'message' => 'success'
        ]);
    }
    public function updateAgentDomain(Request $request)
    {
        $domain = Domain::find($request->agent_domain_id);

        if ($domain) {
            $data = [
                'name'  => $request->name,
                'description'  => $request->description,
                'status'  => $request->status,
                'url'  => $request->url,
                'color'  => $request->color,
                'back_color'  => $request->back_color,
                'fb_pixel'  => $request->fb_pixel
            ];

            if ($request->icon) {
                if (!$request->hasFile('icon')) {
                    return response()->json([
                        'error' => true,
                        'message' => 'File not found',
                        'status_code' => 400,
                    ], 400);
                }
                $file = $request->file('icon');
                if (!$file->isValid()) {
                    return response()->json([
                        'error' => true,
                        'message' => 'Image file not valid',
                        'status_code' => 400,
                    ], 400);
                }

                $file = Storage::disk('s3')->put('upload/domain/icon', $request->icon, 'public');
                if ($domain->icon) {
                    if (Storage::disk('s3')->exists($domain->icon)) {
                        Storage::disk('s3')->delete($domain->icon);
                    }
                }
                $data['icon'] = $file;
            }

            $domain->update($data);

            return response()->json([
                'message' => 'success'
            ]);
        }

        return response()->json([
            'message' => 'failed'
        ]);
    }
    public function toggleAgentDomain(Request $request)
    {
        $user_id = $request->user_id;

        $user = User::find($user_id);
        if ($user->status_agent) {
            $user->domains()->detach($request->domain_id);

            return response()->json([
                'message' => 'success'
            ]);
        }

        $user->domains()->attach($request->domain_id);
        return response()->json([
            'message' => 'success'
        ]);
    }

    public function deleteAgentDomain(Request $request)
    {
        $domain = Domain::find($request->agent_domain_id);

        if ($domain) {
            if ($domain->icon) {
                if (Storage::disk('s3')->exists($domain->icon)) {
                    Storage::disk('s3')->delete($domain->icon);
                }
            }
            $domain->delete();

            return response()->json([
                'message' => 'success'
            ]);
        }

        return response()->json([
            'message' => 'failed'
        ]);
    }
}
