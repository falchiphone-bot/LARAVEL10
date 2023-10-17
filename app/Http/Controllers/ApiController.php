<?php

namespace App\Http\Controllers;

use App\Models\webhook;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    //
    public function index(Request $r){
        // return ['hello'=> 'word'];
        // webhook::create(['webhook' => 'teste', 'type'=>'123']);

        // $data = $r->all();
        // webhook::create(['webhook' => implode(',',$data), 'type'=>'123']);


        $data = $r->all();
        $request_type = $r->method();
        $dataString = json_encode($data);

        webhook::create(['webhook' => $dataString, 'type'=>$request_type]);

        // return $data['hub_challenge'];

        // return ['sucess' => true];
    }
}
