<?php

namespace App\Http\Controllers;

use App\Models\StockMutation;
use App\Models\Product;
use App\Models\ProductBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMutationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index(Request $request)
{
    $search = $request->search;

    $query = StockMutation::with([
        'product',
        'supplier'
    ]);

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->whereHas('product', function ($product) use ($search) {
                $product->where('name', 'like', "%{$search}%");
            })
            ->orWhere('mutation_type', 'like', "%{$search}%");
        });
    }

    $mutations = $query
        ->orderBy('date', 'desc')
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name,
                'supplier_id' => $item->supplier_id,
                'supplier_name' => $item->supplier?->company_name,
                'mutation_type' => $item->mutation_type,
                'quantity' => $item->quantity,
                'date' => $item->date,
            ];
        });

    return response()->json($mutations);
}

    /**
     * Show the form for creating a new resource.
     * Tidak digunakan pada API.
     */
    public function create()
    {
        return response()->json([
            'message' => 'Method tidak digunakan untuk API'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'product_name' => 'required|string|max:255',
            'supplier_id' => 'nullable|integer',
            'mutation_type' => 'required|in:In,Out',
            'quantity' => 'required|integer|min:1',
            'date' => 'required|date',
            'expired_date' => 'nullable|date',
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $mutation = StockMutation::create([
                'product_id' => $validated['product_id'],
                'supplier_id' => $validated['supplier_id'] ?? null,
                'mutation_type' => $validated['mutation_type'],
                'quantity' => $validated['quantity'],
                'date' => $validated['date']
            ]);

            $product = Product::findOrFail($validated['product_id']);

            if ($validated['mutation_type'] === 'In') {
                $product->current_stock += $validated['quantity'];
                
                // Create product batch
                $batchNumber = 'BATCH-' . date('Ymd', strtotime($validated['date'])) . '-' . strtoupper(substr(uniqid(), -5));
                
                ProductBatch::create([
                    'product_id' => $product->id,
                    'batch_number' => $batchNumber,
                    'stock' => $validated['quantity'],
                    'exp_date' => $validated['expired_date'] ?? null,
                    'notes' => $validated['notes'] ?? null
                ]);

                // Update product's closest exp_date
                if (!empty($validated['expired_date'])) {
                    if (!$product->exp_date || $validated['expired_date'] < $product->exp_date) {
                        $product->exp_date = $validated['expired_date'];
                    }
                }
            } else {
                if ($product->current_stock < $validated['quantity']) {
                    throw new \Exception('Stok tidak mencukupi untuk dikeluarkan.');
                }
                $product->current_stock -= $validated['quantity'];
            }

            $product->save();

            DB::commit();

            return response()->json([
                'message' => 'Mutasi stok berhasil ditambahkan',
                'data' => $mutation
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyimpan mutasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(StockMutation $stockMutation)
    {
        return response()->json($stockMutation);
    }

    /**
     * Show the form for editing the specified resource.
     * Tidak digunakan pada API.
     */
    public function edit(StockMutation $stockMutation)
    {
        return response()->json([
            'message' => 'Method tidak digunakan untuk API'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $stockMutation = StockMutation::find($id);

        if (! $stockMutation) {
            return response()->json([
                'message' => 'Mutasi stok tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'product_id' => 'sometimes|integer',
            'product_name' => 'sometimes|string|max:255',
            'supplier_id' => 'nullable|integer',
            'mutation_type' => 'sometimes|in:In,Out',
            'quantity' => 'sometimes|integer|min:1',
            'date' => 'sometimes|date'
        ]);

        $stockMutation->update($validated);

        return response()->json([
            'message' => 'Mutasi stok berhasil diperbarui',
            'data' => $stockMutation->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $stockMutation = StockMutation::find($id);

        if (! $stockMutation) {
            return response()->json([
                'message' => 'Mutasi stok tidak ditemukan'
            ], 404);
        }

        $stockMutation->delete();

        return response()->json([
            'message' => 'Mutasi stok berhasil dihapus'
        ]);
    }
}
