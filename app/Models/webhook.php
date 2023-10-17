<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class webhook extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = ['webhook','type'];

    protected $tableName = 'webhooks';

}
