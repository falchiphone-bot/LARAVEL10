<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Login com Google</title>
    <script src="https://apis.google.com/js/platform.js" async defer></script>
    <meta name="google-signin-client_id"
        content="618861912106-phecp60h381q8bnblh9nh941batpggkl.apps.googleusercontent.com">
</head>

<body>
    <h1>Autenticar com Google</h1>
    <div class="g-signin2" data-onsuccess="onSignIn"></div>


    {{-- <div id="user-profile">
        <h2>Informações do Perfil</h2>
        <p>Nome: <span id="user-name"></span></p>
        <p>Email: <span id="user-email"></span></p>
        <p>ID do Usuário: <span id="user-id"></span></p>
        <p>ID Token: <span id="id-token"></span></p>
    </div> --}}



    {{-- <script>
        function onSignIn(googleUser) {
            // Obter as informações do usuário
            $profile = googleUser.getBasicProfile();
            $id_token = googleUser.getAuthResponse().id_token;

            // Exibir informações na tela
            document.getElementById('user-name').innerHTML = $profile.getName();
            document.getElementById('user-email').innerHTML = $profile.getEmail();
            document.getElementById('user-id').innerHTML = $profile.getId();
            document.getElementById('id-token').innerHTML = $id_token;

            return redirect(route('gmail.enviaremail'));
        }
    </script> --}}
    <script>
    function onSignIn(googleUser) {
        var profile = googleUser.getBasicProfile();
        console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
        console.log('Name: ' + profile.getName());
        console.log('Image URL: ' + profile.getImageUrl());
        console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
      }
</script>


    <a href="#" onclick="signOut();">Sign out</a>
    <script>
        function signOut() {
            var auth2 = gapi.auth2.getAuthInstance();
            auth2.signOut().then(function() {
                // console.log('User signed out.');
                dd("Desconectado!");
            });
        }
    </script>

</body>

</html>
