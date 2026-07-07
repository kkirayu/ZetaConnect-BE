//

$products = \App\Models\Product::all();
$suppliers = \App\Models\Supplier::all();

if($products->isEmpty()){
    echo "No products found\n";
    return;
}

if($suppliers->isEmpty()){
    echo "No suppliers found\n";
}

$firstSupplierId = $suppliers->first()->id ?? null;

foreach($products as $product){
    // Masuk (In)
    \App\Models\StockMutation::create([
        'product_id' => $product->id,
        'supplier_id' => $firstSupplierId,
        'mutation_type' => 'In',
        'quantity' => rand(20, 50),
        'date' => now()->subDays(rand(5, 10))
    ]);

    // Keluar (Out)
    \App\Models\StockMutation::create([
        'product_id' => $product->id,
        'supplier_id' => null,
        'mutation_type' => 'Out',
        'quantity' => rand(1, 10),
        'date' => now()->subDays(rand(1, 4))
    ]);
}
echo "Dummy data generated successfully!\n";
