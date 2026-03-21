<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InventoryAlertService;

class DailyInventoryCheck extends Command
{
    protected $signature = 'inventory:daily-check';
    protected $description = 'Quét toàn bộ kho kiểm tra cận date hằng ngày';

    protected $alertService;

    public function __construct(InventoryAlertService $alertService)
    {
        parent::__construct();
        $this->alertService = $alertService;
    }

    public function handle()
    {
        $this->info('Bắt đầu kiểm tra kho hằng ngày...');
        $this->alertService->checkDailyAlerts();
        $this->info('Hoàn tất kiểm tra!');
    }
}