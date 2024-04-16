<?php

namespace App\Http\Controllers;
use App\Models\Ixc\ClientIxc;
use Illuminate\Support\Facades\Validator;
use App\Models\Ixc\vd_contratos;

use function PHPUnit\Framework\assertNotEmpty;

class ClientesIxcController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:CLIENTESIXCNETRUBI - LISTAR'])->only('index');
    }


    public function dashboard()
    {
        return view('Ixc/Clientes/dashboard');
    }


    public function index()
    {
       $clientes = ClientIxc::limit(500)
       ->orderBy('id','desc')
       ->get();

       $CadastroClientesAtivo = ClientIxc::
       where('ativo','S')
       ->count();
       $CadastroClientesNaoAtivo = ClientIxc::
       where('ativo','N')
       ->count();
       $CadastroClientes = ClientIxc::count();

       $status_internet_Ativo = ClientIxc::
       where('status_internet','A')
       ->count();

       $status_internet_NAOAtivo = ClientIxc::
       where('status_internet','N')
       ->count();


       $tem_whatsapp = ClientIxc::
       where('whatsapp', '!=' ,'')
       ->count();

        return view('Ixc/Clientes.index',compact('clientes',
         'CadastroClientesAtivo',
         'CadastroClientesNaoAtivo',
         'CadastroClientes',
         'status_internet_Ativo',
         'status_internet_NAOAtivo',
         'tem_whatsapp',
        ));
    }

    public function contratos_ixc_tv()
    {

            $contratos = vd_contratos::select('id', 'nome', 'valor_contrato')
                ->where('nome', 'like', '%tv%')
                ->get();

                dd($contratos, $contratos->count());
   }
   public function contratos_ixc_app()
   {

           $contratos = vd_contratos::select('id', 'nome', 'valor_contrato')
               ->where('nome', 'like', '%app%')
               ->get();

               dd($contratos, $contratos->count());
  }



}

