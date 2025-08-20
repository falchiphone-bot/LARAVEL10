@extends('layouts.bootstrap5')

@section('content')
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Status da Transcrição</div>
                    <div class="card-body">
                        <p><strong>Job ID:</strong> {{ $jobId }}</p>
                        <div id="status">
                            <em>Carregando...</em>
                        </div>
                        <div class="mt-3">
                            <a href="{{ route('openai.transcribe') }}" class="btn btn-secondary">Voltar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function poll() {
            try {
                const res = await fetch("{{ route('openai.transcribe.status', ['jobId' => $jobId]) }}", { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                const el = document.getElementById('status');
                if (!data || data.status === 'pending') {
                    el.innerHTML = '<span class="badge bg-secondary">Em processamento...</span>';
                } else if (data.status === 'error') {
                    el.innerHTML = '<div class="alert alert-danger">' + (data.error || 'Erro desconhecido') + '</div>';
                } else if (data.status === 'done') {
                    el.innerHTML = `
                        <div class="mb-3">
                            <h5 class="mt-3">Texto Transcrito (Espanhol):</h5>
                            <div class="p-3 bg-light border rounded">${(data.transcribedText || '').replaceAll('\n','<br>')}</div>
                        </div>
                        <div class="mt-4">
                            <h5 class="mt-3">Texto Traduzido (Português):</h5>
                            <div class="p-3 bg-light border rounded">${(data.translatedText || '').replaceAll('\n','<br>')}</div>
                        </div>
                    `;
                    return; // para de pollar quando terminar
                }
            } catch (e) {
                console.error(e);
            }
            setTimeout(poll, 2000);
        }
        poll();
    </script>
@endsection
