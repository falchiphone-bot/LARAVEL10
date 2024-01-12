<?php

namespace App\Http\Controllers;

use App\Helpers\SicredApiHelper;
use App\Models\Atletas\CobrancaSicredi;
use Illuminate\Http\Request;
use Carbon\Carbon;


class SicrediController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()


    {
      
        return view('Sicredi.index');
    }

}
