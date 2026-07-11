<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Pet;
use App\Models\Appointment;
use App\Models\EReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PharmacyController extends Controller
{
    /**
     * Dashboard Statistics
     */
    public function dashboard()
    {
        try {

            $monthlyTransactions = Appointment::whereMonth(
                'created_at',
                now()->month
            )->whereYear(
                'created_at',
                now()->year
            )->count();

            $totalMedicines = Product::count();

            $monthlyPatients = Pet::whereMonth(
                'created_at',
                now()->month
            )->whereYear(
                'created_at',
                now()->year
            )->count();

            $todayVisits = Appointment::whereDate(
                'created_at',
                today()
            )->count();

            return response()->json([
                'monthly_transactions' => $monthlyTransactions,
                'total_medicines'      => $totalMedicines,
                'monthly_patients'     => $monthlyPatients,
                'today_visits'         => $todayVisits
            ]);

        } catch (\Exception $e) {

            Log::error('Dashboard Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Gagal mengambil data dashboard.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Product dengan stok tipis
     */
    public function lowStock()
    {
        try {

            $products = Product::whereColumn(
                    'current_stock',
                    '<=',
                    'min_stock'
                )
                ->select([
                    'id',
                    'name',
                    'category',
                    'current_stock',
                    'min_stock'
                ])
                ->orderBy('current_stock', 'asc')
                ->get();

            return response()->json($products);

        } catch (\Exception $e) {

            Log::error('Low Stock Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Gagal mengambil data stok tipis.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Demografi pasien berdasarkan spesies
     */
    public function patientDemographics()
    {
        try {

            $data = Pet::selectRaw('species, COUNT(*) as total')
                ->groupBy('species')
                ->orderByDesc('total')
                ->get();

            return response()->json($data);

        } catch (\Exception $e) {

            Log::error('Patient Demographics Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Gagal mengambil data demografi pasien.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Produk yang akan kadaluarsa
     * Opsional untuk dashboard tambahan
     */
    public function expiringProducts()
    {
        try {

            $products = Product::whereNotNull('exp_date')
                ->whereDate(
                    'exp_date',
                    '<=',
                    now()->addDays(30)
                )
                ->orderBy('exp_date')
                ->get();

            return response()->json($products);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Gagal mengambil data produk mendekati kadaluarsa.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ringkasan inventaris
     * Opsional untuk dashboard tambahan
     */
    public function inventorySummary()
    {
        try {

            return response()->json([
                'total_products' => Product::count(),

                'low_stock_products' => Product::whereColumn(
                    'current_stock',
                    '<=',
                    'min_stock'
                )->count(),

                'expired_products' => Product::whereDate(
                    'exp_date',
                    '<',
                    today()
                )->count(),

                'expiring_soon_products' => Product::whereDate(
                    'exp_date',
                    '<=',
                    now()->addDays(30)
                )->count()
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Gagal mengambil ringkasan inventaris.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }



    /**
     * Daftar resep yang dikelompokkan per rekam medis (1 kunjungan = 1 resep)
     */
    public function prescriptions(Request $request)
    {
        try {
            $query = EReceipt::with([
                    'pet.owner',
                    'doctor',
                    'items'
                ]);

            // Filter pencarian
            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->whereHas('pet', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('pet.owner', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%");
                    })
                    ->orWhere('id', 'like', "%{$search}%");
                });
            }

            // Filter status
            if ($request->has('status') && $request->status !== '') {
                $status = $request->status;
                if ($status === 'Selesai' || $status === 'Ditebus') $status = 'Completed';
                
                $query->where('status', $status);
            }

            $records = $query->orderByDesc('created_at')->get();

            $mapped = $records->map(function ($record) {
                // Di database EReceipt, status adalah 'Pending' atau 'Completed'
                // Frontend lama menggunakan 'Pending', 'Sebagian Ditebus', 'Selesai'
                $overallStatus = $record->status === 'Completed' ? 'Selesai' : 'Pending';
                
                // Frontend juga butuh item.status ('Pending' atau 'Ditebus')
                $itemStatus = $record->status === 'Completed' ? 'Ditebus' : 'Pending';

                return [
                    'id'            => $record->id,
                    'prescription_code' => 'RX-' . str_pad((string)$record->id, 4, '0', STR_PAD_LEFT),
                    'created_at'    => $record->created_at,
                    'time'          => $record->created_at ? $record->created_at->format('H:i') : '-',
                    'date'          => $record->created_at ? $record->created_at->format('d M Y') : '-',
                    'patient_name'  => $record->pet?->name ?? '-',
                    'owner_name'    => $record->pet?->owner?->name ?? '-',
                    'doctor_name'   => $record->doctor?->name ?? '-',
                    'status'        => $overallStatus,
                    'items'         => $record->items->map(function ($item) use ($itemStatus) {
                        return [
                            'id'           => $item->id,
                            'product_name' => $item->medicine_name ?? 'Produk tidak ditemukan',
                            'quantity'     => $item->quantity,
                            'instructions' => trim($item->dosage . ' ' . $item->frequency),
                            'status'       => $itemStatus,
                        ];
                    }),
                ];
            });

            return response()->json($mapped);

        } catch (\Exception $e) {

            Log::error('Prescriptions Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Gagal mengambil data resep.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update status semua item resep dalam satu rekam medis
     */
    public function updatePrescriptionStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:Pending,Ditebus,Completed,Selesai'
            ]);

            $record = EReceipt::findOrFail($id);
            
            // Konversi status frontend ke backend
            $newStatus = in_array($request->status, ['Ditebus', 'Completed', 'Selesai']) ? 'Completed' : 'Pending';

            $record->update(['status' => $newStatus]);

            return response()->json([
                'message' => "Status resep berhasil diubah ke '{$newStatus}'."
            ]);

        } catch (\Exception $e) {

            Log::error('Update Prescription Status Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Gagal mengubah status resep.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
