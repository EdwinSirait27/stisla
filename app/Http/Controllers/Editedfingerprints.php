<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Models\Stores;
use App\Models\EditedFingerprint;
use Illuminate\Support\Facades\Storage;



class Editedfingerprints extends Controller
{
    public function index()
    {
        $user     = auth()->user();

        /** @var \App\Models\User|null $user */

        if (!$user->hasPermissionTo('ManageFingerspot')) {
            abort(403, 'Unauthorized');
        }
        return view('pages.Editedfinger.Editedfinger');
    }

    public function showAttachment($id)
    {
        $user = auth()->user();
        /** @var \App\Models\User|null $user */

        if (!$user->hasPermissionTo('ManageFingerspot')) {
            abort(403, 'Unauthorized');
        }

        $record = EditedFingerprint::findOrFail($id);

        if (!$record->attachment) {
            abort(404, 'Attachment not found.');
        }

        if (!Storage::disk('s3')->exists($record->attachment)) {
            abort(404, 'File not found on storage.');
        }

        // Generate temporary signed URL (berlaku 5 menit)
        $url = Storage::disk('s3')->temporaryUrl(
            $record->attachment,
            now()->addMinutes(5)
        );

        return redirect($url);
    }
    public function getEditedfingerprints()
    {
        $user = auth()->user();

        /** @var \App\Models\User|null $user */

        if (!$user->hasPermissionTo('ManageFingerspot')) {
            abort(403, 'Unauthorized');
        }

        $editedfingerprints = EditedFingerprint::select([
            'id',
            'pin',
            'employee_name',
            'position_name',
            'store_name',
            'scan_date',
            'in_1',
            'device_1',
            'in_2',
            'device_2',
            'in_3',
            'device_3',
            'in_4',
            'device_4',
            'in_5',
            'device_5',
            'in_6',
            'device_6',
            'in_7',
            'device_7',
            'in_8',
            'device_8',
            'in_9',
            'device_9',
            'in_10',
            'device_10',
            'duration',
            'attachment'
        ])->get();

        return DataTables::of($editedfingerprints)

            ->addColumn('attachment', function ($row) {
                if (!$row->attachment) return '-';

                $signedUrl = route('fingerprints.attachment', ['id' => $row->id]);

                return '<button class="btn btn-sm btn-primary btn-view-attachment" data-url="' . $signedUrl . '">
                <i class="fas fa-image me-1"></i> Lihat
            </button>';
            })
            ->rawColumns(['attachment'])
            ->make(true);
    }
}
