<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PlainArrayImport implements ToArray, WithHeadingRow
{
    public function array(array $array)
    {
        // Retorna como está; o WithHeadingRow garante linhas associativas pelos cabeçalhos
        return $array;
    }
}
