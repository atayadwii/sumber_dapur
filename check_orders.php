<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$pesanan = \App\Models\Pesanan::orderBy('id', 'desc')->take(5)->get(['id', 'user_pembeli_id', 'user_penjual_id', 'status_pesanan', 'total_harga', 'created_at']);

echo "=== LAST 5 ORDERS ===\n";
foreach ($pesanan as $p) {
    echo "ID: {$p->id} | Status: {$p->status_pesanan} | Total: Rp {$p->total_harga} | Created: {$p->created_at}\n";
}
