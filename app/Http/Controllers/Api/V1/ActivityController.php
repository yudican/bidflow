<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = Activity::where('created_by', auth()->user()->id)->get();

        return response()->json([
            'message' => 'List activity',
            'data' => $activities
        ]);
    }

    public function show($id)
    {
        $activity = Activity::where('uuid', $id)->where('created_by', auth()->user()->id)->first();

        if (!$activity) {
            return response()->json([
                'message' => 'Not Found',
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'List activity',
            'data' => $activity
        ]);
    }

    public function createActivity(Request $request)
    {
        $validate = [
            'title' => 'required',
            'submit_date' => 'required',
            'status' => 'required',
        ];

        $validator = Validator::make($request->all(), $validate);

        $attachment = null;
        if ($request->attachment) {
            $attachment = $this->uploadImage($request, 'attachment');
        }
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
                'title' => $request->title,
                'description' => $request->description,
                'submit_date' => $request->submit_date ?? Carbon::now(),
                'status' => $request->status,
                'created_by' => auth()->user()->id,
            ];

            if ($attachment) {
                $data['attachment'] = getImage($attachment);
            }

            $activity = Activity::create($data);


            DB::commit();
            return response()->json([
                'message' => 'Successfully add activity',
                'data' => $activity
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error add activity',
                'data' => []
            ], 400);
        }
    }

    public function updatedActivity(Request $request, $id)
    {
        $validate = [
            'title' => 'required',
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

        $activity = Activity::where('uuid', $id)->first();
        if (!$activity) {
            return response()->json([
                'message' => 'Not Found',
                'data' => []
            ], 404);
        }

        $attachment = $this->uploadImage($request, 'attachment');
        try {
            DB::beginTransaction();
            $activity = Activity::find($id);
            $data = [
                'title' => $request->title,
                'status' => $request->status,
                'description' => $request->description,
            ];

            if ($attachment) {
                $data['attachment'] = getImage($attachment);
            }

            $activity->update($data);

            DB::commit();
            return response()->json([
                'message' => 'Successfully add activity',
                'data' => $activity
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error add activity',
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
        $file = Storage::disk('s3')->put('upload/activity/activity', $request[$path], 'public');
        return $file;
    }
}
