@extends('layouts.bootstrap5')

@section('content')
    <div class="py-5 bg-light">
        <div class="container">

            {{-- ALAN --}}

            <div class="card-body">

                {{-- Alertas --}}
                @if (session('success'))
                    <div class="alert alert-success fw-bold text-center">
                        {{ session('success') }}
                    </div>
                    {{ session(['success' => null]) }}
                @elseif (session('error'))
                    <div class="alert alert-danger fw-bold text-center">
                        {{ session('error') }}
                    </div>
                    {{ session(['error' => null]) }}
                @endif

                {{-- Botão Incluir --}}
                @can('IRMAOS_EMAUS_FICHA_CONTROLE - INCLUIR')
                    <a href="{{ route('Irmaos_Emaus_FichaControle.create') }}"
                        class="btn btn-danger btn-lg shadow mb-3">
                        <i class="bi bi-plus-circle"></i> Incluir FICHA DE CONTROLE
                    </a>
                @endcan

                {{-- Total de fichas --}}
                <div class="card-header mb-3">
                    <div class="badge bg-info text-wrap text-white" style="width: 100%; font-size: 24px; padding: 12px 0;">
                        Total de FICHAS DE CONTROLE no sistema de gerenciamento administrativo e contábil:
                        {{ $model->count() ?? 0 }}
                    </div>
                </div>

                {{-- Form de busca + período --}}
                <form method="GET" action="{{ route('Irmaos_Emaus_FichaControle.index') }}" class="mb-3 d-flex align-items-center gap-2 flex-wrap">

    <input type="text" name="search" class="form-control border-primary shadow-sm"
        placeholder="Buscar por nome, serviço, nascimento, cidade, UF, mãe, pai, RG, CPF, NIS..." value="{{ request('search') }}">

    <div class="d-flex align-items-center gap-2">
        <label for="date_start" class="form-label mb-0">De</label>
        <input type="date" id="date_start" name="date_start" class="form-control" value="{{ request('date_start') }}">
    </div>

    <div class="d-flex align-items-center gap-2">
        <label for="date_end" class="form-label mb-0">Até</label>
        <input type="date" id="date_end" name="date_end" class="form-control" value="{{ request('date_end') }}">
    </div>

    <select name="per_page" class="form-select" style="width: auto;">
        @foreach([5,10,15,20,25,30,35,40,45,50,55,60,65,70,75,80,85,90,95,100,500,1000,2000,5000,10000,50000,100000] as $size)
            <option value="{{ $size }}" {{ (int) request('per_page', 5) === $size ? 'selected' : '' }}>
                Mostrar {{ $size }}
            </option>
        @endforeach
    </select>

    <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Buscar</button>
    <a class="btn btn-outline-secondary" href="{{ route('Irmaos_Emaus_FichaControle.index') }}">Limpar</a>
