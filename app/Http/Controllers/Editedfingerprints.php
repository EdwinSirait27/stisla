<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Stores;
use App\Models\EditedFingerprint;


class Editedfingerprints extends Controller
{
    public function index()
    {
        return view('pages.Editedfinger.Editedfinger');
    }
//     public function getEditedfingerprints()
//     {
//         $editedfingerprints = EditedFingerprint::select(['id', 'pin','employee_name','position_name','store_name','scan_date','in_1','device_1','in_2','device_2','in_3','device_3','in_4','device_4','in_5','device_5','in_6','device_6','in_7','device_7','in_8','device_8','in_9','device_9','in_10','device_10','duration','attachment'])
//             ->get()
//            ->map(function ($editedfingerprint) {
//     if ($editedfingerprint->attachment) {
//         $imageUrl = asset('storage/attachment/' . $editedfingerprint->attachment);
//         $editedfingerprint->attachment = '<button class="btn btn-sm btn-primary view-image" data-image-url="' . $imageUrl . '">Lihat Gambar</button>';
//     } else {
//         $editedfingerprint->attachment = '-';
//     }

//     return $editedfingerprint;
// });
//         return DataTables::of($editedfingerprints)
//             ->make(true);
//     }
public function getEditedfingerprints()
{
    $editedfingerprints = EditedFingerprint::select([
        'id', 'pin', 'employee_name', 'position_name', 'store_name', 'scan_date',
        'in_1','device_1','in_2','device_2','in_3','device_3','in_4','device_4',
        'in_5','device_5','in_6','device_6','in_7','device_7','in_8','device_8',
        'in_9','device_9','in_10','device_10','duration','attachment'
    ])
    ->get()
   ->map(function ($editedfingerprint) {
    if ($editedfingerprint->attachment) {
        // attachment dari DB sudah "attachment/nama_file.jpg"
        $url = asset('storage/' . $editedfingerprint->attachment);
        $editedfingerprint->attachment = '<button class="btn btn-sm btn-primary view-image" data-image-url="' . $url . '">Lihat Gambar</button>';
    } else {
        $editedfingerprint->attachment = '-';
    }

    return $editedfingerprint;
});

    return DataTables::of($editedfingerprints)
        ->rawColumns(['attachment']) // <- ini penting agar kolom 'attachment' tidak di-escape jadi teks
        ->make(true);
}

}
