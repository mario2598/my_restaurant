<?php

namespace App\Console\Commands;
use App\Http\Controllers\SisParametroController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class reporteDiarioConsumo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reporteDiarioConsumo';

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

        $query = "SELECT suc.descripcion as nombreSucursal,pe.nombre as nombreProducto,pe.unidad_medida,sum(inv.cantidad_ajuste) as suma,pe.precio as precio_unidad, (sum(inv.cantidad_ajuste) * pe.precio) as costo " .
            "FROM coffee_to_go.bit_materia_prima inv join  coffee_to_go.usuario usu on usu.id = inv.usuario " .
            "join coffee_to_go.materia_prima pe on pe.id = inv.materia_prima join coffee_to_go.sucursal suc on suc.id = inv.sucursal ";
        $where = " where inv.cantidad_anterior > inv.cantidad_nueva ";

        $where .= " and inv.fecha >= '" . date('Y-m-d', strtotime('-1 day')) . "'";

        $where .= " and inv.fecha <= '" . date('Y-m-d') . "'";

        $query .= $where . " group by suc.descripcion,pe.nombre,pe.unidad_medida,pe.precio order by 6 DESC";

        $datos = DB::select($query);
        $costoTotalMp = 0;
        foreach ($datos as $v) {
            $costoTotalMp = $costoTotalMp + $v->costo;
        }

        $data = [
            'datosReporte' =>  $datos,
            'costoTotalMp' =>  $costoTotalMp
            //'fechaReporte' => $this->fechaFormat(date('Y-m-d', strtotime('-1 day')))
        ];

        $correosString = SisParametroController::getValorByCodGeneral('CORREOS_REP_CONSUMO_GEN');
        $correosArray = explode(",", $correosString);

        $receptores = [];

        foreach ($correosArray as $correo) {
            $partesCorreo = explode("@", $correo);
            $nombre = $partesCorreo[0];
            $receptores[] = ['nombre' => $nombre, 'correo' => $correo]; 
        }
       
        $asunto = SisParametroController::getValorByCodGeneral('ASUNTO_REP_CONSUMO_GEN');
        $envia = SisParametroController::getValorByCodGeneral('CORREO_ENVIO_NOT_CLIENTE');
        $nombreEnvia = SisParametroController::getValorByCodGeneral('NOMBRE_ENVIO_NOT_CLIENTE');

        Mail::send("emails.reportes.reporteDiarioConsumoGen", ['data' => $data], function ($m) use ($envia, $nombreEnvia, $asunto, $receptores) {
            $m->from($envia, $nombreEnvia);

            foreach ($receptores as $receptor) {
                $m->bcc($receptor['correo'], $receptor['nombre']);
            }

            $m->subject($asunto);

        });

    }
}
