<?php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;

class SimpleArrayImport implements ToArray
{
    /** @var array<int,array<int,mixed>> */
    public array $rows = [];

    /**
     * @param array<int,array<int,mixed>> $array
     */
    public function array(array $array)
    {
        $this->rows = $array;
    }
}
