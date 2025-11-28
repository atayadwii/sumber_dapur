<?php

namespace App\Console\Commands;

use App\Models\Pesanan;
use Illuminate\Console\Command;

class CheckExpiredOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and mark expired orders that haven\'t been paid';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Tidak ada auto-expire lagi, payment deadline dihapus
        // Command ini bisa dihapus atau digunakan untuk keperluan lain
        $this->info("Payment deadline feature disabled. No orders to expire.");
        
        return 0;
    }
}
