<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class SisTipoController extends Controller
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

    public static function getIdByCodGeneral($codGeneral){
        return DB::table('sis_tipo')
        ->select('sis_tipo.id')
        ->where('cod_general', '=', $codGeneral)
        ->get()->first()->id;
    }

    public static function getByCodGeneralGrupo($codGeneral){
        return DB::table('sis_tipo')
        ->leftjoin('sis_clase', 'sis_clase.id', '=', 'sis_tipo.clase')
        ->select('sis_tipo.*')
        ->where('sis_clase.cod_general', '=', $codGeneral)
        ->get();
    }

   

}
