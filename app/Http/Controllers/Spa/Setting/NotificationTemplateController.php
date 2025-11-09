<?php

namespace App\Http\Controllers\Spa\Setting;

use App\Exports\Setting\NotificationTemplateExportTable;
use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class NotificationTemplateController extends Controller
{
    public function index($template_id = null)
    {
        return view('spa.spa-index');
    }

    public function listNotificationTemplate(Request $request)
    {
        $search = $request->search;
        $role_id = $request->role_id;
        $group_id = $request->group_id;
        $type = $request->type;
        $status = $request->status;

        $banner =  NotificationTemplate::query();
        if ($search) {
            $banner->where(function ($query) use ($search) {
                $query->where('notification_code', 'like', "%$search%");
                $query->orWhere('notification_title', 'like', "%$search%");
                $query->orWhere('notification_subtitle', 'like', "%$search%");
                $query->orWhere('notification_note', 'like', "%$search%");
            });
        }

        if ($type) {
            $banner->where('notification_type', $type);
        }

        if ($group_id) {
            $banner->where('group_id', $group_id);
        }

        if ($status) {
            $banner->where('status', $status == 10 ? 0 : 1);
        }

        if ($role_id) {
            $banner->whereHas('roles', function ($query) use ($role_id) {
                $query->whereIn('role_id', $role_id);
            });
        }


        $banners = $banner->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => tap($banners, function ($order) use ($type, $group_id) {
                return $order->getCollection()->transform(function ($item) use ($type, $group_id) {
                    $total = 0;
                    if ($type == 'group') {
                        $total = DB::table('notification_templates')->where('group_id', $item['id'])->count();
                    }
                    $item['total'] = $total;


                    return $item;
                });
            }),
            'group_name' => DB::table('notification_templates')->where('id', $group_id)->first()?->notification_title,
            'message' => 'List Notification Template'
        ]);
    }


    public function getDetailNotificationTemplate($template_id)
    {
        $brand = NotificationTemplate::with('roles')->find($template_id);

        return response()->json([
            'status' => 'success',
            'data' => $brand,
            'message' => 'Detail Notification Template'
        ]);
    }

    public function saveNotificationTemplate(Request $request)
    {
        $notification = NotificationTemplate::where('notification_code', $request->notification_code)->first();
        if ($notification) {
            return response()->json([
                'status' => 'success',
                'message' => 'Kode Notifikasi Sudah Terdaftar, Template Gagal Disimpan'
            ], 400);
        }
        try {
            DB::beginTransaction();
            $role_id = json_decode($request->role_ids, true);
            $data = [
                'notification_code'  => $request->notification_code,
                'notification_title'  => $request->notification_title,
                'notification_subtitle'  => $request->notification_subtitle,
                'notification_body'  => $request->notification_body,
                'notification_type'  => $request->notification_type,
                'notification_note'  => $request->notification_note,
                'group_id'  => $request->group_id,
            ];

            $banner = NotificationTemplate::create($data);
            $banner->roles()->attach($role_id);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Template Notifikasi berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Template Notifikasi gagal disimpan'
            ], 400);
        }
    }

    public function updateNotificationTemplate(Request $request, $template_id)
    {
        try {
            DB::beginTransaction();
            $role_id = json_decode($request->role_ids, true);
            $data = [
                'notification_title'  => $request->notification_title,
                'notification_subtitle'  => $request->notification_subtitle,
                'notification_body'  => $request->notification_body,
                'notification_type'  => $request->notification_type,
                'notification_note'  => $request->notification_note,
                'group_id'  => $request->group_id,
            ];
            $row = NotificationTemplate::find($template_id);

            $row->update($data);
            $row->roles()->sync($role_id);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Template Notifikasi berhasil disimpan'
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status' => 'success',
                'message' => 'Data Template Notifikasi gagal disimpan'
            ], 400);
        }
    }

    public function deleteNotificationTemplate($template_id)
    {
        $banner = NotificationTemplate::find($template_id);
        $banner->roles()->detach();
        $banner->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Data Template Notifikasi berhasil dihapus'
        ]);
    }

    public function updateStatusGroup(Request $request, $template_id)
    {
        $notification = NotificationTemplate::find($template_id);
        if ($notification) {
            if ($notification->notification_type == 'group') {
                NotificationTemplate::where('group_id', $template_id)->update(['status' => $request->value ? 1 : 0]);
                $notification->update(['status' => $request->value ? 1 : 0]);
            } else {
                $notification->update(['status' => $request->value ? 1 : 0]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Data Template Notifikasi berhasil diubah'
        ]);
    }

    public function export($group_id = 91)
    {
        $file_name = 'NotificationTemplate.xlsx';
        $path = 'exports/' . $file_name;

        Excel::store(new NotificationTemplateExportTable($group_id), $path, 'public');
        return response()->json([
            'status' => 'success',
            'data' => asset('storage/' . $path),
            'message' => 'List Notification'
        ]);
    }

    public function getGroup($group_id)
    {
        $group = NotificationTemplate::find($group_id, ['notification_title']);
        return response()->json([
            'status' => 'success',
            'data' => $group->notification_title ?? '',
            'message' => 'List Group'
        ]);
    }
}
