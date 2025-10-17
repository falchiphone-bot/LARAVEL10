@extends('layouts.bootstrap5')
@section('content')
<div class="py-4 bg-light">
  <div class="container">
    <div class="card mb-3">
      <div class="card-header">
        <strong>Balancete por período</strong>
      </div>
      <div class="card-body">
        <form method="get" action="{{ route('lancamentos.balancete') }}" class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Empresa</label>
            <select name="empresa_id" class="form-select">
              @foreach($empresas as $e)
                <option value="{{ $e->ID }}" {{ (string)$empresaId === (string)$e->ID ? 'selected' : '' }}>{{ $e->Descricao }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">De</label>
            <input type="date" name="de" class="form-control" value="{{ $de }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">Até</label>
            <input type="date" name="ate" class="form-control" value="{{ $ate }}">
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-primary">Gerar</button>
            <a href="/PlanoContas/dashboard" class="btn btn-outline-secondary ms-2">Voltar</a>
          </div>
        </form>
      </div>
    </div>

    @if(!empty($linhas))
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <div><strong>Resultados</strong></div>
        <div class="d-flex gap-2">
          <a class="btn btn-sm btn-outline-primary" href="{{ route('lancamentos.balancete.exportXlsx', request()->all()) }}">Exportar XLSX</a>
          <a class="btn btn-sm btn-outline-primary" href="{{ route('lancamentos.balancete.exportCsv', request()->all()) }}">Exportar CSV</a>
          <a class="btn btn-sm btn-outline-primary" href="{{ route('lancamentos.balancete.exportPdf', request()->all()) }}">Exportar PDF</a>
        </div>
      </div>
      <div class="card-body table-responsive">
        @php $temGrupos = !empty($grupos ?? []); @endphp
        @if($temGrupos)
          @foreach($grupos as $g)
            <h6 class="mt-3 mb-2"><strong>{{ $g['label'] }}</strong></h6>
            <table class="table table-sm table-striped align-middle">
              <thead>
                <tr>
                  <th>Conta</th>
                  <th>Classificação</th>
                  <th class="text-end">Débito</th>
                  <th class="text-end">Crédito</th>
                  <th class="text-end">Saldo</th>
                </tr>
              </thead>
              <tbody>
                @foreach($g['linhas'] as $l)
                <tr>
                  <td>
                    <a href="/Contas/Extrato/{{ $l['conta_id'] }}?de={{ $de }}&ate={{ $ate }}" target="_blank" rel="noopener noreferrer" title="Abrir extrato da conta">{{ $l['conta'] }}</a>
                  </td>
                  <td>{{ $l['codigo'] ?? '' }}</td>
                  <td class="text-end">{{ number_format($l['debito'], 2, ',', '.') }}</td>
                  <td class="text-end">{{ number_format($l['credito'], 2, ',', '.') }}</td>
                  <td class="text-end {{ $l['saldo'] < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($l['saldo'], 2, ',', '.') }}</td>
                </tr>
                @endforeach
              </tbody>
              <tfoot>
                <tr>
                  <th>Subtotal {{ $g['label'] }}</th>
                  <th></th>
                  <th class="text-end">{{ number_format($g['totDeb'] ?? 0, 2, ',', '.') }}</th>
                  <th class="text-end">{{ number_format($g['totCred'] ?? 0, 2, ',', '.') }}</th>
                  <th class="text-end">{{ number_format($g['totSaldo'] ?? 0, 2, ',', '.') }}</th>
                </tr>
              </tfoot>
            </table>
          @endforeach
          <div class="mt-3">
            <strong>Totais gerais</strong>
            <table class="table table-sm align-middle">
              <tfoot>
                <tr>
                  <th>Total</th>
                  <th></th>
                  <th class="text-end">{{ number_format($totDeb, 2, ',', '.') }}</th>
                  <th class="text-end">{{ number_format($totCred, 2, ',', '.') }}</th>
                  <th class="text-end">{{ number_format($totSaldo, 2, ',', '.') }}</th>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="mt-4">
            <strong>Demonstrativo de Resultado</strong>
            <table class="table table-sm align-middle">
              <tbody>
                <tr>
                  <th>Receitas</th>
                  <td class="text-end">{{ number_format($dreReceitas ?? 0, 2, ',', '.') }}</td>
                </tr>
                <tr>
                  <th>Despesas</th>
                  <td class="text-end">{{ number_format($dreDespesas ?? 0, 2, ',', '.') }}</td>
                </tr>
                <tr>
                  <th>Resultado</th>
                  <td class="text-end {{ ($dreResultado ?? 0) < 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($dreResultado ?? 0, 2, ',', '.') }}
                    <small class="ms-2">{{ ($dreResultado ?? 0) < 0 ? 'PREJUÍZO' : 'LUCRO' }}</small>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        @else
          <table class="table table-sm table-striped align-middle">
            <thead>
              <tr>
                <th>Conta</th>
                <th>Classificação</th>
                <th class="text-end">Débito</th>
                <th class="text-end">Crédito</th>
                <th class="text-end">Saldo</th>
              </tr>
            </thead>
            <tbody>
              @foreach($linhas as $l)
              <tr>
                <td>
                  <a href="/Contas/Extrato/{{ $l['conta_id'] }}?de={{ $de }}&ate={{ $ate }}" target="_blank" rel="noopener noreferrer" title="Abrir extrato da conta">{{ $l['conta'] }}</a>
                </td>
                <td>{{ $l['codigo'] ?? '' }}</td>
                <td class="text-end">{{ number_format($l['debito'], 2, ',', '.') }}</td>
                <td class="text-end">{{ number_format($l['credito'], 2, ',', '.') }}</td>
                <td class="text-end {{ $l['saldo'] < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($l['saldo'], 2, ',', '.') }}</td>
              </tr>
              @endforeach
            </tbody>
            <tfoot>
              <tr>
                <th>Total</th>
                <th></th>
                <th class="text-end">{{ number_format($totDeb, 2, ',', '.') }}</th>
                <th class="text-end">{{ number_format($totCred, 2, ',', '.') }}</th>
                <th class="text-end">{{ number_format($totSaldo, 2, ',', '.') }}</th>
              </tr>
            </tfoot>
          </table>

          <div class="mt-4">
            <strong>Demonstrativo de Resultado</strong>
            <table class="table table-sm align-middle">
              <tbody>
                <tr>
                  <th>Receitas</th>
                  <td class="text-end">{{ number_format($dreReceitas ?? 0, 2, ',', '.') }}</td>
                </tr>
                <tr>
                  <th>Despesas</th>
                  <td class="text-end">{{ number_format($dreDespesas ?? 0, 2, ',', '.') }}</td>
                </tr>
                <tr>
                  <th>Resultado</th>
                  <td class="text-end {{ ($dreResultado ?? 0) < 0 ? 'text-danger' : 'text-success' }}">
                    {{ number_format($dreResultado ?? 0, 2, ',', '.') }}
                    <small class="ms-2">{{ ($dreResultado ?? 0) < 0 ? 'PREJUÍZO' : 'LUCRO' }}</small>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
    @else
      <div class="alert alert-info">Selecione empresa e período e clique em Gerar.</div>
    @endif
  </div>
 </div>
@endsection

