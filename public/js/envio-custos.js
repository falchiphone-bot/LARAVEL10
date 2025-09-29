document.addEventListener('DOMContentLoaded', function(){
  const table = document.getElementById('custos-table');
  if(!table){
    console.warn('[Custos] Tabela de custos não encontrada!');
    return;
  }
  const envioId = table.getAttribute('data-envio-id') || window.ENVIO_ID;
  function toReal(v){
    return Number(v).toLocaleString('pt-BR',{minimumFractionDigits:2, maximumFractionDigits:2});
  }
  // Editar
  table.addEventListener('click', function(ev){
    const btn = ev.target.closest('.js-edit-custo');
    if(!btn) return;
    const tr = btn.closest('tr');
    if(tr.classList.contains('editing')) return;
    tr.classList.add('editing');
    const tdData = tr.querySelector('.c-data');
    const tdNome = tr.querySelector('.c-nome');
    const tdValor = tr.querySelector('.c-valor');
    const tdAcoes = tr.querySelector('.c-acoes');
    const orig = {
      data: tdData.getAttribute('data-value'),
      nome: tdNome.textContent.trim(),
      valor: tdValor.getAttribute('data-raw')
    };
    tdAcoes.dataset.originalActions = tdAcoes.innerHTML;
    tdData.innerHTML = `<input type="date" class="form-control form-control-sm" value="${orig.data||''}">`;
    tdNome.innerHTML = `<input type="text" class="form-control form-control-sm" maxlength="150" value="${orig.nome.replace(/&/g,'&amp;').replace(/</g,'&lt;')}">`;
    tdValor.innerHTML = `<input type="number" step="0.01" min="0" class="form-control form-control-sm" value="${orig.valor}">`;
    tdAcoes.innerHTML = `
      <div class="btn-group btn-group-sm">
        <button type="button" class="btn btn-primary js-save-custo">Salvar</button>
        <button type="button" class="btn btn-outline-secondary js-cancel-custo">Cancelar</button>
      </div>`;
    tr.dataset.original = JSON.stringify(orig);
  });
  // Cancelar
  table.addEventListener('click', function(ev){
    const cancel = ev.target.closest('.js-cancel-custo');
    if(!cancel) return;
    const tr = cancel.closest('tr');
    const orig = JSON.parse(tr.dataset.original||'{}');
    const tdData = tr.querySelector('.c-data');
    const tdNome = tr.querySelector('.c-nome');
    const tdValor = tr.querySelector('.c-valor');
    const tdAcoes = tr.querySelector('.c-acoes');
    tdData.innerHTML = (orig.data? new Date(orig.data).toLocaleDateString('pt-BR') : '');
    tdData.setAttribute('data-value', orig.data || '');
    tdNome.textContent = orig.nome || '';
    tdValor.textContent = toReal(orig.valor||0);
    tdValor.setAttribute('data-raw', orig.valor||0);
    tdAcoes.innerHTML = tdAcoes.dataset.originalActions || '';
    tr.classList.remove('editing');
  });
  // Salvar
  table.addEventListener('click', async function(ev){
    const save = ev.target.closest('.js-save-custo');
    if(!save) return;
    const tr = save.closest('tr');
    const id = tr.getAttribute('data-custo-id');
    const tdData = tr.querySelector('.c-data input');
    const tdNome = tr.querySelector('.c-nome input');
    const tdValor = tr.querySelector('.c-valor input');
    save.disabled = true;
    try{
      const url = `/Envios/${envioId}/custos/${id}`;
      const resp = await fetch(url, {
        method: 'POST',
        headers: {
          'X-Requested-With':'XMLHttpRequest',
          'Accept':'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
        },
        body: new URLSearchParams({
          '_method':'PUT',
          'data': tdData.value,
          'nome': tdNome.value,
          'valor': tdValor.value
        })
      });
      const data = await resp.json().catch(()=>null);
      if(resp.ok && data?.ok){
        tr.classList.remove('editing');
        tr.querySelector('.c-data').innerHTML = data.row.data_formatada;
        tr.querySelector('.c-data').setAttribute('data-value', tdData.value);
        tr.querySelector('.c-nome').textContent = data.row.nome;
        tr.querySelector('.c-valor').textContent = data.row.valor_formatado;
        tr.querySelector('.c-valor').setAttribute('data-raw', tdValor.value);
        const tdAcoes = tr.querySelector('.c-acoes');
        tdAcoes.innerHTML = `
          <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary js-edit-custo">Editar</button>
            <form method="POST" action="/Envios/${envioId}/custos/${id}" onsubmit="return confirm('Remover este custo?')" class="d-inline">
              <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]')?.content || ''}">
              <input type="hidden" name="_method" value="DELETE">
              <button class="btn btn-outline-danger">Excluir</button>
            </form>
          </div>`;
        const totalBox = document.querySelector('.alert.alert-info');
        if(totalBox && data.total_formatted){
          totalBox.innerHTML = '<strong>Total:</strong> R$ '+data.total_formatted;
        }
      } else {
        let msg = 'Falha ao salvar custo.';
        if(data && data.errors){
          msg += '\n';
          for(const campo in data.errors){
            msg += `- ${campo}: ${data.errors[campo].join(', ')}\n`;
          }
        } else if(data && data.message){
          msg += '\n' + data.message;
        }
        alert(msg);
      }
    }catch(e){
      alert('Erro de rede ao salvar.');
    }finally{
      save.disabled = false;
    }
  });
  // Excluir
  table.addEventListener('submit', async function(ev){
    const form = ev.target;
    if(!form.matches('form[action*="/custos/"]')) return;
    ev.preventDefault();
    const row = form.closest('tr');
    const id = row?.getAttribute('data-custo-id');
    try{
      const resp = await fetch(form.action, {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
        },
        body: new URLSearchParams([['_method','DELETE']])
      });
      const data = await resp.json().catch(()=>null);
      if(resp.ok && data?.ok){
        if(row) row.remove();
        const totalBox = document.querySelector('.alert.alert-info');
        if(totalBox && data.total_formatted){
          totalBox.innerHTML = '<strong>Total:</strong> R$ ' + data.total_formatted;
        }
      } else {
        alert((data && data.message) ? data.message : 'Falha ao remover custo.');
      }
    }catch(e){
      alert('Erro de rede ao remover custo.');
    }
  });
});
