<?php

namespace App\Http\Controllers;

use App\Http\Requests\PixRequest;
use App\Models\Pix;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Dompdf\Dompdf;
use Dompdf\Options;

class PixController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(['permission:PIX - LISTAR'])->only('index');
        $this->middleware(['permission:PIX - INCLUIR'])->only(['create','store']);
        $this->middleware(['permission:PIX - EDITAR'])->only(['edit','update']);
        $this->middleware(['permission:PIX - VER'])->only(['show']);
        $this->middleware(['permission:PIX - EXCLUIR'])->only('destroy');
        $this->middleware(['permission:PIX - EXPORTAR'])->only(['export','exportXlsx','exportPdf']);
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $perPage = (int) $request->query('per_page', 20);
        if ($perPage < 5) { $perPage = 5; }
        if ($perPage > 100) { $perPage = 100; }

        $query = Pix::query();
        if ($q !== '') {
            $query->where('nome', 'like', "%{$q}%");
        }

        $model = $query->orderBy('nome')->paginate($perPage);

        return view('Pix.index', compact('model','q'));
    }

    public function create()
    {
        return view('Pix.create');
    }

    public function store(PixRequest $request)
    {
        Pix::create($request->validated());
        return redirect()->route('Pix.index')->with('success','Registro incluído.');
    }

    public function show(Pix $pix)
    {
        return view('Pix.show', ['model' => $pix]);
    }

    public function edit(Pix $pix)
    {
        return view('Pix.edit', ['model' => $pix]);
    }

    public function update(PixRequest $request, Pix $pix)
    {
        $pix->fill($request->validated());
        $pix->save();
        return redirect()->route('Pix.index')->with('success','Registro atualizado.');
    }

    public function destroy(Pix $pix)
    {
        $nome = $pix->nome;
        $pix->delete();
        return redirect()->route('Pix.index')->with('success', "Registro {$nome} excluído.");
    }

    public function export(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $data = Pix::when($q !== '', fn($qr) => $qr->where('nome','like',"%{$q}%"))
            ->orderBy('nome')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="pix.csv"',
        ];
        $columns = ['Nome'];
        return response()->streamDownload(function () use ($data, $columns) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, $columns, ';');
            foreach ($data as $row) {
                fputcsv($out, [$row->nome], ';');
            }
            fclose($out);
        }, 'pix.csv', $headers);
    }

    public function exportXlsx(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $rows = Pix::when($q !== '', fn($qr) => $qr->where('nome','like',"%{$q}%"))
            ->orderBy('nome')->get(['nome']);

        // Gerar XLSX simples on-the-fly usando PhpSpreadsheet via Excel::raw não é direto.
        // Para manter leve, exportamos CSV; se desejar XLSX formal, posso criar um Export class.
        return $this->export($request);
    }

    public function exportPdf(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $rows = Pix::when($q !== '', fn($qr) => $qr->where('nome','like',"%{$q}%"))
            ->orderBy('nome')->get();

        $html = view('Pix.export-pdf', [
            'rows' => $rows,
            'headerTitle' => $request->query('header_title') ?? 'PIX',
            'headerSubtitle' => $request->query('header_subtitle'),
            'footerLeft' => $request->query('footer_left'),
            'footerRight' => $request->query('footer_right'),
            'logoUrl' => $request->query('logo_url'),
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('a4', 'portrait');
        $dompdf->render();

        $fileName = 'pix-'.date('Ymd-His').'.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
