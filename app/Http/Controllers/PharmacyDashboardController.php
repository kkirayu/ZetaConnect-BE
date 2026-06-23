<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Pet;
use App\Models\Appointment;
use App\Models\Invoice;

class PharmacyDashboardController extends Controller
{
    public function dashboard()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        return response()->json([

            'monthly_transactions' => Invoice::whereMonth(
                'created_at',
                $currentMonth
            )->whereYear(
                'created_at',
                $currentYear
            )->count(),

            'total_medicines' => Product::count(),

            'monthly_patients' => Appointment::whereMonth(
                'created_at',
                $currentMonth
            )->whereYear(
                'created_at',
                $currentYear
            )->distinct('pet_id')
             ->count(),

            'today_visits' => Appointment::whereDate(
                'schedule_date',
                today()
            )->count(),

        ]);
    }

    public function lowStock()
    {
        // category adalah kolom enum di tabel products, bukan relasi
        $lowStockProducts = Product::whereColumn('current_stock', '<', 'min_stock')
            ->get();

        return response()->json($lowStockProducts);
    }
    public function patientDemographics()
    {
        return Pet::selectRaw(
            'species, COUNT(*) as total'
        )
        ->groupBy('species')
        ->orderByDesc('total')
        ->get();
    }
}
