<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Mail;
use App\Traits\SpaceUtil;

class ReportesController extends Controller
{
    use SpaceUtil;
    public function __construct()
    {

        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
    }



    public static function enviarCorreoConsumoDiaAnterior()
    {
        $sucursales = SpaceUtil::getSucursalesAll();

        ReportesController::agregarDatosConMPDiaAnterior($sucursales);
        ReportesController::agregarDatosIngMovProdExtDiaAnterior($sucursales);
        ReportesController::agregarDatosSalidasMovProdExtDiaAnterior($sucursales);
        ReportesController::agregarDatosDesechoMovProdExtDiaAnterior($sucursales);
        ReportesController::agregarDatosVentasProdExtDiaAnterior($sucursales);
        ReportesController::agregarDatosMateriaPrimaBaja($sucursales);

        $data = [
            'sucursales' =>  $sucursales,
            'fechaReporte' => SpaceUtil::soloFechaFormat(date('Y-m-d', strtotime('-1 day')))
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

    public static function agregarDatosConMPDiaAnterior($sucursales)
    {

        foreach ($sucursales as $s) {

            $query = "SELECT suc.descripcion as nombreSucursal,pe.nombre as nombreProducto,pe.unidad_medida,sum(inv.cantidad_ajuste) as suma,pe.precio as precio_unidad, (sum(inv.cantidad_ajuste) * pe.precio) as costo, mts.cantidad as cantTotalMp " .
                "FROM coffee_to_go.bit_materia_prima inv join  coffee_to_go.usuario usu on usu.id = inv.usuario " .
                "join coffee_to_go.materia_prima pe on pe.id = inv.materia_prima join coffee_to_go.sucursal suc on suc.id = inv.sucursal 
                 join coffee_to_go.mt_x_sucursal mts on mts.sucursal = suc.id and mts.materia_prima = pe.id";
            $where = " where inv.cantidad_anterior > inv.cantidad_nueva and inv.detalle like '%Rebajo por venta%'";

            $where .= " and inv.fecha >= '" . date('Y-m-d', strtotime('-1 day')) . "'";

            $where .= " and inv.sucursal = " . $s->id;

            $where .= " and inv.fecha <= '" . date('Y-m-d') . "'";


            $query .= $where . " group by suc.descripcion,pe.nombre,pe.unidad_medida,pe.precio,mts.cantidad order by 6 DESC";
            $datos = DB::select($query);
            $costoTotalMp = 0;
            foreach ($datos as $v) {
                $costoTotalMp = $costoTotalMp + $v->costo;
            }

            $s->reporteConsumoMp = $datos;
            $s->costoTotalReporteConsumoMp = $costoTotalMp;
        }
    }

    public static function agregarDatosIngMovProdExtDiaAnterior($sucursales)
    {

        foreach ($sucursales as $s) {

            $queryIngresos = "SELECT pe.nombre as nombreProducto,pes.cantidad as cantInventarioActual ,sum(inv.cantidad_ajustada) as ingreso" .
                " FROM coffee_to_go.bit_inv_producto_externo inv join  coffee_to_go.usuario usu on usu.id = inv.usuario " .
                " join coffee_to_go.producto_externo pe on pe.id = inv.producto " .
                " join coffee_to_go.pe_x_sucursal pes on pes.producto_externo = pe.id " .
                " join coffee_to_go.sucursal suc on suc.id = inv.sucursal";

            $where = " where inv.cantidad_anterior < cantidad_nueva";
            $where .= " and inv.fecha >= '" . date('Y-m-d', strtotime('-1 day')) . "'";
            $where .= " and inv.sucursal = " . $s->id;
            $where .= " and inv.fecha < '" . date('Y-m-d') . "'";
            $queryIngresos .= $where . " group by  pe.nombre,pes.cantidad,suc.id  ";
            $datosIngresos = DB::select($queryIngresos);

            $s->reporteMovIngresos = $datosIngresos;
        }
    }

    public static function agregarDatosSalidasMovProdExtDiaAnterior($sucursales)
    {

        foreach ($sucursales as $s) {

            $querySalidas = "SELECT pe.nombre as nombreProducto,pes.cantidad as cantInventarioActual ,sum(inv.cantidad_ajustada) as salida" .
                " FROM coffee_to_go.bit_inv_producto_externo inv join  coffee_to_go.usuario usu on usu.id = inv.usuario " .
                " join coffee_to_go.producto_externo pe on pe.id = inv.producto " .
                " join coffee_to_go.pe_x_sucursal pes on pes.producto_externo = pe.id " .
                " join coffee_to_go.sucursal suc on suc.id = inv.sucursal";

            $where = " where inv.cantidad_anterior > cantidad_nueva and inv.devolucion = 'N' ";
            $where .= " and inv.fecha >= '" . date('Y-m-d', strtotime('-1 day')) . "'";
            $where .= " and inv.sucursal = " . $s->id;
            $where .= " and inv.fecha < '" . date('Y-m-d') . "'";
            $querySalidas .= $where . " group by  pe.nombre,pes.cantidad,suc.id ";

            $datosSalidas = DB::select($querySalidas);

            $s->reporteMovSalidas = $datosSalidas;
        }
    }

    public static function agregarDatosDesechoMovProdExtDiaAnterior($sucursales)
    {

        foreach ($sucursales as $s) {

            $queryDesechos = "SELECT pe.nombre as nombreProducto,pes.cantidad as cantInventarioActual ,sum(inv.cantidad_ajustada) as desecho" .
                " FROM coffee_to_go.bit_inv_producto_externo inv join  coffee_to_go.usuario usu on usu.id = inv.usuario " .
                " join coffee_to_go.producto_externo pe on pe.id = inv.producto " .
                " join coffee_to_go.pe_x_sucursal pes on pes.producto_externo = pe.id " .
                " join coffee_to_go.sucursal suc on suc.id = inv.sucursal";

            $where = " where inv.cantidad_anterior > cantidad_nueva and inv.devolucion = 'S' ";
            $where .= " and inv.fecha >= '" . date('Y-m-d', strtotime('-1 day')) . "'";
            $where .= " and inv.sucursal = " . $s->id;
            $where .= " and inv.fecha < '" . date('Y-m-d') . "'";
            $queryDesechos .= $where . " group by  pe.nombre,pes.cantidad,suc.id ";

            $datosDesechos = DB::select($queryDesechos);

            $s->reporteMovDesechos = $datosDesechos;
        }
    }

    public static function agregarDatosVentasProdExtDiaAnterior($sucursales)
    {

        foreach ($sucursales as $s) {

            $queryVentas = "SELECT do.nombre_producto,sum(do.cantidad) as cantidad FROM coffee_to_go.detalle_orden do ".
              "join coffee_to_go.orden o on o.id = do.orden";

            $where = " where o.estado <> 5 and do.tipo_producto = 'E' ";
            $where .= " and o.fecha_inicio >= '" . date('Y-m-d', strtotime('-1 day')) . "'";
            $where .= " and o.sucursal = " . $s->id;
            $where .= " and o.fecha_inicio < '" . date('Y-m-d') . "'";
            $queryVentas .= $where . " group by do.nombre_producto order by 2 DESC ";

            $datosVentas = DB::select($queryVentas);

            $s->reporteVentasProdExt= $datosVentas;
        }
    }

    public static function agregarDatosMateriaPrimaBaja($sucursales)
    {

        foreach ($sucursales as $s) {

            $queryInv = "SELECT mp.*, s.cantidad as cant_inventario, s.sucursal as sucursal, p.nombre as nombreProveedor,(mp.cant_min_deseada - s.cantidad) as cantPendiente,(mp.cant_min_deseada - s.cantidad) * mp.precio as mtoPendiente FROM coffee_to_go.materia_prima mp join " .
                " coffee_to_go.mt_x_sucursal s on s.materia_prima = mp.id " .
                " left join coffee_to_go.proveedor p on p.id = mp.proveedor".
                " where s.cantidad < mp.cant_min_deseada and mp.cant_min_deseada > 0 " .
                " and s.sucursal = " .  $s->id;
                "  order by s.cantidad ASC";

            $datosInv = DB::select($queryInv);

            $s->reporteMateriaPrimaBaja = $datosInv;
        }
    }

}
