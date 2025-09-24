@extends('layouts.bootstrap5')

@section('content')
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body text-center">
          <h5 class="mb-3">Você saiu da sua conta</h5>
          <p class="text-muted">Por segurança, esta guia será fechada automaticamente.</p>
          <div class="small text-muted mb-3">Se o navegador não permitir fechar automaticamente, clique no botão abaixo ou feche manualmente.</div>
          <div class="d-flex gap-2 justify-content-center">
            <a href="{{ url('/login') }}" class="btn btn-primary">Ir para a tela de login</a>
            <button id="btn-close" class="btn btn-outline-secondary" type="button">Fechar guia</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  function tryClose(){
    // Tenta fechar via window.close(). Alguns navegadores exigem que a aba tenha sido aberta por script
    window.open('', '_self');
    window.close();
  }
  // Tenta imediatamente
  try{ tryClose(); }catch(_e){}
  // Adiciona ao botão manual
  document.addEventListener('DOMContentLoaded', function(){
    const btn = document.getElementById('btn-close');
    if (btn) btn.addEventListener('click', tryClose);
  });
  // Se não fechar, permanece na tela com opções
})();
</script>
@endpush
