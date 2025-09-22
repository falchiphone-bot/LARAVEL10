<?php

namespace App\Services;

class DashboardCache
{
    /**
     * Retorna contadores do bloco Cadastros.
     */
    public static function cadastrosCounts(): array
    {
        return [
            'representantes' => class_exists(\App\Models\Representantes::class) ? \App\Models\Representantes::count() : null,
            'preparadores'   => class_exists(\App\Models\Preparadores::class) ? \App\Models\Preparadores::count() : null,
            'funcao'         => class_exists(\App\Models\FuncaoProfissional::class) ? \App\Models\FuncaoProfissional::count() : null,
            'categorias'     => class_exists(\App\Models\Categorias::class) ? \App\Models\Categorias::count() : null,
            'posicoes'       => class_exists(\App\Models\Posicoes::class) ? \App\Models\Posicoes::count() : null,
            'tipoarquivo'    => class_exists(\App\Models\TipoArquivo::class) ? \App\Models\TipoArquivo::count() : null,
            'tipoesporte'    => class_exists(\App\Models\TipoEsporte::class) ? \App\Models\TipoEsporte::count() : null,
        ];
    }

    /**
     * Retorna contadores do bloco Atletas.
     */
    public static function athletesCounts(): array
    {
        return [
            'formandos'   => class_exists(\App\Models\FormandoBase::class) ? \App\Models\FormandoBase::count() : null,
            'flow'        => class_exists(\App\Models\FormandoBaseWhatsapp::class) ? \App\Models\FormandoBaseWhatsapp::count() : null,
            'percentuais' => class_exists(\App\Models\TanabiAthletePercentage::class) ? \App\Models\TanabiAthletePercentage::count() : null,
        ];
    }
}
