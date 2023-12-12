<?php

namespace App\Console\Commands;

use App\Http\Controllers\ReportesController;
use App\Http\Controllers\SisParametroController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Traits\SpaceUtil;

class reporteDiarioConsumo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reporteDiarioConsumo';
    use SpaceUtil;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reporte de consumo diario';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ReportesController::enviarCorreoConsumoDiaAnterior();
    }
}
