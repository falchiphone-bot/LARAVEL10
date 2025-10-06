<?php

namespace App\Http\Controllers;

use App\Models\InvestmentAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\MoedasValores;

class InvestmentAccountController extends Controller
{
    public function index(Request $request): View
    {
        $this->middleware('auth');
        $from = $request->input('from');
        $to = $request->input('to');
        $account = trim((string)$request->input('account'));
        $broker = trim((string)$request->input('broker'));

        $q = InvestmentAccount::where('user_id', Auth::id());
        if (!empty($from)) { $q->whereDate('date', '>=', $from); }
        if (!empty($to))   { $q->whereDate('date', '<=', $to); }
        if ($account !== '') { $q->where('account_name', 'LIKE', '%'.$account.'%'); }
        if ($broker  !== '') { $q->where('broker', 'LIKE', '%'.$broker.'%'); }

        $totalSum = (clone $q)->sum('total_invested');

        // Busca a taxa USD->BRL mais recente (idmoeda=1) para converter o total agregado
        $usdToBrlRate = null;
        try {
            $lastFx = MoedasValores::where('idmoeda', 1)->orderByDesc('data')->orderByDesc('id')->first();
            if ($lastFx && is_numeric($lastFx->valor) && (float)$lastFx->valor > 0) {
                $usdToBrlRate = (float)$lastFx->valor;
            }
        } catch (\Throwable $e) {
            // silencioso: manter nulidade se falhar
        }
        $totalSumBrl = $usdToBrlRate ? $totalSum * $usdToBrlRate : null;

        $q->orderByDesc('date')->orderByDesc('created_at');
        $accounts = $q->paginate(25)->appends(array_filter([
            'from' => $from ?: null,
            'to' => $to ?: null,
            'account' => $account ?: null,
            'broker' => $broker ?: null,
        ]));

    return view('openai.investments.index', compact('accounts','from','to','account','broker','totalSum','usdToBrlRate','totalSumBrl'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->middleware('auth');
        $data = $request->validate([
            'date' => 'required|date',
            'total_invested' => 'required', // normalizado a seguir
            'account_name' => 'required|string|max:100',
            'broker' => 'required|string|max:100',
        ]);
        $val = (string) $data['total_invested'];
        $val = trim($val);
        if ($val !== '') {
            if (str_contains($val, ',')) {
                $val = str_replace('.', '', $val);
                $val = str_replace(',', '.', $val);
            }
        }
        $account = new InvestmentAccount();
        $account->user_id = Auth::id();
        $account->date = $data['date'];
        $account->total_invested = (float)$val;
        $account->account_name = $data['account_name'];
        $account->broker = $data['broker'];
        $account->save();
        return back()->with('success', 'Registro incluído.');
    }

    public function update(InvestmentAccount $account, Request $request): RedirectResponse
    {
        if ((int)$account->user_id !== (int)Auth::id()) { abort(403); }
        $data = $request->validate([
            'date' => 'required|date',
            'total_invested' => 'required', // normalizado a seguir
            'account_name' => 'required|string|max:100',
            'broker' => 'required|string|max:100',
        ]);
        $val = (string) $data['total_invested'];
        $val = trim($val);
        if ($val !== '') {
            if (str_contains($val, ',')) {
                $val = str_replace('.', '', $val);
                $val = str_replace(',', '.', $val);
            }
        }
        $account->date = $data['date'];
        $account->total_invested = (float)$val;
        $account->account_name = $data['account_name'];
        $account->broker = $data['broker'];
        $account->save();
        return back()->with('success', 'Registro atualizado.');
    }

    public function destroy(InvestmentAccount $account): RedirectResponse
    {
        if ((int)$account->user_id !== (int)Auth::id()) { abort(403); }
        $account->delete();
        return back()->with('success', 'Registro excluído.');
    }
}
