<?php
/// efetuado por Pedro Roberto Falchi em 18/03/2023 as 19:44
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Model_hasRoles extends Model
{
    protected $connection = 'sqlsrv';
    public $timestamps = false;

    protected $fillable = [
        'role_id',
        'model_type',
        'model_id',
        ];
}
