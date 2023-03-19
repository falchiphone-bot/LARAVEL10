<?php
/// efetuado por Pedro Roberto Falchi em 19/03/2023 as 11:10
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role_hasPermissions extends Model
{
    protected $connection = 'sqlsrv';
    public $timestamps = false;

    protected $fillable = [
        'permission_id',
        'role_id',
        ];
}
