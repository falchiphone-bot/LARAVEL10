
@{
    Layout = null;
}

<!DOCTYPE html>

<html>
<head>
    <meta name="viewport" content="width=device-width" />
    <title>Rádio online - LIVEPRF</title>

    <script>
        function funcao1() {
            var x;
            var r = confirm("Escolha um valor!");
            if (r == true) {
                x = "você pressionou OK!";
            }
            else {
                x = "Você pressionou Cancelar!";
            }
            document.getElementById("demo").innerHTML = x;
        }
    </script>

    @*<iframe class="rumble" width="640" height="360" src="https://rumble.com/" frameborder="0" allowfullscreen></iframe>*@
    <p>
        <a href="\WebPlayer\dadostrilha"><img src="~/Logo/imagemMusica.jpg" alt="" height="40" width="310"></a>
    </p>


    <script>
        function refreshIFrame() {
            var x = document.getElementById("~\WebPlayer\DadosTrilhaLivePrf");
            x.contentWindow.location.reload();
            var t = setTimeout(refreshIFrame, 3000);
        }
    </script>
</head>
<body>
    @*<input type="button" onclick="funcao1()" value="Exibir Alert" />*@
    <hr />
    <table>
        <tr style="background-color:white; color:white; text-align:left" colspan="1">
            <td>
                <img src="~/Logo/liveprfnsra.png" />
            </td>
        </tr>
    </table>


    <table class="table table-bordered table-hover text-capitalize">
        <tr style="background-color:blue; color:white; text-align:center" colspan="1">
            <td style="background-color:red; color:white; text-align:center" colspan="1">
                <audio controls autoplay="autoplay">:<source src="http://paineldj6.com.br:8071/stream?type=.mp3" type="audio/mp3">Seu navegador não suporta este player</audio>
                @*<script language="javascript" type="text/javascript" src="http://paineldj6.com.br:2199/system/player.js"></script>*@
            </td>
        </tr>
    </table>

    <hr />
    <div id="fb-root"></div>
    <script>
        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/pt_BR/sdk.js#xfbml=1&version=v2.0";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));</script>


    <script language="javascript" type="text/javascript" src="http://paineldj6.com.br:2199/system/streaminfo.js"></script>




    <table class="table table-bordered table-hover text-capitalize">
        <tr style="background-color:yellow; color:white; text-align:center" colspan="1">
            <td style="background-color:forestgreen; color:white; text-align:center" colspan="1">
                <script language="javascript" type="text/javascript" src="http://paineldj6.com.br:2199/system/recenttracks.js"></script>
            </td>
        </tr>
    </table>




    <img src="~/Logo/imagemMusica.jpg" alt="" height="40" width="310">



    <p style="background-color:blue; color:white; text-align:center" colspan="1">
        <h1>Dados da trilha</h1>
    </p>



    <iframe id="iframe" src="..\WebPlayer\DadosTrilhaLivePrf" height="400" width="310" frameborder="0"></iframe>
    <script>
        window.setInterval(function () {
            reloadIFrame()
        }, 5000);

        function reloadIFrame() {
            console.log('atualizando..');
            document.getElementById('iframe').contentWindow.location.reload();
        }
    </script>

</body>

</html>
