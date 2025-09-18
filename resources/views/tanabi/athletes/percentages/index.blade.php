@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h5 mb-0">Percentuais Atletas - TANABI SAF</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('openai.investments.index') }}" class="btn btn-outline-secondary">← Voltar</a>
      @can('TANABI ATLETAS PERCENTUAIS - EXPORTAR')
      <a href="{{ route('tanabi.athletes.percentages.exportCsv') }}" class="btn btn-outline-success">Exportar CSV</a>
      @endcan
    </div>
  </div>
  @if(session('success'))
    <div class="alert alert-success py-2">{{ session('success') }}</div>
  @endif
  @can('TANABI ATLETAS PERCENTUAIS - CRIAR')
  <div class="card mb-4">
    <div class="card-header py-2">Novo Percentual</div>
    <div class="card-body py-3">
  <form method="POST" action="{{ route('tanabi.athletes.percentages.store') }}" class="row g-2 align-items-end" id="createForm">
        @csrf
        <div class="col-md-4">
          <label class="form-label small mb-1 d-flex justify-content-between align-items-center">
            <span>Atleta (selecionar)</span>
            <a href="{{ route('FormandoBase.create') }}" target="_blank" class="btn btn-xs btn-outline-secondary py-0 px-1" style="font-size: .65rem;">+ Novo</a>
          </label>
          <div class="input-group input-group-sm">
            <select name="formando_base_id" class="form-select form-select-sm" id="formandoSelect">
              <option value="">— Selecionar —</option>
              @foreach($athletes as $a)
                <option value="{{ $a->id }}" data-nome="{{ $a->nome }}">{{ $a->nome }}</option>
              @endforeach
            </select>
            <button type="button" class="btn btn-outline-secondary" id="refreshAthletesBtn" title="Recarregar lista" data-reload-atletas>↻</button>
          </div>
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Nome Livre (se não listado)</label>
          <input name="athlete_name" class="form-control form-control-sm" id="athleteNameInput">
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">% TANABI</label>
          <input name="tanabi_percentage" type="number" step="0.0001" min="0" max="100" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-2">
          <label class="form-label small mb-1">% Outro Clube</label>
          <input name="other_club_percentage" type="number" step="0.0001" min="0" max="100" class="form-control form-control-sm" required>
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Outro Clube (cadastro)</label>
          <select name="other_club_id" class="form-select form-select-sm">
            <option value="">— Selecionar —</option>
            @foreach($clubs as $c)
              <option value="{{ $c->id }}">{{ $c->nome }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label small mb-1">Ou Nome Livre</label>
          <input name="other_club_name" class="form-control form-control-sm" placeholder="Se não listado">
        </div>
        <div class="col-md-2 mt-md-4 pt-md-1">
          <button class="btn btn-sm btn-primary w-100">Salvar</button>
        </div>
      </form>
    </div>
  </div>
  @endcan
  <div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th>Atleta</th>
          <th class="text-end">% TANABI</th>
          <th class="text-end">% Outro Clube</th>
          <th class="text-end">% Total</th>
          <th>Outro Clube</th>
          <th>Criado</th>
          <th style="width:90px;">Ações</th>
        </tr>
      </thead>
      <tbody>
        @php($tanabiSum = 0)
        @php($otherSum = 0)
        @forelse($items as $it)
          @php($total = (float)$it->tanabi_percentage + (float)$it->other_club_percentage)
          @php($tanabiSum += (float)$it->tanabi_percentage)
          @php($otherSum += (float)$it->other_club_percentage)
  <tr data-row-id="{{ $it->id }}"
              data-athlete="{{ $it->athlete_name }}"
              data-tanabi="{{ $it->tanabi_percentage }}"
              data-other="{{ $it->other_club_percentage }}"
        data-other-club="{{ $it->other_club_name }}"
        data-other-club-id="{{ $it->other_club_id }}">
            <td>{{ $it->athlete_name }}</td>
            <td class="text-end">{{ number_format($it->tanabi_percentage,4,',','.') }}%</td>
            <td class="text-end">{{ number_format($it->other_club_percentage,4,',','.') }}%</td>
            <td class="text-end fw-semibold">{{ number_format($total,4,',','.') }}%</td>
            <td>{{ $it->otherClub?->nome ?? ($it->other_club_name ?: '—') }}</td>
            <td class="small text-muted">{{ optional($it->created_at)->format('d/m/Y H:i') }}</td>
            <td>
              @can('TANABI ATLETAS PERCENTUAIS - EDITAR')
              <button class="btn btn-sm btn-outline-primary btn-edit w-100" data-id="{{ $it->id }}">Editar</button>
              @endcan
            </td>
          </tr>
          @if($it->otherClubPercentages->count())
          <tr class="table-light">
            <td colspan="7" class="p-0">
              <div class="p-2 small">
                <div class="fw-semibold mb-1">Percentuais em Outros Clubes (detalhamento)</div>
                <div class="table-responsive">
                  <table class="table table-sm table-bordered mb-2">
                    <thead>
                      <tr class="table-secondary">
                        <th style="width:30%">Clube</th>
                        <th style="width:15%" class="text-end">% Clube</th>
                        <th style="width:15%" class="text-end">% Rel. Total Outro</th>
                        <th class="text-end" style="width:15%">% Rel. Geral</th>
                        <th style="width:15%" class="text-center">Ações</th>
                      </tr>
                    </thead>
                    <tbody>
                      @php($sumBreak = $it->otherClubPercentages->sum('percentage'))
                      @foreach($it->otherClubPercentages as $br)
                        @php($relOutro = $it->other_club_percentage > 0 ? ($br->percentage / $it->other_club_percentage * 100) : null)
                        @php($relGeral = ($br->percentage + $it->tanabi_percentage) > 0 ? ($br->percentage + 0) : $br->percentage)
                        <tr>
                          <td>{{ $br->otherClub?->nome ?? ($br->other_club_name ?: '—') }}</td>
                          <td class="text-end">{{ number_format($br->percentage,4,',','.') }}%</td>
                          <td class="text-end">@if($relOutro!==null) {{ number_format($relOutro,2,',','.') }}% @else — @endif</td>
                          <td class="text-end">{{ number_format($br->percentage,4,',','.') }}%</td>
                          <td class="text-center">
                            @can('TANABI ATLETAS PERCENTUAIS - EXCLUIR')
                            <form method="POST" action="{{ route('tanabi.athletes.percentages.other.destroy', $br->id) }}" onsubmit="return confirm('Remover percentual detalhado?');" class="d-inline">
                              @csrf
                              @method('DELETE')
                              <button class="btn btn-sm btn-outline-danger">Excluir</button>
                            </form>
                            @endcan
                          </td>
                        </tr>
                      @endforeach
                      <tr class="table-secondary">
                        <th>Total (breakdown)</th>
                        <th class="text-end">{{ number_format($sumBreak,4,',','.') }}%</th>
                        <th colspan="3"></th>
                      </tr>
                    </tbody>
                  </table>
                </div>
                @can('TANABI ATLETAS PERCENTUAIS - ADICIONAR OUTRO CLUBE')
                <form method="POST" action="{{ route('tanabi.athletes.percentages.other.store', $it) }}" class="row g-2 align-items-end">
                  @csrf
                  <div class="col-md-3">
                    <label class="form-label small mb-1">Clube (cadastro)</label>
                    <select name="other_club_id" class="form-select form-select-sm">
                      <option value="">—</option>
                      @foreach($clubs as $c)
                        <option value="{{ $c->id }}">{{ $c->nome }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small mb-1">Ou Nome Livre</label>
                    <input name="other_club_name" class="form-control form-control-sm" placeholder="Se não listado">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label small mb-1">% Clube</label>
                    <input name="percentage" type="number" step="0.0001" min="0" max="100" class="form-control form-control-sm" required>
                  </div>
                  <div class="col-md-2">
                    <button class="btn btn-sm btn-outline-primary w-100 mt-2">Adicionar</button>
                  </div>
                </form>
                @endcan
              </div>
            </td>
          </tr>
          @else
          <tr class="table-light">
            <td colspan="7" class="p-0">
              <div class="p-2 small">
                <div class="fw-semibold mb-1">Adicionar Percentuais em Outros Clubes</div>
                @can('TANABI ATLETAS PERCENTUAIS - ADICIONAR OUTRO CLUBE')
                <form method="POST" action="{{ route('tanabi.athletes.percentages.other.store', $it) }}" class="row g-2 align-items-end">
                  @csrf
                  <div class="col-md-3">
                    <label class="form-label small mb-1">Clube (cadastro)</label>
                    <select name="other_club_id" class="form-select form-select-sm">
                      <option value="">—</option>
                      @foreach($clubs as $c)
                        <option value="{{ $c->id }}">{{ $c->nome }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small mb-1">Ou Nome Livre</label>
                    <input name="other_club_name" class="form-control form-control-sm" placeholder="Se não listado">
                  </div>
                  <div class="col-md-2">
                    <label class="form-label small mb-1">% Clube</label>
                    <input name="percentage" type="number" step="0.0001" min="0" max="100" class="form-control form-control-sm" required>
                  </div>
                  <div class="col-md-2">
                    <button class="btn btn-sm btn-outline-primary w-100 mt-2">Adicionar</button>
                  </div>
                </form>
                @endcan
              </div>
            </td>
          </tr>
          @endif
        @empty
          <tr><td colspan="6" class="text-center text-muted">Nenhum registro.</td></tr>
        @endforelse
      </tbody>
      @if($items->count())
      <tfoot>
        <tr class="table-secondary">
          <th>Total Geral</th>
          <th class="text-end">{{ number_format($tanabiSum,4,',','.') }}%</th>
          <th class="text-end">{{ number_format($otherSum,4,',','.') }}%</th>
          <th class="text-end">{{ number_format($tanabiSum + $otherSum,4,',','.') }}%</th>
          <th colspan="3"></th>
        </tr>
      </tfoot>
      @endif
    </table>
  </div>
  <!-- Modal Edição -->
  <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header py-2">
          <h5 class="modal-title">Editar Percentual</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" id="editForm">
          @csrf
          @method('PUT')
          <div class="modal-body">
            <div class="mb-2">
              <label class="form-label small mb-1 d-flex justify-content-between align-items-center">
                <span>Atleta (selecionar)</span>
                <a href="{{ route('FormandoBase.create') }}" target="_blank" class="btn btn-xs btn-outline-secondary py-0 px-1" style="font-size: .65rem;">+ Novo</a>
              </label>
              <div class="input-group input-group-sm">
                <select name="formando_base_id" class="form-select form-select-sm" id="editFormandoSelect">
                  <option value="">— Selecionar —</option>
                  @foreach($athletes as $a)
                    <option value="{{ $a->id }}" data-nome="{{ $a->nome }}">{{ $a->nome }}</option>
                  @endforeach
                </select>
                <button type="button" class="btn btn-outline-secondary" id="editRefreshAthletesBtn" title="Recarregar lista" data-reload-atletas>↻</button>
              </div>
            </div>
            <div class="mb-2">
              <label class="form-label small mb-1">Nome Livre (se não listado)</label>
              <input name="athlete_name" class="form-control form-control-sm" id="editAthleteNameInput">
            </div>
            <div class="row g-2">
              <div class="col-6">
                <label class="form-label small mb-1">% TANABI</label>
                <input name="tanabi_percentage" type="number" step="0.0001" min="0" max="100" class="form-control form-control-sm" required>
              </div>
              <div class="col-6">
                <label class="form-label small mb-1">% Outro Clube</label>
                <input name="other_club_percentage" type="number" step="0.0001" min="0" max="100" class="form-control form-control-sm" required>
              </div>
            </div>
            <div class="row g-2 mt-2">
              <div class="col-6">
                <label class="form-label small mb-1">Outro Clube (cadastro)</label>
                <select name="other_club_id" class="form-select form-select-sm">
                  <option value="">—</option>
                  @foreach($clubs as $c)
                    <option value="{{ $c->id }}">{{ $c->nome }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-6">
                <label class="form-label small mb-1">Ou Nome Livre</label>
                <input name="other_club_name" class="form-control form-control-sm" placeholder="Se não listado">
              </div>
            </div>
            <div class="mt-3 small" id="editTotalInfo"></div>
          </div>
          <div class="modal-footer py-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button class="btn btn-sm btn-primary">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  const modalEl = document.getElementById('editModal');
  if(!modalEl) return;
  let modal;
  // Sincronização formulário de criação
  const formandoSelect = document.getElementById('formandoSelect');
  const athleteNameInput = document.getElementById('athleteNameInput');
  if(formandoSelect){
    formandoSelect.addEventListener('change', () => {
      const opt = formandoSelect.options[formandoSelect.selectedIndex];
      if(formandoSelect.value){
        athleteNameInput.value = opt.getAttribute('data-nome');
        athleteNameInput.readOnly = true;
        athleteNameInput.classList.add('bg-light');
      } else {
        athleteNameInput.readOnly = false;
        athleteNameInput.classList.remove('bg-light');
        athleteNameInput.value = '';
      }
    });
  }
  // Sincronização modal edição
  const editFormandoSelect = document.getElementById('editFormandoSelect');
  const editAthleteNameInput = document.getElementById('editAthleteNameInput');
  if(editFormandoSelect){
    editFormandoSelect.addEventListener('change', () => {
      const opt = editFormandoSelect.options[editFormandoSelect.selectedIndex];
      if(editFormandoSelect.value){
        editAthleteNameInput.value = opt.getAttribute('data-nome');
        editAthleteNameInput.readOnly = true;
        editAthleteNameInput.classList.add('bg-light');
      } else {
        editAthleteNameInput.readOnly = false;
        editAthleteNameInput.classList.remove('bg-light');
      }
    });
  }
  document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', () => {
      const tr = btn.closest('tr');
      const id = btn.getAttribute('data-id');
      const athlete = tr.getAttribute('data-athlete');
      const tanabi = tr.getAttribute('data-tanabi');
      const other = tr.getAttribute('data-other');
  const otherClub = tr.getAttribute('data-other-club');
  const otherClubId = tr.getAttribute('data-other-club-id');
      const form = document.getElementById('editForm');
      form.setAttribute('action', `{{ url('/tanabi/athletes/percentages') }}/${id}`);
      // Reset selects para edição
      if(editFormandoSelect){
        editFormandoSelect.value = '';
        editFormandoSelect.dispatchEvent(new Event('change'));
      }
      editAthleteNameInput.value = athlete;
      form.querySelector('[name=tanabi_percentage]').value = tanabi;
      form.querySelector('[name=other_club_percentage]').value = other;
      form.querySelector('[name=other_club_name]').value = otherClub || '';
      const clubSelect = form.querySelector('[name=other_club_id]');
      if (clubSelect) {
        clubSelect.value = otherClubId || '';
      }
      updateTotalInfo();
      modal = new bootstrap.Modal(modalEl);
      modal.show();
    });
  });
  function updateTotalInfo(){
    const a = parseFloat(document.querySelector('#editForm [name=tanabi_percentage]').value)||0;
    const b = parseFloat(document.querySelector('#editForm [name=other_club_percentage]').value)||0;
    const sum = a + b;
    const enforce = {{ config('tanabi.percentages_enforce_100') ? 'true' : 'false' }};
    const info = document.getElementById('editTotalInfo');
    info.textContent = `Total: ${sum.toFixed(4)}%` + (enforce ? ' (deve ser 100%)' : '');
    info.className = 'mt-3 small ' + (enforce ? (Math.abs(sum-100)<0.00011 ? 'text-success' : 'text-danger') : 'text-muted');
  }
  ['tanabi_percentage','other_club_percentage'].forEach(name => {
    const input = document.querySelector('#editForm [name='+name+']');
    if(input){ input.addEventListener('input', updateTotalInfo); }
  });
})();
</script>
@endpush
