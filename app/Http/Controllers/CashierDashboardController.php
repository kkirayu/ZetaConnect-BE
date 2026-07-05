<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Appointment;
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
        $invoicesToday = Invoice::with(['owner', 'appointment.pet', 'items'])
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

        // 4. Hourly Revenue Data (Trend Chart Simulation)
        $hourlyRevenue = [];
        $currentHour = (int) now()->format('H');
        // Get last 8 hours data
        for ($i = max(0, $currentHour - 7); $i <= $currentHour; $i++) {
            $hourStart = $today->copy()->setTime($i, 0, 0);
            $hourEnd = $today->copy()->setTime($i, 59, 59);
            
            $rev = Invoice::whereBetween('created_at', [$hourStart, $hourEnd])
                ->where('status', 'Paid')
                ->sum('total_amount');
            
            $hourlyRevenue[] = [
                'hour' => $hourStart->format('H:i'),
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
                // If we want to show item names, we'd need to load the polymorphic relation or join. 
                // Since items relation has item_type and item_id, we just show generic for now
                $itemsSummary[] = "{$item->quantity}x Item"; 
            }
            $itemsStr = count($itemsSummary) > 0 ? implode(', ', $itemsSummary) : 'No items';

            $queueList[] = [
                'id' => '#INV-' . str_pad($inv->id, 4, '0', STR_PAD_LEFT),
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
}
