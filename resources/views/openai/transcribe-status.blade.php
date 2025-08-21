<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status da Transcrição</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="container py-4">
        <div class="mb-3 d-flex gap-2">
            @canany(['OPENAI - CHAT', 'OPENAI - TRANSCRIBE - ESPANHOL'])
            <a href="{{ route('openai.menu') }}" class="btn btn-outline-secondary">← Voltar ao Menu</a>
            @endcanany
            <a href="{{ route('openai.transcribe') }}" class="btn btn-secondary">Voltar</a>
        </div>

        <h1 class="h4 mb-3">Status da Transcrição</h1>

        <div class="card shadow-sm">
            <div class="card-body">
                <p class="mb-3"><strong>Job ID:</strong> {{ $jobId }}</p>
                <div id="status"><em>Carregando...</em></div>
            </div>
        </div>
    </div>

    @push('scripts')
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
                            <h2 class="h6">Texto Transcrito (Espanhol):</h2>
                            <div class="bg-light border rounded p-2" style="white-space: pre-wrap;">${(data.transcribedText || '')}</div>
                        </div>
                        <div>
                            <h2 class="h6">Texto Traduzido (Português):</h2>
                            <div class="bg-light border rounded p-2" style="white-space: pre-wrap;">${(data.translatedText || '')}</div>
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
    @endpush
</body>
</html>
