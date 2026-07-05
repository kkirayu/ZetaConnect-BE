<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function financial(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $queryRevenueByDate = DB::table('payments')
            ->select(DB::raw('DATE(paid_at) as date'), DB::raw('SUM(amount_paid) as total_revenue'), DB::raw('COUNT(id) as total_transactions'))
            ->where('status', 'Success')
            ->whereNotNull('paid_at');

        $queryRevenueByMethod = DB::table('payments')
            ->select('payment_method', DB::raw('SUM(amount_paid) as total_revenue'), DB::raw('COUNT(id) as total_transactions'))
            ->where('status', 'Success');

        if ($startDate) {
            $queryRevenueByDate->whereDate('paid_at', '>=', $startDate);
            $queryRevenueByMethod->whereDate('paid_at', '>=', $startDate);
        }
        if ($endDate) {
            $queryRevenueByDate->whereDate('paid_at', '<=', $endDate);
            $queryRevenueByMethod->whereDate('paid_at', '<=', $endDate);
        }

        $revenueByDate = $queryRevenueByDate->groupBy(DB::raw('DATE(paid_at)'))->orderBy('date', 'desc')->get();
        $revenueByMethod = $queryRevenueByMethod->groupBy('payment_method')->get();

        return response()->json([
            'success' => true,
            'message' => 'Laporan Keuangan berhasil diambil',
            'data' => [
                'revenue_by_date' => $revenueByDate,
                'revenue_by_method' => $revenueByMethod,
            ]
        ], 200);
    }

    public function demographics(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $queryVisitsBySpecies = DB::table('appointments')
            ->join('pets', 'appointments.pet_id', '=', 'pets.id')
            ->select('pets.species', DB::raw('COUNT(appointments.id) as total_visits'))
            ->whereIn('appointments.status', ['Selesai', 'Dalam Periksa']);

        $queryVisitsByBreed = DB::table('appointments')
            ->join('pets', 'appointments.pet_id', '=', 'pets.id')
            ->select('pets.breed', DB::raw('COUNT(appointments.id) as total_visits'))
            ->whereIn('appointments.status', ['Selesai', 'Dalam Periksa'])
            ->whereNotNull('pets.breed');

        if ($startDate) {
            $queryVisitsBySpecies->whereDate('appointments.schedule_date', '>=', $startDate);
            $queryVisitsByBreed->whereDate('appointments.schedule_date', '>=', $startDate);
        }
        if ($endDate) {
            $queryVisitsBySpecies->whereDate('appointments.schedule_date', '<=', $endDate);
            $queryVisitsByBreed->whereDate('appointments.schedule_date', '<=', $endDate);
        }

        $visitsBySpecies = $queryVisitsBySpecies->groupBy('pets.species')->get();
        $visitsByBreed = $queryVisitsByBreed->groupBy('pets.breed')->orderBy('total_visits', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Laporan Demografi berhasil diambil',
            'data' => [
                'visits_by_species' => $visitsBySpecies,
                'visits_by_breed' => $visitsByBreed,
            ]
        ], 200);
    }

    public function stockMutation(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $queryStockMutation = DB::table('stock_mutations')
            ->join('products', 'stock_mutations.product_id', '=', 'products.id')
            ->select('products.name as product_name', 'stock_mutations.mutation_type', DB::raw('SUM(stock_mutations.quantity) as total_quantity'));

        if ($startDate) {
            $queryStockMutation->whereDate('stock_mutations.date', '>=', $startDate);
        }
        if ($endDate) {
            $queryStockMutation->whereDate('stock_mutations.date', '<=', $endDate);
        }

        $stockMutation = $queryStockMutation->groupBy('products.name', 'stock_mutations.mutation_type')->get();

        $formattedData = [];
        foreach ($stockMutation as $item) {
            if (!isset($formattedData[$item->product_name])) {
                $formattedData[$item->product_name] = ['In' => 0, 'Out' => 0];
            }
            $formattedData[$item->product_name][$item->mutation_type] += $item->total_quantity;
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan Mutasi Stok berhasil diambil',
            'data' => [
                'mutations' => $formattedData,
                'raw_mutations' => $stockMutation,
            ]
        ], 200);
    }

    public function dashboardSummary()
    {
        $today = now()->format('Y-m-d');
        $thisMonth = now()->month;
        $thisYear = now()->year;

        // 1. Total Pendapatan Hari Ini
        $totalRevenueToday = DB::table('payments')
            ->whereDate('paid_at', $today)
            ->where('status', 'Success')
            ->sum('amount_paid');

        // 2. Jumlah Staf Aktif
        $activeStaffCount = DB::table('users')
            ->whereIn('role', ['Admin', 'Dokter', 'Resepsionis', 'Apoteker', 'Kasir'])
            ->where('status', 'Aktif')
            ->count();

        // 3. Total Pasien Bulan Ini (Unik berdasarkan pet_id)
        $totalPatientsThisMonth = DB::table('appointments')
            ->whereMonth('schedule_date', $thisMonth)
            ->whereYear('schedule_date', $thisYear)
            ->distinct('pet_id')
            ->count('pet_id');

        // 4. Kunjungan Hari Ini
        $visitsToday = DB::table('appointments')
            ->whereDate('schedule_date', $today)
            ->count();

        // 5. Statistik Pendapatan 7 Hari
        $sevenDaysAgo = now()->subDays(6)->format('Y-m-d');
        $revenueLast7Days = DB::table('payments')
            ->select(DB::raw('DATE(paid_at) as date'), DB::raw('SUM(amount_paid) as total_revenue'))
            ->whereDate('paid_at', '>=', $sevenDaysAgo)
            ->where('status', 'Success')
            ->groupBy(DB::raw('DATE(paid_at)'))
            ->orderBy('date', 'asc')
            ->get();

        // 6. Jenis Pasien Terbanyak
        $visitsBySpecies = DB::table('appointments')
            ->join('pets', 'appointments.pet_id', '=', 'pets.id')
            ->select('pets.species', DB::raw('COUNT(appointments.id) as count'))
            ->groupBy('pets.species')
            ->orderBy('count', 'desc')
            ->get();

        $totalSpeciesVisits = $visitsBySpecies->sum('count');
        $speciesPercentage = $visitsBySpecies->map(function ($item) use ($totalSpeciesVisits) {
            $percentage = $totalSpeciesVisits > 0 ? round(($item->count / $totalSpeciesVisits) * 100) : 0;
            return [
                'species' => $item->species,
                'count' => $item->count,
                'percentage' => $percentage
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Summary dashboard berhasil diambil',
            'data' => [
                'total_revenue_today' => $totalRevenueToday,
                'active_staff_count' => $activeStaffCount,
                'total_patients_this_month' => $totalPatientsThisMonth,
                'visits_today' => $visitsToday,
                'revenue_chart' => $revenueLast7Days,
                'species_distribution' => $speciesPercentage,
            ]
        ], 200);
    }
}
