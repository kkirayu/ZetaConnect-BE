<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = Activity::with('causer')->latest()->get()->map(function ($activity) {
            $user = $activity->causer;
            
            // Format deskripsi agar lebih informatif jika default dari package
            $action = $activity->description;
            if ($action === 'created') $action = 'Menambahkan data baru';
            if ($action === 'updated') $action = 'Mengubah data';
            if ($action === 'deleted') $action = 'Menghapus data';

            return [
                'id' => $activity->id,
                'timestamp' => $activity->created_at->format('d M Y, H:i') . ' WIB',
                'user' => $user ? $user->name : 'Sistem',
                'role' => $user ? ucfirst($user->role) : 'Sistem',
                'action' => $action . ' pada ' . class_basename($activity->subject_type),
                'severity' => $action === 'deleted' ? 'Tinggi' : 'Normal',
                'properties' => $activity->properties
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }
}
