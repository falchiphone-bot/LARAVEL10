<!DOCTYPE html>
<html>
<head>
    <title>Retornar e Atualizar</title>
</head>
<body>
    <script>
        window.onload = function() {
            // Volta duas páginas no histórico
            history.go(-2);
            // Aguarda 1 segundo e depois dá refresh na página atual
            window.addEventListener('popstate', function() {
                location.reload(true);
            });
        };
    </script>
    <p>Redirecionando para as páginas anteriores...</p>
</body>
</html>

