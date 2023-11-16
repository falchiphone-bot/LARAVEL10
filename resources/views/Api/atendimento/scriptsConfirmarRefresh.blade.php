
@push('scripts')
                <link rel="stylesheet"
                    href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
                <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

                <script>
                    $('form').submit(function(e) {
                        e.preventDefault();
                        $.confirm({
                            title: 'Confirmar!',
                            content: 'Confirma o envio?',
                            buttons: {
                                confirmar: function() {
                                    $.confirm({
                                        title: 'Confirmar!',
                                        content: 'Deseja realmente continuar com o envio?',
                                        buttons: {
                                            confirmar: function() {
                                                e.currentTarget.submit();
                                            },
                                            cancelar: function() {
                                                // Você pode adicionar ações aqui, se necessário.
                                            },
                                        }
                                    });
                                },
                                cancelar: function() {
                                    // Você pode adicionar ações aqui, se necessário.
                                },
                            }
                        });
                    });

                    var pageRefreshAllowed = true;

                    function stopPageRefresh() {
                        pageRefreshAllowed = false;
                    }

                    function allowPageRefresh() {
                        pageRefreshAllowed = true;
                    }

                    window.onbeforeunload = function() {
                        if (!pageRefreshAllowed) {
                            return "Você tem campos não salvos no formulário. Tem certeza de que deseja sair da página?";
                        }
                    };
                </script>
            @endpush
