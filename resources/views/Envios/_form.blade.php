@php
  $faixasList = $faixasSalariais ?? collect();
  $selectedFaixas = old('saf_faixas_salariais', isset($envio) ? $envio->safFaixasSalariais->pluck('id')->toArray() : []);
@endphp
<div class="mb-3">
  <label class="form-label">Faixas Salariais SAF</label>
  <select name="saf_faixas_salariais[]" class="form-select" multiple>
    @foreach($faixasList as $faixa)
      <option value="{{ $faixa->id }}" @selected(in_array($faixa->id, $selectedFaixas))>{{ $faixa->nome }}</option>
    @endforeach
  </select>
  <div class="form-text">Selecione as faixas salariais relacionadas a este envio.</div>
</div>
@csrf
<div class="mb-3">
  <label class="form-label">Nome</label>
  <input type="text" name="nome" class="form-control" value="{{ old('nome', $envio->nome ?? '') }}" required>
  @error('nome')<div class="text-danger small">{{ $message }}</div>@enderror
  <div class="form-text">Dê um nome para identificar este envio.</div>
  </div>
<div class="mb-3">
  <label class="form-label">Descrição</label>
  <textarea name="descricao" class="form-control" rows="3">{{ old('descricao', $envio->descricao ?? '') }}</textarea>
  @error('descricao')<div class="text-danger small">{{ $message }}</div>@enderror
</div>
@php
  // Lista de representantes pode ser passada tanto em create quanto edit
  $repList = $representantes ?? collect();
  $selectedRep = old('representante_id', $envio->representante_id ?? '');
@endphp
<div class="mb-3">
  <label class="form-label">Representante</label>
  <select name="representante_id" class="form-select">
    <option value="">-- Selecionar --</option>
    @foreach($repList as $rep)
      <option value="{{ $rep->id }}" @selected($selectedRep == $rep->id)>{{ $rep->nome }}</option>
    @endforeach
  </select>
  @error('representante_id')<div class="text-danger small">{{ $message }}</div>@enderror
  <div class="form-text">Opcional: vincule este envio a um representante.</div>
</div>

@push('styles')
  {{-- Select2 CSS (carregado apenas onde o formulário aparece) --}}
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    .select2-container .select2-selection--single { height: 38px; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 36px; }
    .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
  </style>
@endpush

@push('scripts')
  {{-- Select2 JS --}}
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script>
    (function(){
      if (window.__envioRepSelect2Init) return; // evita duplicar se partial incluído 2x
      window.__envioRepSelect2Init = true;
      document.addEventListener('DOMContentLoaded', function(){
        var $sel = window.jQuery && jQuery('select[name="representante_id"]');
        if (!$sel || !$sel.length) return;
        // Reordenar opções alfabeticamente (ignorando a primeira placeholder)
        $sel.each(function(){
          var $s = jQuery(this);
          var currentVal = $s.val(); // preservar seleção atual antes de reordenar
          var $opts = $s.find('option').not(':first').get();
          $opts.sort(function(a,b){
            return a.text.localeCompare(b.text,'pt-BR',{sensitivity:'base'});
          });
            // Reconstrói mantendo placeholder
          var $first = $s.find('option').first();
          $s.empty().append($first).append($opts);
          if (currentVal) { $s.val(currentVal); }
          $s.select2({
            width: '100%',
            placeholder: '-- Selecionar --',
            allowClear: true,
            language: {
              noResults: function(){ return 'Nenhum resultado'; }
            }
          });
          if (currentVal) { $s.val(currentVal).trigger('change'); }
        });
      });
    })();
  </script>
@endpush
<div class="mb-3">
  <label class="form-label">Arquivos</label>
  <input type="file" name="files[]" class="form-control" multiple>
  @error('files')<div class="text-danger small">{{ $message }}</div>@enderror
  @error('files.*')<div class="text-danger small">{{ $message }}</div>@enderror
  <div class="form-text">Você pode selecionar vários arquivos (qualquer tipo) de até 100 MB cada.</div>
</div>
<div>
  <button type="submit" class="btn btn-primary">Salvar</button>
  <a href="{{ route('Envios.index') }}" class="btn btn-secondary">Cancelar</a>
</div>
