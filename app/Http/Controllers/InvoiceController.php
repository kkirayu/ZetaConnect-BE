<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Appointment;
use App\Models\EReceipt;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    /**
     * Tampilkan daftar invoice.
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['owner', 'cashier', 'appointment.pet', 'items']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        if ($request->has('cashier_id')) {
            $query->where('cashier_id', $request->cashier_id);
        }

        if ($request->has('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->has('limit') && $request->limit === 'all') {
            $invoices = $query->orderBy('created_at', 'desc')->get();
        } else {
            $limit = $request->get('limit', 10);
            $invoices = $query->orderBy('created_at', 'desc')->paginate($limit);
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar invoice berhasil diambil',
            'data'    => $invoices,
        ], 200);
    }

    /**
     * Ambil tagihan tertunda (Appointment Selesai yang belum ada invoice).
     */
    public function pendingBilling()
    {
        // 1. Ambil semua appointment yang statusnya 'Selesai' dan tidak memiliki invoice.
        $appointments = \App\Models\Appointment::with(['pet.owner', 'service', 'doctor'])
            ->where('status', 'Selesai')
            ->whereDoesntHave('invoice')
            ->orderBy('created_at', 'asc')
            ->get();

        $result = [];

        foreach ($appointments as $apt) {
            // 2. Format Jasa Layanan
            $services = [];
            if ($apt->service) {
                $services[] = [
                    'item_type' => 'Service',
                    'item_id' => $apt->service->id,
                    'name' => $apt->service->name,
                    'quantity' => 1,
                    'price' => $apt->service->price,
                    'subtotal' => $apt->service->price,
                ];
            }

            // 3. Format Resep Obat (Cari EReceipt yang Completed untuk pasien ini hari ini)
            $productsToBill = [];
            $eReceipts = \App\Models\EReceipt::where('pet_id', $apt->pet_id)
                ->where('status', 'Completed')
                ->whereDate('created_at', $apt->created_at->format('Y-m-d'))
                ->with(['items', 'doctor'])
                ->get();

            $recipeDoctorName = null;

            foreach ($eReceipts as $receipt) {
                if (!$recipeDoctorName && $receipt->doctor) {
                    $recipeDoctorName = $receipt->doctor->name;
                }

                foreach ($receipt->items as $item) {
                    // Cari product berdasarkan nama obat
                    $product = \App\Models\Product::where('name', $item->medicine_name)->first();
                    $price = $product ? $product->selling_price : 0;
                    $subtotal = $price * $item->quantity;

                    $productsToBill[] = [
                        'item_type' => 'Product',
                        'item_id' => $product ? $product->id : 0,
                        'name' => $item->medicine_name,
                        'quantity' => $item->quantity,
                        'price' => $price,
                        'subtotal' => $subtotal,
                    ];
                }
            }

            $totalServices = collect($services)->sum('subtotal');
            $totalProducts = collect($productsToBill)->sum('subtotal');

            $result[] = [
                'appointment_id' => $apt->id,
                'queue_number' => $apt->queue_number ?? $apt->id,
                'schedule_date' => $apt->schedule_date,
                'patient' => [
                    'name' => $apt->pet ? $apt->pet->name : 'Unknown',
                    'owner_name' => $apt->pet && $apt->pet->owner ? $apt->pet->owner->name : 'Unknown',
                    'owner_id' => $apt->pet && $apt->pet->owner ? $apt->pet->owner->id : null,
                ],
                'doctor' => [
                    'name' => $recipeDoctorName ?: ($apt->doctor ? $apt->doctor->name : 'Unknown'),
                ],
                'services' => $services,
                'products' => $productsToBill,
                'total_estimation' => $totalServices + $totalProducts,
                'raw_created_at' => $apt->created_at,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar tagihan tertunda berhasil diambil',
            'data'    => $result,
        ], 200);
    }

    /**
     * Simpan invoice baru beserta item-itemnya.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_id'         => 'nullable|exists:appointments,id|unique:invoices,appointment_id',
            'owner_id'               => 'nullable|exists:users,id',
            'client_name'            => 'nullable|string|max:255',
            'cashier_id'             => 'required|exists:users,id',
            'discount'               => 'nullable|numeric|min:0',
            'payment_method'         => 'required|in:Tunai,QRIS,Transfer,Debit',
            'items'                  => 'required|array|min:1',
            'items.*.item_type'      => 'required|in:Service,Product',
            'items.*.item_id'        => 'required|integer',
            'items.*.quantity'       => 'required|integer|min:1',
            'items.*.price'          => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Generate ID invoice: INV-YYYYMMDD-XXX
            $today    = now()->format('Ymd');
            $count    = Invoice::whereDate('created_at', today())->count() + 1;
            $invoiceId = 'INV-' . $today . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

            // Hitung subtotal dari items
            $subtotal = 0;
            $items    = [];
            foreach ($request->items as $item) {
                $itemSubtotal = $item['price'] * $item['quantity'];
                $subtotal    += $itemSubtotal;
                $items[]      = [
                    'item_type' => $item['item_type'],
                    'item_id'   => $item['item_id'],
                    'quantity'  => $item['quantity'],
                    'price'     => $item['price'],
                    'subtotal'  => $itemSubtotal,
                ];
            }

            $discount    = (float) ($request->discount ?? 0);
            $totalAmount = max(0, $subtotal - $discount);

            $invoice = Invoice::create([
                'id'             => $invoiceId,
                'appointment_id' => $request->appointment_id,
                'owner_id'       => $request->owner_id,
                'client_name'    => $request->client_name,
                'cashier_id'     => $request->cashier_id,
                'subtotal'       => $subtotal,
                'discount'       => $discount,
                'total_amount'   => $totalAmount,
                'payment_method' => $request->payment_method,
                'status'         => 'Unpaid',
            ]);

            // Simpan semua item
            foreach ($items as $item) {
                $invoice->items()->create($item);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice berhasil dibuat',
                'data'    => $invoice->load(['owner', 'cashier', 'appointment', 'items.item']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat invoice',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detail invoice beserta items dan pembayaran.
     */
    public function show($id)
    {
        $invoice = Invoice::with(['owner', 'cashier', 'appointment', 'items.item', 'payments'])->find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $invoice,
        ], 200);
    }

    /**
     * Update invoice (discount atau status saja).
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice tidak ditemukan',
            ], 404);
        }

        if ($invoice->status === 'Paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice yang sudah lunas tidak dapat diubah',
            ], 409);
        }

        $validator = Validator::make($request->all(), [
            'discount'       => 'sometimes|numeric|min:0',
            'payment_method' => 'sometimes|in:Tunai,QRIS,Transfer,Debit',
            'status'         => 'sometimes|in:Unpaid,Cancelled',
            'items'          => 'sometimes|array|min:1',
            'items.*.item_type' => 'required_with:items|in:Service,Product',
            'items.*.item_id'   => 'required_with:items|integer',
            'items.*.quantity'  => 'required_with:items|integer|min:1',
            'items.*.price'     => 'required_with:items|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        DB::beginTransaction();
        try {
            if (isset($data['items'])) {
                // Hapus item lama
                $invoice->items()->delete();
                
                $subtotal = 0;
                foreach ($data['items'] as $item) {
                    $itemSubtotal = $item['price'] * $item['quantity'];
                    $subtotal += $itemSubtotal;
                    
                    $invoice->items()->create([
                        'item_type' => $item['item_type'],
                        'item_id'   => $item['item_id'],
                        'quantity'  => $item['quantity'],
                        'price'     => $item['price'],
                        'subtotal'  => $itemSubtotal,
                    ]);
                }
                $invoice->subtotal = $subtotal;
            }

            // Hitung ulang total_amount
            $discount = isset($data['discount']) ? $data['discount'] : $invoice->discount;
            $data['total_amount'] = max(0, $invoice->subtotal - $discount);
            
            if (isset($data['items'])) {
                unset($data['items']);
                $data['subtotal'] = $invoice->subtotal;
            }

            $invoice->update($data);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Invoice berhasil diupdate',
                'data'    => $invoice->fresh()->load(['owner', 'cashier', 'items.item']),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate invoice',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus invoice (hanya yang berstatus Unpaid).
     */
    public function destroy($id)
    {
        $invoice = Invoice::find($id);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice tidak ditemukan',
            ], 404);
        }

        if ($invoice->status !== 'Unpaid') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya invoice berstatus Unpaid yang dapat dihapus',
            ], 409);
        }

        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice berhasil dihapus',
        ], 200);
    }
}
