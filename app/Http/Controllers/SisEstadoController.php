<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;
use App\Traits\SpaceUtil;

class SisEstadoController extends Controller
{
    use SpaceUtil;
    private $admin;
    public function __construct()
    {
        setlocale(LC_ALL, "es_CR");
    }

    public function index()
    {
    }

   
    public static function getIdEstadoByCodGeneral($codGeneral){
        return DB::table('sis_estado')
        ->select('sis_estado.id')
        ->where('cod_general', '=', $codGeneral)
        ->get()->first()->id;
    }

}
