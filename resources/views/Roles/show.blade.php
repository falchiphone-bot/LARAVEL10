@extends('layouts.bootstrap5')
@section('content')
    <div class="py-5 bg-light">
        <div class="container">
            {{-- <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#">Permissions</a></li>
              <li class="breadcrumb-item active" aria-current="page">create</li>
            </ol>
          </nav> --}}

            <div class="card">
                <h1 class="text-center">Inclusão de permissões na função</h1>
                <hr>
                <form method="post" action="/Funcoes/salvarpermissao/{{ $cadastro->id }}" class="mt-6 space-y-6">
                    @csrf
                    <div class="card">
                        <div class="card-header">
                            Selecione as permissões da função: <strong>{{ $cadastro->name }}</strong>
                            <h6>
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <select multiple id="permissao" name="permissao[]" autocomplete="permissao-name"
                                        class="select2 form-control">
                                        @foreach ($permissoes as $id => $name)
                                            <option @if ($cadastro->hasPermissionTo($name)) selected @endif
                                                value={{ $id }}>{{ $name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="row mt-2">
                                <div class="col-6">
                                    <button type="submit" class="btn btn-primary">Salvar</button>
                                    <a href="{{ route('Funcoes.index') }}" class="btn btn-warning">Retornar para lista de
                                        funções</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            </section>
        </div>
    </div>
    </div>
    </div>
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
        $('form').submit(function(e) {
            e.preventDefault();
            $.confirm({
                title: 'Confirmar!',
                content: 'Confirma?',
                buttons: {
                    confirmar: function() {
                        // $.alert('Confirmar!');
                        $.confirm({
                            title: 'Confirmar!',
                            content: 'Deseja realmente continuar?',
                            buttons: {
                                confirmar: function() {
                                    // $.alert('Confirmar!');
                                    e.currentTarget.submit()
                                },
                                cancelar: function() {
                                    // $.alert('Cancelar!');
                                },

                            }
                        });

                    },
                    cancelar: function() {
                        // $.alert('Cancelar!');
                    },

                }
            });
        });
    </script>
@endpush
