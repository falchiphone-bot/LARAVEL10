<?php

namespace App\Http\Controllers;

use App\Models\FormandoBaseAvaliacao;
use App\Models\FormandoBasePosicoes;
use Dompdf\Dompdf;
use Dompdf\Options;
use Dompdf\FrameDecorator;
use Illuminate\Http\Request;



class FormandoBaseAvaliacaoController  extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:FORMANDOBASEAVALIACAO - LISTAR'])->only('index');
        $this->middleware(['permission:FORMANDOBASEAVALIACAO - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission:FORMANDOBASEAVALIACAO - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission:FORMANDOBASEAVALIACAO - VER'])->only(['edit', 'update']);
        $this->middleware(['permission:FORMANDOBASEAVALIACAO - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */



    public function index(Request $request)
    {
    //    $model= FormandoBaseAvaliacao::OrderBy('created_at')->get();


    $model = FormandoBaseAvaliacao::query();

    if ($request->has('sort')) {
        $sortOption = $request->input('sort');

        switch ($sortOption) {
            case 'datenew':
                $model->orderBy('created_at', 'desc');
                break;
            case 'date':
                $model->orderBy('created_at', 'asc');
                break;
            case 'score':
                $model->orderBy('avaliacao', 'desc');
                break;
            case 'name':
                $model->join('FormandoBase', 'FormandoBase.id', '=', 'FormandoBaseAvaliacao.formandobase_id')
                      ->orderBy('FormandoBase.nome', 'asc');
                break;
        }
    }

    if ($request->has('formandobase_id')) {
        $formandobaseId = $request->input('formandobase_id');
        $model->where('formandobase_id', $formandobaseId);
    }

    $model = $model->get();

    $GerarPdf = null;
    if ($request->has('pdfgerar')) {
        $GerarPdf = $request->input('pdfgerar');
    }
    if(!$GerarPdf){
      return view('FormandoBaseAvaliacao.index',compact('model'));
    }
    else{
       $view = view('FormandoBaseAvaliacao.indexpdf',compact('model'))->render();


      ob_start();
      $suaView = $view;
      // Imprima o conteúdo HTML
      echo $suaView;

// dd('parado');

      $conteudoHTML = ob_get_clean();

      $options = new Options();
      $options->set('isHtml5ParserEnabled', true);
      $options->set('isPhpEnabled', true);
      $pdf = new Dompdf($options);

      $suaView = $conteudoHTML;

      $pdf->loadHtml($suaView);

      $pdf->render();


      if ($_SERVER["REQUEST_METHOD"] == "POST") {
          // Verifique se o campo "pdfgerar" está definido na solicitação POST
          if (isset($_POST["pdfgerar"])) {
              // Acesse o valor selecionado com base no atributo "name"
              $pdfgerado = $_POST["pdfgerar"];

              if ($pdfgerado === "pdfdownload") {
                  // Ação para o radio button com "value" igual a "pdfdownload"
                  // Faça o que for necessário aqui
                  $pdf->stream('pdf_de_avaliacoes_formandos.pdf', array("Attachment" => true));
              } elseif ($pdfgerado === "pdfvisualizar") {
                  // Ação para o radio button com "value" igual a "pdfvisualizar"
                  // Faça o que for necessário aqui
                  $pdf->stream('pdf_de_avaliacoes_formandos.pdf', array("Attachment" => false));
              }
          }
      }



    }





    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // return view('RedeSocial.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RedeSocialCreateRequest $request)
    {
        // $request["nome"] = strtoupper($request["nome"]);
        // $existecadastro = RedeSocial::where('nome',trim($request["nome"]))->first();
        // if($existecadastro)
        // {
        //     session(['error' => "NOME:  ". $request->nome  .", já existe! NADA INCLUÍDO! "]);
        //     return redirect(route('RedeSocial.index'));
        // }

        // $request['user_created'] = Auth::user()->email;

        // $model= $request->all();


        // RedeSocial::create($model);
        // session(['success' => "TIPO DE ESPORTE:  ". $request->nome  .",  INCLUÍDO COM SUCESSO!"]);
        // return redirect(route('RedeSocial.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // $cadastro = RedeSocial::find($id);
        // return view('RedeSocial.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // $model= RedeSocial::find($id);


        // return view('RedeSocial.edit',compact('model'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        // $request["nome"] = strtoupper($request["nome"]);
        // $existecadastro = RedeSocial::where('nome',trim($request["nome"]))->first();
        // if($existecadastro)
        // {
        //     session(['error' => "NOME:  ". $request->nome  .", já existe ou não precisa ser alterado! "]);
        //     return redirect(route('RedeSocial.index'));
        // }


        // $cadastro = RedeSocial::find($id);


        // $request['user_updated'] = Auth::user()->email;
        // $cadastro->fill($request->all()) ;


        // $cadastro->save();


        // return redirect(route('RedeSocial.index'));
    }


    public function destroy(Request $request, string $id)
    {

        $model= FormandoBaseAvaliacao::find($id);

        $model->delete();

        session(['success' => "AVALIAÇÃO EXCLUÍDA COM SUCESSO!"]);
        return redirect(route('FormandoBase.index'));

    }
}
