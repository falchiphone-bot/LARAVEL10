<<!DOCTYPE html>
<html>
<head>
    <title>Retornar e Atualizar</title>
</head>
<body>
    <script>
        window.onload = function() {
            // Volta duas páginas no histórico
            history.go(-1);

            // Aguardar um pequeno período antes de forçar o refresh
            setTimeout(function() {
                location.reload(true);
            }, 500); // Ajuste o tempo conforme necessário
        };
    </script>
    <p>Redirecionando para as páginas anteriores...</p>
</body>
</html>


