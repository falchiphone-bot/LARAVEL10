<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Login com Google</title>
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <meta name="google-signin-client_id" content="618861912106-phecp60h381q8bnblh9nh941batpggkl.apps.googleusercontent.com">
</head>
<body>
    <h1>Login com Google</h1>
    <div class="g-signin2" data-onsuccess="onSignIn"></div>
    <script>
        function onSignIn(googleUser) {
            // Obter as informações do usuário
            var profile = googleUser.getBasicProfile();
            var id_token = googleUser.getAuthResponse().id_token;

            // Enviar informações para o servidor para fazer login
            // Exemplo de AJAX:
            // $.post('/login', {id_token: id_token}, function(data) {
            //     console.log('Usuário logado com sucesso');
            // });
        }
    </script>

<a href="#" onclick="signOut();">Sign out</a>
<script>
  function signOut() {
    var auth2 = gapi.auth2.getAuthInstance();
    auth2.signOut().then(function () {
      console.log('User signed out.');
    });
  }
</script>

</body>
</html>
