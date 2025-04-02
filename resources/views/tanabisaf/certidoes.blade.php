
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certidões do TANABI SAF</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-900 text-white">

    @include('tanabisaf.header')

    <section class="max-w-4xl mx-auto py-12 px-6">
        <h2 class="text-4xl font-bold text-center mb-6">CERTIDÕES</h2>
        <p class="text-green-300 text-lg leading-relaxed text-center">
            <strong>DO TANABI SAF</strong>
        </p>
        <p class="text-green-300 text-lg leading-relaxed mt-4">
            Apresentamos todas
        </p>
        <p class="text-green-300 text-lg leading-relaxed mt-4">
            as certidões oficiais
        </p>
    </section>




    <section class="bg-green-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500">CERTIDÃO NEGATIVA DE DÉBITOS TRABALHISTAS DO TANABI SAF</h3>
            <p class="text-green-300 text-lg text-center mt-4">
                <a href="{{ route('download', ['id_arquivo' => 40675]) }}">Baixar Arquivo PDF</a>
            </p>
        </div>
    </section>

    <section class="bg-green-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500">Certidão Negativa de Débitos Inscritos da Dívida Ativa do Estado de São Paulo do TANAB SAF</h3>
            <p class="text-green-300 text-lg text-center mt-4">
                <a href="{{ route('download', ['id_arquivo' => 40676]) }}">Baixar Arquivo PDF</a>
            </p>
        </div>
    </section>

    <section class="bg-green-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500">Débitos Tributários Não Inscritos na Dívida Ativa do Estado de São Paulo do TANABI SAF</h3>
            <p class="text-green-300 text-lg text-center mt-4">
                <a href="{{ route('download', ['id_arquivo' => 40677]) }}">Baixar Arquivo PDF</a>
            </p>
        </div>
    </section>


    <section class="bg-green-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500">PODER JUDICIÁRIO JUSTIÇA FEDERAL TRIBUNAL REGIONAL FEDERAL DA 3a REGIÃO CERTIDÃO JUDICIAL CÍVEL DO TANABI SAF</h3>
            <p class="text-green-300 text-lg text-center mt-4">
                <a href="{{ route('download', ['id_arquivo' => 40678]) }}">Baixar Arquivo PDF</a>
            </p>
        </div>
    </section>


    <section class="bg-green-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500">PODER JUDICIÁRIO JUSTIÇA FEDERAL TRIBUNAL REGIONAL FEDERAL DA 3a REGIÃO CERTIDÃO JUDICIAL CÍVEL DO TANABI SAF</h3>
            <p class="text-green-300 text-lg text-center mt-4">
                <a href="{{ route('download', ['id_arquivo' => 40679]) }}">Baixar Arquivo PDF</a>
            </p>
        </div>
    </section>







</body>
</html>
