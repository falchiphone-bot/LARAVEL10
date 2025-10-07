@php
    $empresasDisponiveis = \App\Models\Empresa::join('Contabilidade.EmpresasUsuarios','EmpresasUsuarios.EmpresaID','Empresas.ID')
        ->where('EmpresasUsuarios.UsuarioID',auth()->id())
        ->orderBy('Descricao')
        ->pluck('Descricao','Empresas.ID');
@endphp
<div class="card" id="trocaEmpresaSimplesWrapper">
    <div class="card-header" style="background:#d5ecff;border-bottom:1px solid #8fc2ed;">
        <strong>Trocar Empresa (modo simples)</strong>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('lancamento.troca-empresa.simples', $lancamento->ID) }}">
            @csrf
            <div class="mb-3">
                <label for="novaempresa" class="form-label">Nova Empresa</label>
                <select name="novaempresa" id="novaempresa" class="form-control select2-simples" data-placeholder="Selecione">
                    <option value="">Selecione</option>
                    @foreach($empresasDisponiveis as $empId => $empDesc)
                        <option value="{{ $empId }}" @selected(old('novaempresa')==$empId)>{{ $empDesc }}</option>
                    @endforeach
                </select>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="novacontadebito" class="form-label">Conta Débito</label>
                    <select name="novacontadebito" id="novacontadebito" class="form-control select2-simples" data-placeholder="Selecione">
                        <option value="">Selecione empresa primeiro</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="novacontacredito" class="form-label">Conta Crédito</label>
                    <select name="novacontacredito" id="novacontacredito" class="form-control select2-simples" data-placeholder="Selecione">
                        <option value="">Selecione empresa primeiro</option>
                    </select>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Transferir</button>
                <button type="button" class="btn btn-outline-secondary" onclick="window.__toggleLivewireTrocaEmpresa && window.__toggleLivewireTrocaEmpresa(true)">Voltar ao Livewire</button>
            </div>
        </form>
        @if($errors->any())
            <div class="alert alert-danger mt-3">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if(session('message'))
            <div class="alert alert-success mt-3">{{ session('message') }}</div>
        @endif
    </div>
</div>
@push('scripts')
<script>
(function(){
    function initSelect2Simples(){
        const parent = $('#editarLancamentoModal');
        $('select.select2-simples').each(function(){
            const $el = $(this);
            if($.fn.select2){
                if($el.hasClass('select2-hidden-accessible')){ $el.select2('destroy'); }
                $el.select2({
                    dropdownParent: parent.length ? parent : undefined,
                    theme: 'bootstrap-5',
                    width: '100%',
                    allowClear: true,
                    placeholder: $el.data('placeholder') || 'Selecione'
                });
            }
        });
    }
    document.addEventListener('DOMContentLoaded', initSelect2Simples);
    window.addEventListener('abrir-modal', ()=>setTimeout(initSelect2Simples,250));

    const empresaSelect = document.getElementById('novaempresa');
    empresaSelect?.addEventListener('change', async function(){
        const empId = this.value;
        ['novacontadebito','novacontacredito'].forEach(id=>{
            const sel = document.getElementById(id); if(sel){ sel.innerHTML = '<option value="">Carregando...</option>'; }
        });
        if(!empId){
            ['novacontadebito','novacontacredito'].forEach(id=>{
                const sel = document.getElementById(id); if(sel){ sel.innerHTML = '<option value="">Selecione empresa primeiro</option>'; }
            });
            return;
        }
        try {
            const resp = await fetch('/empresa/' + empId + '/contas-grau5');
            if(!resp.ok){ throw new Error('Falha ao buscar contas'); }
            const json = await resp.json();
            const data = json.data || {};
            const options = ['<option value="">Selecione</option>'];
            Object.keys(data).forEach(k=> options.push('<option value="'+k+'">'+data[k]+'</option>'));
            ['novacontadebito','novacontacredito'].forEach(id=>{
                const sel = document.getElementById(id); if(sel){ sel.innerHTML = options.join(''); }
            });
            initSelect2Simples();
        } catch(e){
            console.error(e);
            ['novacontadebito','novacontacredito'].forEach(id=>{
                const sel = document.getElementById(id); if(sel){ sel.innerHTML = '<option value="">Erro ao carregar</option>'; }
            });
        }
    });
})();
</script>
@endpush
