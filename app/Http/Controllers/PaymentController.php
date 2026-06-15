<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Payment::with(['invoice', 'cashier']);

        if ($request->has('invoice_id')) {
            $query->where('invoice_id', $request->invoice_id);
        }

        // Filter berdasarkan status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan metode pembayaran
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter berdasarkan tanggal pembayaran
        if ($request->has('date')) {
            $query->whereDate('paid_at', $request->date);
        }

        // Filter berdasarkan kasir
        if ($request->has('cashier_id')) {
            $query->where('cashier_id', $request->cashier_id);
        }

        $payments = $query->orderBy('created_at', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'Daftar pembayaran berhasil diambil',
            'data'    => $payments,
        ], 200);
    }

    /**
     * Simpan pembayaran baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_id'       => 'required|exists:invoices,id',
            'cashier_id'       => 'required|exists:users,id',
            'payment_method'   => 'required|in:Tunai,QRIS,Transfer,Debit',
            'amount_paid'      => 'required|numeric|min:0',
            'reference_number' => 'nullable|string|max:100',
            'notes'            => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $invoice = Invoice::find($request->invoice_id);

        // Pastikan invoice belum lunas
        if ($invoice->status === 'Paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice ini sudah lunas',
            ], 409);
        }

        // Pastikan invoice tidak dibatalkan
        if ($invoice->status === 'Cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice ini sudah dibatalkan',
            ], 409);
        }

        // Hitung kembalian
        $amountPaid   = (float) $request->amount_paid;
        $totalAmount  = (float) $invoice->total_amount;
        $changeAmount = max(0, $amountPaid - $totalAmount);

        // Validasi jumlah bayar untuk metode non-tunai (harus tepat)
        if (in_array($request->payment_method, ['QRIS', 'Transfer', 'Debit']) && $amountPaid !== $totalAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah pembayaran harus tepat untuk metode ' . $request->payment_method,
            ], 422);
        }

        // Validasi jumlah bayar tunai
        if ($request->payment_method === 'Tunai' && $amountPaid < $totalAmount) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah pembayaran kurang dari total tagihan',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $payment = Payment::create([
                'invoice_id'       => $request->invoice_id,
                'cashier_id'       => $request->cashier_id,
                'payment_method'   => $request->payment_method,
                'amount_paid'      => $amountPaid,
                'change_amount'    => $changeAmount,
                'reference_number' => $request->reference_number,
                'status'           => 'Success',
                'notes'            => $request->notes,
                'paid_at'          => now(),
            ]);

            // Update status invoice menjadi Paid
            $invoice->update([
                'status'         => 'Paid',
                'payment_method' => $request->payment_method,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses',
                'data'    => $payment->load(['invoice', 'cashier']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses pembayaran',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detail pembayaran.
     */
    public function show($id)
    {
        $payment = Payment::with(['invoice.items', 'cashier'])->find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Data pembayaran tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $payment,
        ], 200);
    }

    /**
     * Proses refund pembayaran.
     */
    public function refund(Request $request, $id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Data pembayaran tidak ditemukan',
            ], 404);
        }

        if ($payment->status !== 'Success') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pembayaran berstatus Success yang dapat di-refund',
            ], 409);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            $payment->update([
                'status' => 'Refunded',
                'notes'  => $request->notes ?? $payment->notes,
            ]);

            // Kembalikan status invoice ke Unpaid
            $payment->invoice->update(['status' => 'Unpaid']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil di-refund',
                'data'    => $payment->fresh()->load(['invoice', 'cashier']),
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses refund',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hapus data pembayaran (hanya yang berstatus Pending/Failed).
     */
    public function destroy($id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Data pembayaran tidak ditemukan',
            ], 404);
        }

        if (in_array($payment->status, ['Success', 'Refunded'])) {
            return response()->json([
                'success' => false,
                'message' => 'Pembayaran yang sudah berhasil atau di-refund tidak dapat dihapus',
            ], 409);
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data pembayaran berhasil dihapus',
        ], 200);
    }
}
