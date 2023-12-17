<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContatosWhatsappCreateRequest;
use App\Models\ContatosWhatsapp;
use App\Models\webhookContact;
use App\Services\WebhookContactsServico;
use App\Models\webhook;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;



class ContatosWhatsappController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:ContatosWhatsapp - LISTAR'])->only('index');
        $this->middleware(['permission:ContatosWhatsapp - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:ContatosWhatsapp - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:ContatosWhatsapp - VER'])->only(['show']);
        $this->middleware(['permission:ContatosWhatsapp - EXCLUIR'])->only('destroy');
    }

    public function index()
    {
       $model= null;

    //     $mensagem = webhook::orderby('created_at','desc')->first();

    //     $recipient_id = trim($mensagem->messagesFrom);
    //     $entry_id = trim($mensagem->entry_id);

    // /   $registro = webhookContact::
    //        where('recipient_id', $recipient_id)
    //      ->where('entry_id', $entry_id)
    //      ->orderBy('id', 'desc')
    //      ->get();


    //      dd( $registro,$recipient_id , $entry_id, $mensagem );

            if (Gate::allows('WHATSAPP_ENTRY_ID_167722543083127')
              && Gate::allows('WHATSAPP_ENTRY_ID_189514994242034'
              && Gate::allows('WHATSAPP_ENTRY_ID_ 179613235241221'))) {
                $model = webhookContact::where('contactName', '!=', '')
                    ->where(function($query) {
                        $query->where('entry_id', '167722543083127')
                            ->orWhere('entry_id', '189514994242034')
                            ->orWhere('entry_id', '179613235241221');;
                    })
                    ->orderBy('contactName')
                    ->get();
            }

        else
       if (Gate::allows('WHATSAPP_ENTRY_ID_167722543083127')) {
           $model= webhookContact::Where('contactName','!=','')
           ->where('entry_id',167722543083127)
           ->OrderBy('contactName')->get();
       }
       else
       if (Gate::allows('WHATSAPP_ENTRY_ID_189514994242034')) {
            $model= webhookContact::Where('contactName','!=','')
            ->where('entry_id',189514994242034)
            ->OrderBy('contactName')->get();
        }
        else
        if (Gate::allows('WHATSAPP_ENTRY_ID_179613235241221')) {
             $model= webhookContact::Where('contactName','!=','')
             ->where('entry_id',179613235241221)
             ->OrderBy('contactName')->get();
         }


     if($model == null)
     {
         session(['error' => 'Nada pesquisado! Usuário sem permissão de acesso!']);
         return redirect(route('whatsapp.atendimentoWhatsapp'));
     }

        return view('ContatosWhatsapp.index',compact('model'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('ContatosWhatsapp.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContatosWhatsappCreateRequest $request)
    {
        $request["contactName"] = strtoupper($request["contactName"]);
        $existecadastro = webhookContact::where('contactName',trim($request["contactName"]))->first();
        if($existecadastro)
        {
            session(['error' => "NOME:  ". $request->nome  .", já existe! NADA INCLUÍDO! "]);
            return redirect(route('ContatosWhatsapp.index'));
        }


        $model= $request->all();


        webhookContact::create($model);
        session(['success' => "CONTATO:  ". $request->contactName  .",  INCLUÍDO COM SUCESSO!"]);
        return redirect(route('ContatosWhatsapp.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = webhookContact::find($id);
        return view('ContatosWhatsapp.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $model= webhookContact::find($id);


        return view('ContatosWhatsapp.edit',compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        // $request["contactName"] = strtoupper($request["contactName"]);
        // $existecadastro = webhookContact::where('contactName',trim($request["contactName"]))->first();
        // if($existecadastro)
        // {
        //     session(['error' => "NOME:  ". $request->contactName  .", já existe ou não precisa ser alterado! "]);
        //     return redirect(route('ContatosWhatsapp.index'));
        // }


        $cadastro = webhookContact::find($id);

        $cadastro->fill($request->all()) ;

// dd($request->all());
        $cadastro->save();


        return redirect(route('ContatosWhatsapp.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {

    //    $Posicao = Posicoes::where('tipo_esporte', $id)->get();

    //    if($Posicao->Count() > 0)
    //    {

    //     session(['error' => "TIPO DE ESPORTE:  ". $request->nome  .",  SELECIONADO! NÃO PODE SER EXCLUÍDO POIS ESTÁ SENDO USADO! RETORNADO A SITUAÇÃO ANTERIOR. ATENÇÃO!"]);
    //     return redirect(route('ContatosWhatsapp.index'));
    //    }


        $model= webhookContact::find($id);

        $model->delete();

       session(['success' => "Contato:  ". $model->nome  .",  EXCLUÍDO COM SUCESSO!"]);
        return redirect(route('ContatosWhatsapp.index'));

    }

    public function temposessaocontato()
    {
        $entry_id = '16772254308312';
        $alerta= webhookContact::
        where('alerta_mensagem_recebida', 1)
        ->orwhere('entry_id', $entry_id)
        ->orderby('recipient_id')
        ->get();



        foreach($alerta as $contatos)
        {
           $TempoSessao = WebhookContactsServico::temposessao($contatos);
           echo ' Contato: ' , $contatos->contactName, ' Tempo da sessão: ' ,$TempoSessao['parte_inteira'], " ";

        }





    }

}
