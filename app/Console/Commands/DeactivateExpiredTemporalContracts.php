<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contract;
use Carbon\Carbon;

class DeactivateExpiredTemporalContracts extends Command
{
    protected $signature = 'contracts:deactivate-expired';
    protected $description = 'Desactiva automáticamente los contratos temporales que han finalizado';

    public function handle()
    {
        $today = Carbon::today();
        
        $expiredContracts = Contract::where('contract_type', 'Temporal')
            ->where('is_active', true)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', $today)
            ->get();

        $count = $expiredContracts->count();

        foreach ($expiredContracts as $contract) {
            $contract->update([
                'is_active' => false,
                'termination_reason' => 'Contrato temporal finalizado automáticamente por el sistema'
            ]);
        }

        $this->info("Se desactivaron {$count} contratos temporales vencidos.");
        
        return 0;
    }
}
