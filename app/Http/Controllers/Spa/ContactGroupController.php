<?php

namespace App\Http\Controllers\Spa;

use App\Http\Controllers\Controller;
use App\Models\ContactGroup;
use App\Models\ContactGroupAddressMember;
use App\Models\ContactGroupMember;
use App\Models\LogAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactGroupController extends Controller
{
    public function index($group_id = null)
    {
        return view('spa.spa-index');
    }

    public function listContactGroup(Request $request)
    {
        $search = $request->search;
        $contact = ContactGroup::query();
        if ($search) {
            $contact->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
                $query->orWhere('code', 'like', "%$search%");
                $query->orWhere('address', 'like', "%$search%");
            });
        }

        $contacts = $contact->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $contacts,
            'message' => 'List Contact'
        ]);
    }

    public function listContactMember(Request $request)
    {
        $search = $request->search;
        $contactGroupId = $request->contact_group_id;

        $contact = DB::table('vw_contact_groups');

        if ($search) {
            $contact->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('uid', 'like', "%$search%");
            });
        }

        if ($contactGroupId) {
            // Ensure contacts with the specified contact_group_id appear first
            $contact->orderByRaw("CASE WHEN contact_group_id = ? THEN 0 ELSE 1 END", [$contactGroupId]);
        }

        $contacts = $contact->paginate($request->perpage);

        return response()->json([
            'status' => 'success',
            'data' => $contacts,
            'contact_group_id' => $contactGroupId,
            'message' => 'List Contact'
        ]);
    }

    public function detailContactGroup($group_id)
    {
        $group = ContactGroup::with(['groupMembers', 'groupAddressMembers', 'logs'])->find($group_id);
        return response()->json([
            'status' => 'success',
            'data' => $group,
            'message' => 'detail group'
        ]);
    }

    public function saveContactGroup(Request $request, $group_id = null)
    {
        $form = [
            'code' => $request->code,
            'name' => $request->name,
            'deskripsi' => $request->deskripsi,
            'updated_by' => auth()->user()->id,
        ];

        if (!$group_id) {
            $form['created_by'] = auth()->user()->id;
        }

        $group = ContactGroup::updateOrCreate(['id' => $group_id], $form);
        $existingContacts = ContactGroupMember::where('contact_group_id', $group->id)->select(['id', 'contact_id'])->get();
        $existingContactAddress = ContactGroupAddressMember::where('contact_group_id', $group->id)->select(['id'])->get();
        $existingContactAddressIds = [];
        if (is_array($request->addresss) && count($request->addresss) > 0) {
            $existingContactAddressIds = array_column($request->addresss, 'id');
        }


        // Delete contact that are not in $request->items
        foreach ($existingContacts as $existingContact) {
            if (in_array($existingContact->contact_id, $request->items_deletes)) {
                ContactGroupMember::find($existingContact->id)->delete();
            }
        }

        // Delete address that are not in $request->addresss
        foreach ($existingContactAddress as $existingContactAddress) {
            if (!in_array($existingContactAddress->id, $existingContactAddressIds)) {
                $existingContactAddress->delete();
            }
        }

        if (is_array($request->items)) {
            foreach ($request->items as $key => $item) {
                ContactGroupMember::updateOrCreate(['contact_group_id' => $group->id, 'contact_id' => $item], [
                    'contact_id' => $item,
                    'is_admin' => 0,
                    'contact_group_id' => $group->id,
                ]);
            }
        }
       
        if (is_array($request->addresss)) {
            foreach ($request->addresss as $key => $address) {
                ContactGroupAddressMember::updateOrCreate(['contact_group_id' => $group->id, 'id' => $address['id']], [
                    'contact_group_id' => $group->id,
                    'nama' => $address['nama'],
                    'telepon' => $address['telepon'],
                    'alamat' => $address['alamat'],
                    'provinsi_id' => $address['provinsi_id'],
                    'kabupaten_id' => $address['kabupaten_id'],
                    'kelurahan_id' => $address['kelurahan_id'],
                    'kecamatan_id' => $address['kecamatan_id'],
                    'kodepos' => $address['kodepos'],
                    'default' => @$address['default'],
                ]);
            }
        }
        

        LogAction::create([
            'model_id' => $group_id ?? $group->id,
            'user_id' => auth()->user()->id,
            'action' => $group_id ? 'Update' : 'Create',
            'description' => json_encode($request->all())
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $group,
            'message' => 'Data Berhasil disimpan'
        ]);
    }

    public function deleteContactGroup($group_id)
    {
        $group = ContactGroup::find($group_id);
        if ($group) {
            $group->delete();
            return response()->json([
                'status' => 'success',
                'data' => $group,
                'message' => 'Data Berhasi Dihapus'
            ]);
        }

        return response()->json([
            'status' => 'error',
            'data' => null,
            'message' => 'Data Gagal Dihapus'
        ], 400);
    }
}
