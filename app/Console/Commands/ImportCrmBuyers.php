<?php

namespace App\Console\Commands;

use App\Services\CrmBuyerImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ImportCrmBuyers extends Command
{
    protected $signature = 'crm:import-buyers';
    protected $description = 'Add customer accounts and paid photo buyers to the CRM contact directory (no consent is recorded).';

    public function handle(CrmBuyerImporter $buyerImporter): int
    {
        $examined = $buyerImporter->import();

        Log::info('CRM buyer import finished.', ['event' => 'crm.buyers.imported'] + $examined);
        $this->info(sprintf(
            'Examined %d customer accounts and %d paid order emails.',
            $examined['customerAccounts'],
            $examined['orderEmails']
        ));

        return self::SUCCESS;
    }
}
