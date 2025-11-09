<?php

namespace App\Imports;

use App\Models\Banner;
use Maatwebsite\Excel\Concerns\ToModel;

class BannerImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Banner([
            'title'     => $row[0],
            'url'    => $row[1],
            'slug' => $row[2],
        ]);
    }
}
