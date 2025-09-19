<?php

namespace App\Http\Controllers;

use App\Http\Requests\FormaPagamentoRequest;
use App\Models\FormaPagamento;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;

class FormaPagamentoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:FORMA_PAGAMENTOS - LISTAR'])->only('index');
        $this->middleware(['permission:FORMA_PAGAMENTOS - INCLUIR'])->only(['create','store']);
        $this->middleware(['permission:FORMA_PAGAMENTOS - EDITAR'])->only(['edit','update']);
        $this->middleware(['permission:FORMA_PAGAMENTOS - VER'])->only(['show']);
        $this->middleware(['permission:FORMA_PAGAMENTOS - EXCLUIR'])->only('destroy');
        $this->middleware(['permission:FORMA_PAGAMENTOS - EXPORTAR'])->only(['export','exportPdf']);
    }

    public function index(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        $query = FormaPagamento::query();
        if ($q !== '') {
            $query->where('nome', 'like', "%{$q}%");
        }
        $model = $query->orderBy('nome')->paginate(20);

        return view('FormaPagamento.index', compact('model','q'));
    }

    public function create()
    {
        return view('FormaPagamento.create');
    }

    public function store(FormaPagamentoRequest $request)
    {
        FormaPagamento::create($request->validated());
        return redirect()->route('FormaPagamento.index')->with('success','Forma de pagamento incluída.');
    }

    public function show(FormaPagamento $forma_pagamento)
    {
        return view('FormaPagamento.show', ['model' => $forma_pagamento]);
    }

    public function edit(FormaPagamento $forma_pagamento)
    {
        return view('FormaPagamento.edit', ['model' => $forma_pagamento]);
    }

    public function update(FormaPagamentoRequest $request, FormaPagamento $forma_pagamento)
    {
        $forma_pagamento->update($request->validated());
        return redirect()->route('FormaPagamento.index')->with('success','Forma de pagamento atualizada.');
    }

    public function destroy(FormaPagamento $forma_pagamento)
    {
        $forma_pagamento->delete();
        return redirect()->route('FormaPagamento.index')->with('success','Forma de pagamento excluída.');
    }

    public function export(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        $query = FormaPagamento::query();
        if ($q !== '') { $query->where('nome', 'like', "%{$q}%"); }
        $data = $query->orderBy('nome')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="forma-pagamentos.csv"',
        ];
        return response()->streamDownload(function() use ($data) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, ['Nome'], ';');
            foreach ($data as $row) { fputcsv($out, [$row->nome], ';'); }
            fclose($out);
        }, 'forma-pagamentos.csv', $headers);
    }

    public function exportPdf(Request $request)
    {
        $q = trim((string)$request->query('q', ''));
        $query = FormaPagamento::query();
        if ($q !== '') { $query->where('nome', 'like', "%{$q}%"); }
        $registros = $query->orderBy('nome')->get();

        $html = view('FormaPagamento.export-pdf', compact('registros'))->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="forma-pagamentos.pdf"',
        ]);
    }
}