</form>


                {{-- Tabela com cores e ícones --}}
                <table class="table table-striped table-hover" style="background-color: #93f771;">
                    <thead class="bg-primary text-white">
                    <style>
                        .th-ordenada {
                            background: #ffc107 !important;
                            color: #212529 !important;
                        }
                        .icone-ordenado {
                            color: #d35400 !important;
                            font-weight: bold;
                        }
                    </style>
                        <tr>
                            {{-- SERVIÇO --}}
                            <th class="@if(request('sort_by', 'created_at') == 'Irmaos_EmausServicos.nomeServico') th-ordenada @endif">
                                <a href="{{ route('Irmaos_Emaus_FichaControle.index', array_merge(request()->all(), ['sort_by' => 'Irmaos_EmausServicos.nomeServico', 'sort_dir' => (request('sort_by') == 'Irmaos_EmausServicos.nomeServico' && request('sort_dir') == 'asc') ? 'desc' : 'asc'])) }}" class="text-white text-decoration-none">
                                    SERVIÇO
                                    @if(request('sort_by') == 'Irmaos_EmausServicos.nomeServico')
                                        @if(request('sort_dir') == 'asc')
                                            <i class="bi bi-arrow-up icone-ordenado"></i>
                                        @else
                                            <i class="bi bi-arrow-down icone-ordenado"></i>
                                        @endif
                                    @endif
                                </a>
                            </th>

                            {{-- NOME --}}
                            <th class="@if(request('sort_by', 'created_at') == 'Nome') th-ordenada @endif">
                                <a href="{{ route('Irmaos_Emaus_FichaControle.index', array_merge(request()->all(), ['sort_by' => 'Nome', 'sort_dir' => (request('sort_by') == 'Nome' && request('sort_dir') == 'asc') ? 'desc' : 'asc'])) }}" class="text-white text-decoration-none">
                                    NOME
                                    @if(request('sort_by') == 'Nome')
                                        @if(request('sort_dir') == 'asc')
                                            <i class="bi bi-arrow-up icone-ordenado"></i>
                                        @else
                                            <i class="bi bi-arrow-down icone-ordenado"></i>
                                        @endif
                                    @endif
                                </a>
                            </th>

                            {{-- CADASTRADO POR --}}
                            <th class="@if(request('sort_by', 'created_at') == 'user_created') th-ordenada @endif">
                                <a href="{{ route('Irmaos_Emaus_FichaControle.index', array_merge(request()->all(), ['sort_by' => 'user_created', 'sort_dir' => (request('sort_by') == 'user_created' && request('sort_dir') == 'asc') ? 'desc' : 'asc'])) }}" class="text-white text-decoration-none">
                                    CADASTRADO POR
                                    @if(request('sort_by') == 'user_created')
                                        @if(request('sort_dir') == 'asc')
                                            <i class="bi bi-arrow-up icone-ordenado"></i>
                                        @else
                                            <i class="bi bi-arrow-down icone-ordenado"></i>
                                        @endif
                                    @endif
                                </a>
                            </th>

                            {{-- CADASTRADO EM --}}
                            <th class="@if(request('sort_by', 'created_at') == 'created_at') th-ordenada @endif">
                                <a href="{{ route('Irmaos_Emaus_FichaControle.index', array_merge(request()->all(), ['sort_by' => 'created_at', 'sort_dir' => (request('sort_by') == 'created_at' && request('sort_dir') == 'asc') ? 'desc' : 'asc'])) }}" class="text-white text-decoration-none">
                                    CADASTRADO EM
                                    @if(request('sort_by') == 'created_at')
                                        @if(request('sort_dir') == 'asc')
                                            <i class="bi bi-arrow-up icone-ordenado"></i>
                                        @else
                                            <i class="bi bi-arrow-down icone-ordenado"></i>
                                        @endif
                                    @endif
                                </a>
                            </th>

                            {{-- ALTERADO POR --}}
                            <th class="@if(request('sort_by', 'created_at') == 'user_updated') th-ordenada @endif">
                                <a href="{{ route('Irmaos_Emaus_FichaControle.index', array_merge(request()->all(), ['sort_by' => 'user_updated', 'sort_dir' => (request('sort_by') == 'user_updated' && request('sort_dir') == 'asc') ? 'desc' : 'asc'])) }}" class="text-white text-decoration-none">
                                    ALTERADO POR
                                    @if(request('sort_by') == 'user_updated')
                                        @if(request('sort_dir') == 'asc')
                                            <i class="bi bi-arrow-up icone-ordenado"></i>
                                        @else
                                            <i class="bi bi-arrow-down icone-ordenado"></i>
                                        @endif
                                    @endif
                                </a>
                            </th>

                            {{-- ALTERADO EM --}}
                            <th class="@if(request('sort_by', 'created_at') == 'updated_at') th-ordenada @endif">
                                <a href="{{ route('Irmaos_Emaus_FichaControle.index', array_merge(request()->all(), ['sort_by' => 'updated_at', 'sort_dir' => (request('sort_by') == 'updated_at' && request('sort_dir') == 'asc') ? 'desc' : 'asc'])) }}" class="text-white text-decoration-none">
                                    ALTERADO EM
                                    @if(request('sort_by') == 'updated_at')
                                        @if(request('sort_dir') == 'asc')
                                            <i class="bi bi-arrow-up icone-ordenado"></i>
                                        @else
                                            <i class="bi bi-arrow-down icone-ordenado"></i>
                                        @endif
                                    @endif
                                </a>
                            </th>

                            {{-- Ações (sem ordenação) --}}
                            <th colspan="3"></th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($model as $Model)
                            <tr>
                                <td>
                                    {{ $Model->idServicos ? $Model->Irmaos_EmausServicos->nomeServico : 'Sem serviço' }}
                                </td>
                                <td>{{ $Model->Nome }}</td>
                                <td>{{ $Model->user_created }}</td>
                                <td>{{ $Model->created_at ? \Carbon\Carbon::parse($Model->created_at)->format('d/m/Y H:i:s') : 'Sem data' }}</td>
                                <td>{{ $Model->user_updated }}</td>
                                <td>{{ $Model->updated_at ? \Carbon\Carbon::parse($Model->updated_at)->format('d/m/Y H:i:s') : 'Sem data' }}</td>

                                @can('IRMAOS_EMAUS_FICHA_CONTROLE - EDITAR')
                                    <td>
                                        <a href="{{ route('Irmaos_Emaus_FichaControle.edit', $Model->id) }}" class="btn btn-warning" tabindex="-1" role="button" aria-disabled="true">
                                            <i class="bi bi-pencil-square"></i> Editar
                                        </a>
                                    </td>
                                @endcan

                                @can('IRMAOS_EMAUS_FICHA_CONTROLE - VER')
                                    <td>
                                        <a href="{{ route('Irmaos_Emaus_FichaControle.show', $Model->id) }}" class="btn btn-info" tabindex="-1" role="button" aria-disabled="true">
                                            <i class="bi bi-eye"></i> Ver ficha
                                        </a>
                                    </td>
                                @endcan

                                @can('IRMAOS_EMAUS_FICHA_CONTROLE - ENVIAR_ARQUIVOS')
                                    <td>
                                        <a href="{{ route('Irmaos_Emaus_FichaControle.showenviarArquivos', $Model->id) }}" class="btn btn-info"
                                             tabindex="-1" role="button" aria-disabled="true">
                                            <i class="bi bi-eye"></i> Enviar arquivos
                                        </a>
                                    </td>
                                @endcan

                                @can('IRMAOS_EMAUS_FICHA_CONTROLE - EXCLUIR')
                                    <td>
                                        <form method="POST" action="{{ route('Irmaos_Emaus_FichaControle.destroy', $Model->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir?')">
                                                <i class="bi bi-trash"></i> Excluir
                                            </button>
                                        </form>
                                    </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                </table>

<div class="d-flex justify-content-center mt-3">
    {{ $model->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
</div>


                {{-- Espaço para badges ou mensagens adicionais --}}
                <div class="badge bg-primary text-wrap mt-3" style="width: 100%;"></div>

            </div>
        </div>

        <div class="b-example-divider"></div>
    </div>
@endsection

@push('styles')
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
@endpush

@push('scripts')
    {{-- jQuery Confirm --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

    {{-- Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });

        // Confirmação dupla para submits
        $('form').submit(function(e) {
            e.preventDefault();
            $.confirm({
                title: 'Confirmar!',
                content: 'Confirma?',
                buttons: {
                    confirmar: function() {
                        $.confirm({
                            title: 'Confirmar!',
                            content: 'Deseja realmente continuar?',
                            buttons: {
                                confirmar: function() {
                                    e.currentTarget.submit();
                                },
                                cancelar: function() {}
                            }
                        });
                    },
                    cancelar: function() {}
                }
            });
        });
    </script>
@endpush
