<?php

namespace App\Http\Controllers\Api\V1\Prospect;

use App\Http\Controllers\Controller;
use App\Models\Prospect;
use App\Models\ProspectActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;

class ProspectController extends Controller
{
    public function index(Request $request)
    {
        $prospect = Prospect::where('created_by', auth()->user()->id);

        if ($request->query('status') != 'all') {
            $prospect->where('tag', $request->query('status'));
        }

        if ($request->query('date') != 'all') {
            $desiredDate = Carbon::now();

            $dateType = $request->query('date');
            if ($dateType == 'day') {
                $prospect->whereDate('created_at', $desiredDate->format('YYYY-MM-DD'));
            }

            if ($dateType == 'week') {
                $startOfWeek = $desiredDate->startOfWeek();
                $endOfWeek = $desiredDate->endOfWeek();
                $prospect->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
            }

            if ($dateType == 'month') {
                $prospect->whereYear('created_at', $desiredDate->year)->whereMonth('created_at', $desiredDate->month);
            }

            if ($dateType == 'year') {
                $prospect->whereYear('created_at', $desiredDate->year);
            }
        }

        if ($request->query('activity') != 'all') {
            $activity = $request->query('activity');
            if ($activity == '0') {
                return $prospect->where('status', 'new');
            }

            if ($activity == '<5') {
                return $prospect->where('status', 'onprogress');
            }

            if ($activity == '>5') {
                return $prospect->where('status', 'closed');
            }
        }

        return response()->json([
            'message' => 'List Prospect',
            'data' => $prospect->orderBy('created_at', $request->query('short') ?? 'DESC')->get()
        ]);
    }

