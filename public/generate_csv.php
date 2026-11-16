<?php
$filename = 'dummy_sales_1000.csv';
$handle = fopen($filename, 'w');
fputcsv($handle, ['product_name', 'quantity', 'date', 'purchase_cost', 'selling_price', 'main_category', 'brand_category']);

// Sample products to rotate through
$products = [
    ['name' => 'Sample Product A', 'cat' => 'Electronics', 'brand' => 'Samsung', 'cost' => 100, 'price' => 150],
    ['name' => 'Sample Product B', 'cat' => 'Groceries', 'brand' => 'Nestle', 'cost' => 50, 'price' => 99.5],
    ['name' => 'Test Item C', 'cat' => 'Electronics', 'brand' => 'Apple', 'cost' => 500, 'price' => 800],
    ['name' => 'Test Item D', 'cat' => 'Fittings', 'brand' => 'Apex', 'cost' => 20, 'price' => 45],
    ['name' => 'Test Item E', 'cat' => 'Groceries', 'brand' => 'Coke', 'cost' => 10, 'price' => 25]
];

for ($i = 0; $i < 1000; $i++) {
    $p = $products[array_rand($products)];
    $qty = rand(1, 20);
    // Random date within the last 2 years to test the forecasting sync
    $date = date('Y-m-d', strtotime('-' . rand(1, 730) . ' days'));
    
    fputcsv($handle, [
        $p['name'],
        $qty,
        $date,
        $p['cost'],
        $p['price'],
        $p['cat'],
        $p['brand']
    ]);
}
fclose($handle);
echo "Successfully generated <strong>{$filename}</strong> with 1,000 rows in the public folder!<br><br><a href='dummy_sales_1000.csv' download>Click here to download it</a>.";
?>