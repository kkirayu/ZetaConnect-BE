<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Pet;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
     * Daftar semua produk untuk Stock Monitoring
     */
    public function products(Request $request)
    {
        try {
            $query = Product::query();

            // Filter pencarian berdasarkan nama atau kategori
            if ($request->has('search') && $request->search !== '') {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('category', 'like', "%{$search}%");
                });
            }

            $products = $query->orderBy('name', 'asc')->get();

            // Mapping data dengan computed fields untuk frontend
            $mapped = $products->map(function ($product) {
                $isExpired = $product->exp_date && $product->exp_date < today();
                $stockStatus = 'Tersedia';

                if ($product->current_stock <= 0) {
                    $stockStatus = 'Habis';
                } elseif ($product->current_stock <= $product->min_stock) {
                    $stockStatus = 'Hampir Habis';
                }

                return [
                    'id'             => $product->id,
                    'name'           => $product->name,
                    'description'    => $product->description,
                    'category'       => $product->category,
                    'base_price'     => $product->base_price,
                    'selling_price'  => $product->selling_price,
                    'current_stock'  => $product->current_stock,
                    'min_stock'      => $product->min_stock,
                    'exp_date'       => $product->exp_date,
                    'is_expired'     => $isExpired,
                    'stock_status'   => $stockStatus,
                ];
            });

            return response()->json($mapped);

        } catch (\Exception $e) {

            Log::error('Products List Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Gagal mengambil data produk.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus produk
     */
    public function deleteProduct($id)
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            return response()->json([
                'message' => "Produk '{$product->name}' berhasil dihapus."
            ]);

        } catch (\Exception $e) {

            Log::error('Delete Product Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Gagal menghapus produk.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
