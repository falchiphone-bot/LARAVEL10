@extends('layouts.bootstrap5')
@section('content')
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h5 mb-0">Importar Tabela/CSV</h1>
    <div class="d-flex gap-2">
      <a href="{{ route('asset-stats.index') }}" class="btn btn-outline-secondary">Voltar</a>
      <button form="asset-import-form" class="btn btn-primary">IMPORTAR</button>
    </div>
  </div>
  <div class="alert alert-info small">
    Cole abaixo a tabela correspondente ao ativo, com as colunas: Data, Média, Mediana, P5, P95. A primeira linha de cabeçalhos é opcional. Datas em dd/mm/yyyy ou yyyy-mm-dd.
  </div>
  <form id="asset-import-form" method="POST" action="{{ route('asset-stats.importStore') }}" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
      <label class="form-label">Símbolo <span class="text-muted small">(opcional se o nome do arquivo contiver: ex: projecoes_NVO...)</span></label>
      <div class="input-group">
  <input type="text" class="form-control" name="symbol" id="importSymbolInput" value="{{ old('symbol') }}" placeholder="Ex: PETR4" maxlength="16" style="max-width:120px;">
        <button class="btn btn-outline-primary" type="submit" title="Importar agora">Importar</button>
      </div>
      <div class="form-text">Se deixar em branco e o arquivo se chamar algo como <code>projecoes_ABCD_2025-10.csv</code>, será detectado <strong>ABCD</strong>. Você também pode usar o botão superior IMPORTAR.</div>
      @error('symbol')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="mb-3">
      <label class="form-label">Arquivo CSV (opcional)</label>
      <input type="file" name="file" class="form-control" accept=".csv,.txt,text/csv">
      <div class="form-text">Se selecionar um arquivo, o conteúdo será usado em vez da área de colagem.</div>
      @error('file')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
    <div class="form-check form-switch mb-3">
      <input class="form-check-input" type="checkbox" role="switch" id="overwriteSwitch" name="overwrite" value="1" {{ old('overwrite') ? 'checked' : '' }}>
      <label class="form-check-label" for="overwriteSwitch" title="Se ligado, sobrescreve registros já existentes para o mesmo símbolo/data">Substituir existentes</label>
    </div>
    <div class="mb-3">
      <label class="form-label">Colar Tabela/CSV (alternativa)</label>
      <textarea id="payload" name="payload" rows="12" class="form-control" placeholder="Data;Média;Mediana;P5;P95\n2025-10-01;10,1;10,0;8,5;12,3">{{ old('payload') }}</textarea>
      @error('payload')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>
  </form>
</div>
@endsection

@push('scripts')
<script>
// Pré-visualiza o arquivo dentro do textarea (opcional) sem substituir se já há texto manual
document.querySelector('input[name="file"]').addEventListener('change', function(ev){
  const file = ev.target.files && ev.target.files[0];
  if(!file) return;
  const reader = new FileReader();
  reader.onload = function(e){
    const ta = document.getElementById('payload');
    if(!ta.value.trim()) { // só preenche se vazio para não sobrescrever edição manual
      ta.value = (e.target.result || '').replace(/\r\n/g,'\n');
    }
  };
  reader.readAsText(file, 'utf-8');
  // Tentativa de detectar símbolo pelo nome do arquivo (client-side) para sugestão
  const symInput = document.querySelector('input[name="symbol"]');
  if(symInput && !symInput.value.trim()){
    const name = file.name.replace(/\.[^.]+$/,'');
    const parts = name.split(/[\-_]/).filter(Boolean);
    // Remove termos comuns
    const ignore = ['projecoes','dados','serie','historica','historico','hist'];
    let candidate = parts.find(p=>!ignore.includes(p.toLowerCase()) && /[a-zA-Z]/.test(p) && p.length>=2 && p.length<=16);
    if(candidate){
      symInput.value = candidate.toUpperCase();
    }
  }
});

// Confirmação antes de enviar se overwrite marcado
document.getElementById('asset-import-form').addEventListener('submit', function(e){
  const ov = document.getElementById('overwriteSwitch');
  if (ov && ov.checked) {
    const ok = confirm('Você habilitou "Substituir existentes". Registros já existentes serão atualizados. Confirmar?');
    if (!ok) { e.preventDefault(); return false; }
  }
});
</script>
@endpush
