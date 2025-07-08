
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certidões do VEC</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-900 text-white">

    @include('vec.header')

    <section class="max-w-4xl mx-auto py-12 px-6">
        <h2 class="text-4xl font-bold text-center mb-6">CERTIDÕES</h2>
        <p class="text-green-300 text-lg leading-relaxed text-center">
            <strong>DO VOTUPORANGA ESPORTE CLUBE</strong>
        </p>
        <p class="text-green-300 text-lg leading-relaxed mt-4">
            Apresentamos todas
        </p>
        <p class="text-green-300 text-lg leading-relaxed mt-4">
            as certidões oficiais
        </p>
    </section>



    <section class="bg-gray-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500">SECRETARIA MUNICIPAL DA FAZENDA Departamento de Receita Tributária DO VOTUPORANGA ESPORTE CLUBE - VALIDADE 07.09.2025</h3>
            <p class="text-green-300 text-lg text-center mt-4">
                <a href="{{ route('download', ['id_arquivo' => 40906]) }}" target="_blank" rel="noopener noreferrer">Baixar Arquivo PDF</a>
            </p>
        </div>
    </section>



    <section class="bg-gray-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500">CERTIDÃO NEGATIVA DE DÉBITOS RELATIVOS AOS TRIBUTOS FEDERAIS E À DÍVIDA ATIVA DA UNIÃO DO VOTUPORANGA ESPORTE CLUBE - VALIDADE 03.01.2026</h3>
            <p class="text-green-300 text-lg text-center mt-4">
                <a href="{{ route('download', ['id_arquivo' => 40904]) }}" target="_blank" rel="noopener noreferrer">Baixar Arquivo PDF</a>
            </p>
        </div>
    </section>

    <section class="bg-gray-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500">CERTIDÃO NEGATIVA DE DÉBITOS - Nº 7894/2025 DO VOTUPORANGA ESPORTE CLUBE - VALIDADE 02.10.2025</h3>
            <p class="text-green-300 text-lg text-center mt-4">
                <a href="{{ route('download', ['id_arquivo' => 40681]) }}" target="_blank" rel="noopener noreferrer">Baixar Arquivo PDF</a>
            </p>
        </div>
    </section>




    <section class="bg-gray-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500">CERTIDÃO NEGATIVA DE DÉBITOS TRABALHISTAS DO VOTUPORANGA ESPORTE CLUBE - VALIDADE 03.01.2026</h3>
            <p class="text-green-300 text-lg text-center mt-4">
                <a href="{{ route('download', ['id_arquivo' => 40906]) }}" target="_blank" rel="noopener noreferrer">Baixar Arquivo PDF</a>
            </p>
        </div>
    </section>

    <section class="bg-gray-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500">Certidão Negativa de Débitos Inscritos da Dívida Ativa do Estado de São Paulo do VOTUPORANGA ESPORTE CLUBE - VALIDADE 07.08.2025</h3>
            <p class="text-green-300 text-lg text-center mt-4">
                <a href="{{ route('download', ['id_arquivo' => 40907]) }}" target="_blank" rel="noopener noreferrer">Baixar Arquivo PDF</a>
            </p>
        </div>
    </section>






    <section class="bg-gray-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500">Certificado de Regularidade do FGTS - CRF DO VOTUPORANGA ESPORTE CLUBE - VALIDADE 18.07.2025</h3>
            <p class="text-green-300 text-lg text-center mt-4">
                <a href="{{ route('download', ['id_arquivo' => 40909]) }}" target="_blank" rel="noopener noreferrer">Baixar Arquivo PDF</a>
            </p>
        </div>
    </section>




    <section class="bg-gray-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500">PODER JUDICIÁRIO JUSTIÇA FEDERAL TRIBUNAL REGIONAL FEDERAL DA 3a REGIÃO CERTIDÃO JUDICIAL CÍVEL DO VOTUPORANGA ESPORTE CLUBE - VALIDADE 02.10.2025</h3>
            <p class="text-green-300 text-lg text-center mt-4">
                <a href="{{ route('download', ['id_arquivo' => 40910]) }}" target="_blank" rel="noopener noreferrer">Baixar Arquivo PDF</a>
            </p>
        </div>
    </section>






    <section class="bg-gray-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500"> Débitos Tributários Não Inscritos na Dívida Ativa do Estado de São Paulo - VALIDADE 02.10.2025</h3>
            <p class="text-green-300 text-lg text-center mt-4">
                <a href="{{ route('download', ['id_arquivo' => 40686]) }}" target="_blank" rel="noopener noreferrer">Baixar Arquivo PDF</a>
            </p>
        </div>
    </section>


    <section class="bg-gray-800 py-12 px-6">
        <div class="max-w-4xl mx-auto">
            <h3 class="text-3xl font-bold text-center text-red-500">PODER JUDICIÁRIO JUSTIÇA FEDERAL TRIBUNAL REGIONAL FEDERAL DA 3a REGIÃO CERTIDÃO JUDICIAL CRIMINAL NEGATIVA DO VOTUPORANGA ESPORTE CLUBE - VALIDADE 02.10.2025</h3>
            <p class="text-green-300 text-lg text-center mt-4">
                <a href="{{ route('download', ['id_arquivo' => 40911]) }}" target="_blank" rel="noopener noreferrer">Baixar Arquivo PDF</a>
            </p>
        </div>
    </section>
</body>
</html>
