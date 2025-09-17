<?php

namespace App\Http\Controllers;

use App\Models\SafClube;
use App\Models\SafFederacao;
use App\Models\SafCampeonato;

class TanabiSafPublicController extends Controller
{
    // Página pública para listar clubes (sem auth)
    public function clubes(\Illuminate\Http\Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $uf = trim((string) $request->query('uf', ''));
        $pais = trim((string) $request->query('pais', ''));
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));

        $query = SafClube::query();
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('nome','like',"%{$q}%")
                  ->orWhere('cidade','like',"%{$q}%");
            });
        }
        if ($uf !== '') { $query->where('uf', $uf); }
        if ($pais !== '') { $query->where('pais','like',"%{$pais}%"); }

        $clubes = $query->orderBy('nome')->paginate($perPage)->appends($request->query());
        return view('tanabisaf.saf-clubes', compact('clubes','q','uf','pais','perPage'));
    }

    // Página pública para listar federações
    public function federacoes(\Illuminate\Http\Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $uf = trim((string) $request->query('uf', ''));
        $pais = trim((string) $request->query('pais', ''));
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));
        $query = SafFederacao::query();
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('nome','like',"%{$q}%")
                  ->orWhere('cidade','like',"%{$q}%");
            });
        }
        if ($uf !== '') { $query->where('uf',$uf); }
        if ($pais !== '') { $query->where('pais','like',"%{$pais}%"); }
        $federacoes = $query->orderBy('nome')->paginate($perPage)->appends($request->query());
        return view('tanabisaf.saf-federacoes', compact('federacoes','q','uf','pais','perPage'));
    }

    // Página pública para listar campeonatos
    public function campeonatos(\Illuminate\Http\Request $request)
    {
        $q = trim((string) $request->query('q',''));
        $uf = trim((string) $request->query('uf',''));
        $pais = trim((string) $request->query('pais',''));
    $federacaoId = $request->query('federacao_id');
    $anoId = $request->query('ano_id');
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min(100, $perPage));
    $query = SafCampeonato::with(['federacao','categorias','ano']);
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('nome','like',"%{$q}%")
                  ->orWhere('cidade','like',"%{$q}%");
            });
        }
        if ($uf !== '') { $query->where('uf',$uf); }
        if ($pais !== '') { $query->where('pais','like',"%{$pais}%"); }
    if (!empty($federacaoId)) { $query->where('federacao_id',(int)$federacaoId); }
    if (!empty($anoId)) { $query->where('ano_id', (int) $anoId); }
    $campeonatos = $query->orderBy('nome')->paginate($perPage)->appends($request->query());
    $federacoes = SafFederacao::orderBy('nome')->get();
    $anos = \App\Models\SafAno::orderBy('ano','desc')->get();
    return view('tanabisaf.saf-campeonatos', compact('campeonatos','federacoes','anos','q','uf','pais','federacaoId','anoId','perPage'));
    }
}
