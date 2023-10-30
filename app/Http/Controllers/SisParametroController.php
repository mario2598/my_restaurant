<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class SisParametroController extends Controller
{
    use SpaceUtil;
    private $admin;
    public function __construct()
    {
        setlocale(LC_ALL, "es_ES");
    }

    public function index()
    {
    }

   
    public static function getValorByCodGeneral($codGeneral){
        return DB::table('sis_parametro')
        ->select('sis_parametro.valor')
        ->where('cod_general', '=', $codGeneral)
        ->get()->first()->valor;
    }

}
