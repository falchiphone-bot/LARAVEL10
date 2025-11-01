<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PlainArrayImport;

class ExcelManagerController extends Controller
{
    public function index(Request $request)
    {
        $data = Session::get('excel_manager.data', []);
        $headers = Session::get('excel_manager.headers', []);
        return view('tools.excel_manager', [
            'rows' => $data,
            'headers' => $headers,
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required','file','mimes:xlsx,csv,xls'],
        ]);
        $file = $request->file('file');

        // Lê como array associativo usando primeira linha como cabeçalho
        $import = new PlainArrayImport();
        $sheets = Excel::toArray($import, $file);
        $rows = $sheets[0] ?? [];

        // Normaliza cabeçalhos mantendo ordem de aparição
        $headers = [];
        if (!empty($rows)) {
            $first = $rows[0];
            foreach (array_keys($first) as $h) {
                $headers[] = (string)$h;
            }
        }

        Session::put('excel_manager.data', $rows);
        Session::put('excel_manager.headers', $headers);

        return redirect()->route('openai.tools.excel.index')->with('success','Arquivo carregado.');
    }
}
