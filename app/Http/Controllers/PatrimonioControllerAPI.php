<?php

namespace App\Http\Controllers;

use App\Patrimonio;
use Illuminate\Http\Request;

class PatrimonioControllerAPI extends Controller
{
    public function patrimoniosDataTable(Request $request)
    {
        $columns = ['nome', 'distrito', 'epoca', 'ciclo'];
        $length = $request->input('length');
        $column = $request->input('column');
        $dir = $request->input('dir');
        $searchNome = $request->input('search');
        $filterByDistrito = $request->input('distrito');
        $filterByEpoca = $request->input('epoca');
        $hasFilterCiclo = $request->input('ciclo.0');
        $filterCiclo = [];
        if($hasFilterCiclo){
            foreach ($request->input('ciclo') as $value){
                array_push($filterCiclo, $value);
            }
        }
        $query = Patrimonio::orderBy($columns[$column], $dir);
        if($hasFilterCiclo){
            $query->where(function($query) use ($filterCiclo) {
                foreach ($filterCiclo as $ciclo){
                    $query->orWhere('ciclo', '=', $ciclo);
                }
            });
        }
        if ($searchNome) {
            $query->where(function($query) use ($searchNome) {
                $query->where('nome', 'like', '%' . $searchNome . '%');
            });
        }
        if ($filterByDistrito && $filterByDistrito !== "Todos") {
            $query->where(function($query) use ($filterByDistrito) {
                $query->where('distrito', $filterByDistrito);
            });
        }
        if ($filterByEpoca && $filterByEpoca !== "Todas") {
            $query->where(function($query) use ($filterByEpoca) {
                $query->where('epoca', $filterByEpoca);
            });
        }
        $patrimonios = $query->paginate($length);
        return ['data' => $patrimonios, 'draw' => $request->input('draw')];
    }

    public function getAllDistritos()
    {
        return Patrimonio::distinct('distrito')->select('distrito')->get();
    }

    public function getAllEpocas()
    {
        return Patrimonio::distinct('epoca')->select('epoca')->get();
    }
}
