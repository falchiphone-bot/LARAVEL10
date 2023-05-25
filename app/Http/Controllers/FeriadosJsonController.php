<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feriado;

class FeriadoJsonController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->query('data');
        $nome = $request->query('nome');

        $feriados = Feriado::when($data, function($query, $data) {
                return $query->where('data', $data);
            })
            ->when($nome, function($query, $nome) {
                return $query->where('nome', 'like', "%$nome%");
            })
            ->get();

        return response()->json($feriados);
    }
}
