<?php
/// efetuado por Pedro Roberto Falchi
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoedasValores extends Model
{
    protected $table = 'moedavalores';
    public $timestamps = false;

    protected $fillable = ['idmoeda', 'data', 'valor', 'created_at', 'updated_at'];

    // protected $casts = [

    //     'data' => 'date:d/m/Y',
    // ];
}
