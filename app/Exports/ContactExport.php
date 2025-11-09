<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ContactExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    protected $request;
    protected $title;
    public $timeout = 3600;
    public $tries = 3;

    public function __construct($request, $title = 'ContactDetail')
    {
        $this->request = $request;
        $this->title = $title;
    }

    private function getUsersQuery()
    {
        $request = $this->request;

        $query = DB::table('users as u')
            ->select([
                'u.id',
                'u.name',
                'u.email',
                'u.telepon',
                'u.gender',
                'u.bod',
                'u.uid',
                'r.role_name',
                'au.alamat',
                'au.catatan',
                'au.kodepos as address_kodepos',
                'prov.nama as provinsi_nama',
                'kab.nama as kabupaten_nama',
                'kec.nama as kecamatan_nama',
                'kel.nama as kelurahan_nama',
                'kel.zip as kelurahan_zip'
            ])
            ->leftJoin('role_user as ur', 'u.id', '=', 'ur.user_id')
            ->leftJoin('roles as r', 'ur.role_id', '=', 'r.id')
            ->leftJoin('address_users as au', 'u.id', '=', 'au.user_id')
            ->leftJoin('addr_provinsi as prov', 'au.provinsi_id', '=', 'prov.pid')
            ->leftJoin('addr_kabupaten as kab', 'au.kabupaten_id', '=', 'kab.pid')
            ->leftJoin('addr_kecamatan as kec', 'au.kecamatan_id', '=', 'kec.pid')
            ->leftJoin('addr_kelurahan as kel', 'au.kelurahan_id', '=', 'kel.pid');

        if (!empty($request['search'])) {
            $search = $request['search'];
            $query->where(function ($q) use ($search) {
                $q->where('u.name', 'like', "%$search%")
                    ->orWhere('u.email', 'like', "%$search%")
                    ->orWhere('r.role_name', 'like', "%$search%");
            });
        }

        if (!empty($request['roles'])) {
            $query->whereIn('ur.role_id', $request['roles']);
        }

        if (!empty($request['status'])) {
            $query->whereIn('u.status', $request['status']);
        }

        if (!empty($request['createdBy'])) {
            $query->where('u.created_by', $request['createdBy']);
        }

        return $query;
    }

    public function collection()
    {
        $users = new Collection();
        $currentUser = null;

        $this->getUsersQuery()
            ->orderBy('u.id')
            ->chunk(500, function ($rows) use (&$users, &$currentUser) {
                foreach ($rows as $row) {
                    if (!$currentUser || $currentUser->id !== $row->id) {
                        if ($currentUser) {
                            $users->push($currentUser);
                        }
                        $currentUser = (object)[
                            'id' => $row->id,
                            'name' => $row->name,
                            'email' => $row->email,
                            'telepon' => $row->telepon,
                            'gender' => $row->gender,
                            'bod' => $row->bod,
                            'role' => $row->role_name,
                            'uid' => $row->uid,
                            'addresses' => []
                        ];
                    }

                    if ($row->alamat) {
                        $currentUser->addresses[] = [
                            'alamat' => $row->alamat,
                            'catatan' => $row->catatan,
                            'kelurahan' => $row->kelurahan_nama,
                            'kecamatan' => $row->kecamatan_nama,
                            'kabupaten' => $row->kabupaten_nama,
                            'provinsi' => $row->provinsi_nama,
                            'kodepos' => $row->address_kodepos ?? $row->kelurahan_zip ?? '0'
                        ];
                    }
                }

                if ($currentUser) {
                    $users->push($currentUser);
                }
            });

        return $users;
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Email',
            'Telepon',
            'Gender',
            'Tanggal Lahir',
            'Role',
            'UID',
            'Alamat Lengkap',
            'Kelurahan',
            'Kecamatan',
            'Kabupaten',
            'Provinsi',
            'Kode Pos'
        ];
    }

    public function map($row): array
    {
        $firstAddress = !empty($row->addresses) ? $row->addresses[0] : null;

        $address = $firstAddress ? sprintf(
            "%s %s %s, %s, %s, %s - %s",
            $firstAddress['alamat'],
            $firstAddress['catatan'] ? "({$firstAddress['catatan']})" : '',
            $firstAddress['kelurahan'],
            $firstAddress['kecamatan'],
            $firstAddress['kabupaten'],
            $firstAddress['provinsi'],
            $firstAddress['kodepos']
        ) : '-';

        return [
            $row->name ?? '-',
            $row->email ?? '-',
            $row->telepon ?? '-',
            $row->gender ?? '-',
            $row->bod ? date('d-m-Y', strtotime($row->bod)) : '-',
            $row->role ?? '-',
            $row->uid ?? '-',
            $address,
            $firstAddress['kelurahan'] ?? '-',
            $firstAddress['kecamatan'] ?? '-',
            $firstAddress['kabupaten'] ?? '-',
            $firstAddress['provinsi'] ?? '-',
            $firstAddress['kodepos'] ?? '-'
        ];
    }

    public function title(): string
    {
        return $this->title;
    }
}
