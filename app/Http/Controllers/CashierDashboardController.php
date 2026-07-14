<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Appointment;
use App\Models\CashierShift;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CashierDashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // 1. Total Revenue Today
        $totalRevenueToday = Invoice::whereDate('created_at', $today)
            ->where('status', 'Paid')
            ->sum('total_amount');

        // 2. Total Revenue Yesterday
        $totalRevenueYesterday = Invoice::whereDate('created_at', $yesterday)
            ->where('status', 'Paid')
            ->sum('total_amount');

        // Calculate Trend (%)
        $revenuePercentageChange = 0;
        if ($totalRevenueYesterday > 0) {
            $revenuePercentageChange = (($totalRevenueToday - $totalRevenueYesterday) / $totalRevenueYesterday) * 100;
        } else if ($totalRevenueToday > 0) {
            $revenuePercentageChange = 100; // 100% increase if yesterday was 0 and today is > 0
        }

        // 3. Stats (Diproses, Menunggu Bayar, Selesai)
        $invoicesToday = Invoice::with(['owner', 'appointment.pet', 'items.item'])
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'desc')
            ->get();

        $appointmentsTodayWithoutInvoice = Appointment::with(['pet.owner', 'service'])
            ->whereDate('schedule_date', $today)
            ->whereDoesntHave('invoice')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = [
            'diproses' => $appointmentsTodayWithoutInvoice->count(),
            'menunggu_bayar' => $invoicesToday->where('status', 'Unpaid')->count(),
            'selesai' => $invoicesToday->where('status', 'Paid')->count(),
        ];

        // 4. 24-Hour Revenue Data in 8 Sessions (3 Hours each)
        $hourlyRevenue = [];
        for ($i = 0; $i < 8; $i++) {
            $startHour = $i * 3;
            $endHour = $startHour + 2;

            $hourStart = $today->copy()->setTime($startHour, 0, 0);
            $hourEnd = $today->copy()->setTime($endHour, 59, 59);
            
            $rev = Invoice::whereBetween('created_at', [$hourStart, $hourEnd])
                ->where('status', 'Paid')
                ->sum('total_amount');
            
            $hourlyRevenue[] = [
                'hour' => sprintf("%02d:00 - %02d:59", $startHour, $endHour),
                'short_label' => sprintf("%02d-%02d", $startHour, $endHour),
                'revenue' => (float) $rev
            ];
        }

        // 5. Queue List (Combined Appointments + Invoices)
        $queueList = [];

        foreach ($appointmentsTodayWithoutInvoice as $apt) {
            $pet = $apt->pet;
            $owner = $pet ? $pet->owner : null;
            $ownerName = $owner ? $owner->name : 'Unknown';
            $petName = $pet ? $pet->name : 'Unknown';
            $species = $pet ? $pet->species : 'Unknown';
            $serviceName = $apt->service ? $apt->service->name : 'Konsultasi';

            $queueList[] = [
                'id' => '#APT-' . str_pad($apt->id, 4, '0', STR_PAD_LEFT),
                'name' => "{$ownerName} ({$petName} - {$species})",
                'items' => "1x {$serviceName}",
                'time' => Carbon::parse($apt->created_at)->format('h:i A'),
                'status' => 'Diproses',
                'raw_created_at' => $apt->created_at,
                'type' => 'appointment',
                'raw_data' => $apt
            ];
        }

        foreach ($invoicesToday as $inv) {
            $ownerName = $inv->owner ? $inv->owner->name : 'Unknown';
            
            $petName = 'Unknown';
            $species = 'Unknown';
            if ($inv->appointment && $inv->appointment->pet) {
                $petName = $inv->appointment->pet->name;
                $species = $inv->appointment->pet->species;
            }

            $itemsSummary = [];
            foreach ($inv->items as $item) {
                $itemName = $item->item ? $item->item->name : 'Item';
                $itemsSummary[] = "{$item->quantity}x {$itemName}"; 
            }
            $itemsStr = count($itemsSummary) > 0 ? implode(', ', $itemsSummary) : 'No items';

            $queueList[] = [
                'id' => $inv->id,
                'name' => "{$ownerName} ({$petName} - {$species})",
                'items' => $itemsStr,
                'time' => Carbon::parse($inv->created_at)->format('h:i A'),
                'status' => $inv->status === 'Paid' ? 'Selesai' : 'Menunggu',
                'raw_created_at' => $inv->created_at,
                'type' => 'invoice',
                'raw_data' => $inv
            ];
        }

        // Sort queueList by time descending
        usort($queueList, function ($a, $b) {
            return $b['raw_created_at'] <=> $a['raw_created_at'];
        });

        // Limit to top 10 items for dashboard
        $queueList = array_slice($queueList, 0, 10);

        return response()->json([
            'success' => true,
            'data' => [
                'total_revenue_today' => $totalRevenueToday,
                'revenue_percentage_change' => round($revenuePercentageChange, 1),
                'stats' => $stats,
                'hourly_revenue' => $hourlyRevenue,
                'queue_list' => $queueList,
            ]
        ]);
    }

    public function shiftSummary(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        // Cari shift terbuka untuk kasir ini
        $shift = CashierShift::where('cashier_id', $user->id)
            ->where('status', 'open')
            ->first();

        $startingCash = $shift ? $shift->starting_cash : 500000;
        $startTime = $shift ? $shift->start_time : clone $today;

        $invoices = Invoice::with(['owner', 'payments'])
            ->where('created_at', '>=', $startTime)
            ->where('status', 'Paid')
            ->orderBy('created_at', 'asc')
            ->get();

        $transactions = [];
        foreach ($invoices as $inv) {
            $method = 'Tunai';
            if ($inv->payments->count() > 0) {
                $rawMethod = $inv->payments->first()->payment_method;
                if (strtolower($rawMethod) === 'qris' || strtolower($rawMethod) === 'transfer') {
                    $method = 'QRIS';
                }
            }

            $ownerName = $inv->owner ? $inv->owner->name : 'Unknown';
            
            $transactions[] = [
                'id' => 'TRX-' . str_pad($inv->id, 4, '0', STR_PAD_LEFT),
                'time' => $inv->created_at->format('H:i'),
                'invoice' => '#INV-' . str_pad($inv->id, 4, '0', STR_PAD_LEFT),
                'customer' => $ownerName,
                'method' => $method,
                'total' => (float) $inv->total_amount
            ];
        }

        return response()->json([
            'success' => true,
            'transactions' => $transactions,
            'starting_cash' => $startingCash
        ]);
    }

    public function closeShift(Request $request)
    {
        $request->validate([
            'actual_cash' => 'required|numeric',
            'expected_cash' => 'required|numeric',
            'difference' => 'required|numeric',
            'total_revenue' => 'required|numeric',
            'cash_revenue' => 'required|numeric',
            'qris_revenue' => 'required|numeric'
        ]);

        $user = $request->user();
        
        $shift = CashierShift::where('cashier_id', $user->id)
            ->where('status', 'open')
            ->first();

        if (!$shift) {
            $shift = new CashierShift();
            $shift->cashier_id = $user->id;
            $shift->start_time = Carbon::today();
            $shift->starting_cash = 500000;
        }

        $shift->end_time = Carbon::now();
        $shift->system_revenue = $request->total_revenue;
        $shift->physical_cash = $request->actual_cash;
        $shift->status = 'closed';
        $shift->save();

        return response()->json([
            'success' => true,
            'message' => 'Shift berhasil ditutup'
        ]);
    }
}
