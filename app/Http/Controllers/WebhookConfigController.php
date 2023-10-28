<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebhookConfigCreateRequest;
use App\Models\WebhookConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class WebhookConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission: WebhookConfig - LISTAR'])->only('index');
        $this->middleware(['permission: WebhookConfig - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission: WebhookConfig - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission: WebhookConfig - VER'])->only(['edit', 'update']);
        $this->middleware(['permission: WebhookConfig - EXCLUIR'])->only('destroy');
    }


    public function index()
    {
       $WebhookConfig = WebhookConfig::OrderBy('usuario')->get();


        return view('WebhookConfig.index',compact('WebhookConfig'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('WebhookConfig.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(WebhookConfigCreateRequest $request)
    {
        $WebhookConfig= $request->all();


        WebhookConfig::create($WebhookConfig);



        return redirect(route('WebhookConfig.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = WebhookConfig::find($id);
        return view('WebhookConfig.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $WebhookConfig = WebhookConfig::find($id);
        // dd($cadastro);

        return view('WebhookConfig.edit',compact('WebhookConfig'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = WebhookConfig::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('WebhookConfig.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $WebhookConfig= WebhookConfig::find($id);


        $WebhookConfig->delete();
        return redirect(route('WebhookConfig.index'));

    }
}
