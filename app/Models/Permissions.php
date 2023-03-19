<?php
/// efetuado por Pedro Roberto Falchi
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permissions extends Model
{
    protected $connection = 'sqlsrv';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'guard_name',
        'created_at',
        'updated_at',
    ];
}
