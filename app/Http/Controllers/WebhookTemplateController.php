<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebhookTemplateCreateRequest;
use App\Models\WebhookTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class WebhookTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission: WebhookTemplate - LISTAR'])->only('index');
        $this->middleware(['permission: WebhookTemplate - INCLUIR'])->only(['create', 'store']);
        $this->middleware(['permission: WebhookTemplate - EDITAR'])->only(['edit', 'update']);
        $this->middleware(['permission: WebhookTemplate - VER'])->only(['edit', 'update']);
        $this->middleware(['permission: WebhookTemplate - EXCLUIR'])->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */



    public function index()
    {
       $WebhookTemplate = WebhookTemplate::OrderBy('name')->get();


        return view('WebhookTemplate.index',compact('WebhookTemplate'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('WebhookTemplate.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(WebhookTemplateCreateRequest $request)
    {
        $WebhookTemplate= $request->all();


        WebhookTemplate::create($WebhookTemplate);



        return redirect(route('Templates.index'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cadastro = WebhookTemplate::find($id);
        return view('WebhookTemplate.show',compact('cadastro'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $WebhookTemplate = WebhookTemplate::find($id);
        // dd($cadastro);

        return view('WebhookTemplate.edit',compact('WebhookTemplate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $cadastro = WebhookTemplate::find($id);

        $cadastro->fill($request->all()) ;


        $cadastro->save();


        return redirect(route('Templates.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $WebhookTemplate= WebhookTemplate::find($id);


        $WebhookTemplate->delete();
        return redirect(route('Templates.index'));

    }
}
