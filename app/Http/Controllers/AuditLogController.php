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

            $props = $activity->properties;

            return [
                'id' => $activity->id,
                'timestamp' => $activity->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB',
                'user' => $user ? $user->name : 'Sistem',
                'role' => $user ? ucfirst($user->role) : 'Sistem',
                'action' => str_starts_with($action, 'API') ? $action : ($action . ' pada ' . class_basename($activity->subject_type)),
                'severity' => str_contains(strtolower($action), 'delete') || str_contains(strtolower($action), 'menghapus') ? 'Tinggi' : 'Normal',
                'method' => $props['method'] ?? null,
                'endpoint' => $props['endpoint'] ?? null,
                'payload' => $props['payload'] ?? ($props['attributes'] ?? null),
                'response' => $props['response'] ?? null,
                'properties' => $props
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }
}