    public function show($id)
    {
        $prospect = Prospect::with(['activities'])->where('uuid', $id)->where('created_by', auth()->user()->id)->first();

        if (!$prospect) {
            return response()->json([
                'message' => 'Not Found',
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'List Prospect',
            'data' => $prospect
        ]);
    }

    public function createProspect(Request $request)
    {
        $validate = [
            'contact' => 'required',
            'status' => 'required',
            'tag' => 'required',
        ];

        $validator = Validator::make($request->all(), $validate);

        // response validation error
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Form Tidak Lengkap'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $data = [
                'uuid' => Uuid::uuid4(),
                'contact' => $request->contact,
                'created_by' => auth()->user()->id,
                'status' => 'new',
                'tag' => $request->tag,
            ];

            $prospect = Prospect::create($data);


            DB::commit();
            return response()->json([
                'message' => 'Successfully add prospect',
                'data' => $prospect
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error add prospect',
                'data' => []
            ], 400);
        }
    }

    function getProspectActivity($id)
    {
        $prospect = Prospect::with(['activities'])->where('uuid', $id)->where('created_by', auth()->user()->id)->first();

        if (!$prospect) {
            return response()->json([
                'message' => 'Not Found',
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'List Prospect',
            'data' => $prospect->activities
        ]);
    }

    function getAllActivityProspect(Request $request)
    {
        $prospect = ProspectActivity::query();
        if ($request->query('date')) {
            $prospect->whereDate('created_at', $request->query('date'));
        }

        return response()->json([
            'message' => 'List Prospect',
            'data' => $prospect->get()
        ]);
    }

    function createProspectActivity(Request $request)
    {
        $validate = [
            'prospect_id' => 'required',
            'notes' => 'required',
            'status' => 'required',
        ];

        $validator = Validator::make($request->all(), $validate);

        // response validation error
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Form Tidak Lengkap',
                'error' => $validator->errors()
            ], 400);
        }
        $attachment = null;
        if ($request->attachment) {
            $attachment = $this->uploadImage($request, 'attachment');
        }

        try {
            DB::beginTransaction();
            $prospect = Prospect::find($request->prospect_id);
            $count = $prospect->activities()->count();
            if ($count <= 4) {
                $prospect->update([
                    'tag' => 'cold',
                    'status' => 'new'
                ]);
            } else if ($count > 5 && $count <= 7) {
                $prospect->update([
                    'tag' => 'warm',
                    'status' => 'onprogress'
                ]);
            } else if ($count >= 6) {
                $prospect->update([
                    'tag' => 'hot',
                    'status' => 'closed'
                ]);
            }
            $data = [
                'uuid' => Uuid::uuid4(),
                'prospect_id' => $request->prospect_id,
                'notes' => $request->notes,
                'status' => $request->status,
                'submit_date' => Carbon::now(),
            ];

            if ($attachment) {
                $data['attachment'] = getImage($attachment);
            }

            $prospect = ProspectActivity::create($data);


            DB::commit();
            return response()->json([
                'message' => 'Successfully add prospect Activity',
                'data' => $prospect
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error add prospect Activity',
                'data' => []
            ], 400);
        }
    }

    function updateProspectActivity(Request $request, $id)
    {
        $validate = [
            'notes' => 'required',
            'status' => 'required',
        ];

        $validator = Validator::make($request->all(), $validate);

        // response validation error
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Form Tidak Lengkap'
            ], 400);
        }
        $prospect = ProspectActivity::where('uuid', $id)->first();

        if (!$prospect) {
            return response()->json([
                'message' => 'Not Found',
                'data' => []
            ], 404);
        }

        $attachment = null;
        if ($request->attachment) {
            $attachment = $this->uploadImage($request, 'attachment');
        }

        try {
            DB::beginTransaction();

            $data = [
                'notes' => $request->notes,
                'status' => $request->status,
            ];

            if ($attachment) {
                $data['attachment'] = getImage($attachment);
            }

            $prospect->update($data);


            DB::commit();
            return response()->json([
                'message' => 'Successfully add prospect Activity',
                'data' => $prospect
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error add prospect Activity',
                'data' => []
            ], 400);
        }
    }

    public function updatedProspect(Request $request, $id)
    {
        $validate = [
            'contact' => 'required',
            'status' => 'required',
            'tag' => 'required',
        ];

        $validator = Validator::make($request->all(), $validate);

        // response validation error
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Form Tidak Lengkap'
            ], 400);
        }

        $prospect = Prospect::where('uuid', $id)->first();

        if (!$prospect) {
            return response()->json([
                'message' => 'Not Found',
                'data' => []
            ], 404);
        }

        try {
            DB::beginTransaction();

            $data = [
                'contact' => $request->contact,
                'status' => $request->status,
                'tag' => $request->tag,
            ];

            $prospect->update($data);

            DB::commit();
            return response()->json([
                'message' => 'Successfully add prospect',
                'data' => $prospect
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error add prospect',
                'data' => []
            ], 400);
        }
    }

    public function deleteProspect($id)
    {

        $prospect = Prospect::where('uuid', $id)->first();

        if (!$prospect) {
            return response()->json([
                'message' => 'Not Found',
                'data' => []
            ], 404);
        }

        try {
            DB::beginTransaction();

            $prospect->delete();

            DB::commit();
            return response()->json([
                'message' => 'Successfully Deleted prospect',
                'data' => $prospect
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error Deleted prospect',
                'data' => []
            ], 400);
        }
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
        $file = Storage::disk('s3')->put('upload/prospect/activity', $request[$path], 'public');
        return $file;
    }

    public function prospectTags()
    {
        $data = [
            ['name' => 'All', 'tag' => 'all', 'count' => Prospect::count()],
            ['name' => '🔥 Hot', 'tag' => 'hot', 'count' => Prospect::where('tag', 'hot')->count()],
            ['name' => '❄️ Cold', 'tag' => 'cold', 'count' => Prospect::where('tag', 'cold')->count()],
            ['name' => '🌤 Warm', 'tag' => 'warm', 'count' => Prospect::where('tag', 'warm')->count()],
        ];

        return response()->json([
            'error' => false,
            'message' => 'List Tag',
            'data' => $data,
        ]);
    }
}
