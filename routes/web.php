<?php

use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\OAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::resource('teste', App\Http\Controllers\TesteController::class);

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

//Para autenticar no sistema sem usuario ou com usuário do google
Route::get('auth/google/', [GoogleController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

Route::get('auth/register', [App\Http\Controllers\RegisteredUserController::class, 'create'])->name('auth.register');
Route::middleware('auth')->group(function () {


# PDF
Route::get('pdf/GerarPDF', [App\Http\Controllers\ExtratoConectCarController::class, 'GerarPDF'])->name('pdf.gerarpdf');


    #GoogleDrive
    Route::get('drive/showGoogleClientInfo', [App\Http\Controllers\GoogleDriveController::class, 'showGoogleClientInfo'])->name('google.showGoogleClientInfo');
    Route::get('drive/google/login', [App\Http\Controllers\GoogleDriveController::class, 'googleLogin'])->name('google.login');
    Route::post('drive/google-drive/file-upload', [App\Http\Controllers\GoogleDriveController::class, 'googleDriveFileUpload'])->name('google.drive.file.upload');
    Route::post('drive/google-drive/file-delete', [App\Http\Controllers\GoogleDriveController::class, 'googleDriveFileDelete'])->name('google.drive.file.delete');
    Route::post('drive/google-drive/file-deletedefinitivo', [App\Http\Controllers\GoogleDriveController::class, 'googleDriveFileDeleteDefinitivo'])->name('google.drive.file.deletedefinitivo');
    Route::post('drive/google-drive/file-consultar', [App\Http\Controllers\GoogleDriveController::class, 'googleDriveFileConsultar'])->name('google.drive.file.consultar');
    Route::get('drive/google-drive/file-consultardocumento/{id}', [App\Http\Controllers\GoogleDriveController::class, 'googleDriveFileConsultarDocumento'])->name('google.drive.file.consultardocumento');


    Route::post('drive/google-drive/file-comentario', [App\Http\Controllers\GoogleDriveController::class, 'googleDriveFileComentario'])->name('google.drive.file.comentario');
    Route::post('drive/google-drive/file-mover', [App\Http\Controllers\GoogleDriveController::class, 'googleDriveFileMover'])->name('google.drive.file.mover');
    Route::post('drive/google-drive/file-alterarnome', [App\Http\Controllers\GoogleDriveController::class, 'googleDriveFileAlterarNome'])->name('google.drive.file.alterarnome');



    Route::get('drive/dashboard', [App\Http\Controllers\GoogleDriveController::class, 'dashboard'])->name('googledrive.dashboard');
    Route::get('drive/DadosClienteGoogle', function () { return view('GoogleDrive.DadosClienteGoogle');})->name('Dados.clienteGoogle');
    // Route::get('drive/UploadArquivo', [App\Http\Controllers\GoogleDriveController::class, ' '])->name('google.uploadarquivo');
    Route::get('drive/UploadArquivo', function () { return view('GoogleDrive.UploadArquivoGoogleDrive');})->name('upload.arquivos');
    Route::get('drive/DeleteArquivo', function () { return view('GoogleDrive.DeleteArquivoGoogleDrive');})->name('delete.arquivos');
    Route::get('drive/DeleteArquivoDefinitivo', function () { return view('GoogleDrive.DeleteDefinitivoArquivoGoogleDrive');})->name('deletedefinitivo.arquivos');
    Route::get('drive/ConsultarArquivo', function () { return view('GoogleDrive.ConsultarArquivoGoogleDrive');})->name('consultar.arquivos');

    Route::get('drive/ComentarioArquivo', function () { return view('GoogleDrive.ComentarioArquivoGoogleDrive');})->name('comentario.arquivos');
    Route::get('drive/MoverArquivo', function () { return view('GoogleDrive.MoverArquivoGoogleDrive');})->name('mover.arquivos');
    Route::get('drive/AlterarNomeArquivo', function () { return view('GoogleDrive.AlterarNomeArquivoGoogleDrive');})->name('alterarnome.arquivos');
    Route::get('drive/InformacaoArquivo', function () { return view('GoogleDrive.InformacaoGoogleDrive');})->name('informacao.arquivos');

    #Rotas criadas automaticamente laravel
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    # ENVIA EMAIL GMAIL
    // Route::get('/enviar-email', function () {
    //     $sender = new App\Services\GmailSender();
    //     $sender->send('usuario@example.com', 'Assunto do e-mail', 'Corpo da mensagem do e-mail');
    //     return "E-mail enviado com sucesso!";
    // });

    //autenticação google paraenvio de email
    Route::prefix('/mail')->group(function () {
        Route::view('home', 'mail.home')->name('mail.home');

        Route::post('/get-token', [OAuthController::class, 'doGenerateToken'])->name('generate.token');
        Route::get('/get-token', [OAuthController::class, 'doSuccessToken'])->name('token.success');
        Route::post('/send', [MailController::class, 'send'])->name('send.email');
    });

    // Route::get('EnviarEmail', [App\Http\Controllers\EnviaEmailController::class, 'enviaremail'])->name('gmail.enviaremail');
    // Route::get('GoogleLogin', [App\Http\Controllers\EnviaEmailController::class, 'googlelogin'])->name('google.login');

    # Feriados
    Route::resource('Feriados', App\Http\Controllers\FeriadoController::class);

    # Centro de custos
    Route::post('ContasCentroCustos/gerarCalculoPDFPeriodo', [App\Http\Controllers\ContasCentroCustosController::class, 'gerarCalculoPDFPeriodo'])->name('ContasCentroCustos.gerarCalculoPDFPeriodo');
    Route::get('ContasCentroCustos/gerarCalculoPDF/{id?}', [App\Http\Controllers\ContasCentroCustosController::class, 'gerarCalculoPDF'])->name('ContasCentroCustos.gerarCalculoPDF');
    Route::get('CentroCustos/dashboard', [App\Http\Controllers\CentroCustosController::class, 'dashboard'])->name('CentroCustos.dashboard');
    Route::get('ContasCentroCustos/calculocontascentrocustos/{id?}', [App\Http\Controllers\ContasCentroCustosController::class, 'CalculoContasCentroCustos'])->name('ContasCentroCustos.calculocontascentrocustos');
    Route::resource('CentroCustos', App\Http\Controllers\CentroCustosController::class);
    Route::resource('ContasCentroCustos', App\Http\Controllers\ContasCentroCustosController::class);

 # Formandos base
 Route::post('FormandoBase/CreateArquivoFormandoBase', [App\Http\Controllers\FormandoBaseController::class, 'CreateArquivoFormandoBase'])->name('FormandoBase.ArquivoFormandoBase');
 Route::post('FormandoBase/CreateRecebimentoFormandoBase', [App\Http\Controllers\FormandoBaseController::class, 'CreateRecebimentoFormandoBase'])->name('FormandoBase.RecebimentoFormandoBase');
 Route::post('FormandoBase/CreateRedeSocialFormandoBase', [App\Http\Controllers\FormandoBaseController::class, 'CreateRedeSocialFormandoBase'])->name('FormandoBase.RedeSocialFormandoBase');
 Route::post('FormandoBase/CreatePosicaoFormandoBase', [App\Http\Controllers\FormandoBaseController::class, 'CreatePosicaoFormandoBase'])->name('FormandoBase.PosicaoFormandoBase');
 Route::post('FormandoBase/CreateAvaliacaoFormandoBase', [App\Http\Controllers\FormandoBaseController::class, 'CreateAvaliacaoFormandoBase'])->name('FormandoBase.AvaliacaoFormandoBase');
 Route::get('FormandoBase/Excluidos', [App\Http\Controllers\FormandoBaseController::class, 'Excluidos'])->name('formandobase.excluidos');
Route::get('FormandoBase/ConsultaEmpresa', [App\Http\Controllers\FormandoBaseController::class, 'ConsultaEmpresa'])->name('formandobase.consultaempresa');
Route::resource('FormandoBase', App\Http\Controllers\FormandoBaseController::class);
Route::resource('FormandoBasePosicoes', App\Http\Controllers\FormandoBasePosicoesController::class);
Route::resource('FormandoBaseArquivos', App\Http\Controllers\FormandoBaseArquivosController::class);
Route::resource('FormandoBaseAvaliacao', App\Http\Controllers\FormandoBaseAvaliacaoController::class);

# Formandos base recebimentos
Route::get('FormandoBase/ConsultaFormandoBaseRecebimento/{id}', [App\Http\Controllers\FormandoBaseRecebimentosController::class, 'ConsultaFormandoBaseRecebimento'])->name('formandoBase.ConsultaFormandoBaseRecebimento');;
Route::resource('FormandoBaseRecebimentos', App\Http\Controllers\FormandoBaseRecebimentosController::class);


 # Representantes
    Route::post('Representantes/CreateRedeSocialRepresentantes', [App\Http\Controllers\RepresentantesController::class, 'CreateRedeSocialRepresentantes'])->name('representantes.RedeSocialRepresentantes');
    Route::get('Representantes/RepresentantesCadastro', [App\Http\Controllers\RepresentantesController::class, 'representantecadastro'])->name('representantes.cadastrorepresentante');
    Route::resource('Representantes', App\Http\Controllers\RepresentantesController::class);




# Cargo Profissional
Route::resource('CargoProfissional', App\Http\Controllers\CargoProfissionalController::class);

# Função Profissional
Route::resource('FuncaoProfissional', App\Http\Controllers\FuncaoProfissionalController::class);

 # Representantes
 Route::resource('TipoRepresentantes', App\Http\Controllers\TipoRepresentanteController::class);


 # Tipo de esportes
Route::resource('TipoEsporte', App\Http\Controllers\TipoEsporteController::class);

 # Tipo de arquivos
 Route::resource('TipoArquivo', App\Http\Controllers\TipoArquivoController::class);

 # Preparadores
 Route::post('Preparadores/CreateArquivoPreparadores', [App\Http\Controllers\PreparadoresController::class, 'CreateArquivoPreparadores'])->name('Preparadores.ArquivoPreparadores');
 Route::resource('Preparadores', App\Http\Controllers\PreparadoresController::class);
 Route::resource('PreparadoresArquivos', App\Http\Controllers\PreparadoresArquivosController::class);

 # Posicoes
 Route::resource('Posicoes', App\Http\Controllers\PosicoesController::class);

 # Categorias
 Route::resource('Categorias', App\Http\Controllers\CategoriasController::class);

# Redes sociais
Route::resource('RedeSocial', App\Http\Controllers\RedeSocialController::class);

# Redes sociais usuários
Route::resource('RedeSocialUsuarios', App\Http\Controllers\RedeSocialUsuarioController::class);

    # PLANO DE CONTAS
    Route::get('PlanoContas/pesquisaavancada', [App\Http\Controllers\GoogleCalendarController::class, 'pesquisaavancada'])->name('planocontas.pesquisaavancada');

    #testes
    Route::get('Agenda', [\App\Http\Controllers\TesteController::class, 'googleAgenda']);

    #GoogleCalendar
    Route::get('Agenda/starthoje', [App\Http\Controllers\GoogleCalendarController::class, 'starthoje'])->name('Agenda.starthoje');
    Route::get('Agenda/startanterior', [App\Http\Controllers\GoogleCalendarController::class, 'startanterior'])->name('Agenda.startanterior');
    Route::get('Agenda/startposterior', [App\Http\Controllers\GoogleCalendarController::class, 'startposterior'])->name('Agenda.startposterior');
    Route::get('Agenda/dashboard/{token?}', [App\Http\Controllers\GoogleCalendarController::class, 'dashboard'])->name('google.dashboard');
    Route::resource('Agenda', App\Http\Controllers\GoogleCalendarController::class);

    #Empresas

    Route::put('Empresas/desbloquearempresas', [App\Http\Controllers\EmpresaController::class, 'desbloquearempresas'])->name('Empresas.DesbloquearEmpresas');
    Route::put('Empresas/bloquearempresas', [App\Http\Controllers\EmpresaController::class, 'bloquearempresas'])->name('Empresas.BloquearEmpresas');
    Route::put('Empresas/desbloquearempresas', [App\Http\Controllers\EmpresaController::class, 'desbloquearempresas'])->name('Empresas.DesbloquearEmpresas');
    Route::resource('Empresas', App\Http\Controllers\EmpresaController::class);

    #Gerenciamento de Usuários
    Route::resource('Usuarios', App\Http\Controllers\UserController::class);
    Route::post('Usuarios/salvarpermissao/{id}', [App\Http\Controllers\UserController::class, 'salvarpermissao']);
    Route::post('Usuarios/salvarfuncao/{id}', [App\Http\Controllers\UserController::class, 'salvarfuncao']);
    Route::post('Usuarios/salvar-empresa/{id}', [App\Http\Controllers\UserController::class, 'salvarEmpresa']);

    #Gerenciamento de permissões e funções
    Route::resource('Permissoes', App\Http\Controllers\PermissionController::class);
    // Route::resource('ModelodeFuncoes', App\Http\Controllers\Model_has_RoleController::class);

    #PlanoContas
    Route::get('PlanoContas/pesquisaavancada', [App\Http\Controllers\PlanoContaController::class, 'pesquisaavancada'])->name('planocontas.pesquisaavancada');
    Route::post('PlanoContas/pesquisaavancada', [App\Http\Controllers\PlanoContaController::class, 'pesquisaavancadapost'])->name('planocontas.pesquisaavancada.post');
    Route::get('PlanoContas/autenticar/{EmpresaID}', [App\Http\Controllers\EmpresaController::class, 'autenticar'])->name('planocontas.autenticar');
    Route::get('PlanoContas/dashboard', [App\Http\Controllers\PlanoContaController::class, 'dashboard'])->name('planocontas.dashboard');
    Route::post('PlanoContas/BalanceteEmpresa', [App\Http\Controllers\PlanoContaController::class, 'BalanceteEmpresa'])->name('planocontas.balanceteempresa');
    Route::get('PlanoContas/Balancetes', [App\Http\Controllers\PlanoContaController::class, 'Balancetes'])->name('planocontas.balancetes');
    Route::resource('PlanoContas', App\Http\Controllers\PlanoContaController::class);

    #Lançamentos
    Route::post('Lancamentos/ExportarSkalaExcelpost',[App\Http\Controllers\LancamentosController::class,'ExportarSkalaExcelpost'])->name('lancamentos.exportarskalaExcelpost');
    Route::post('Lancamentos/ExportarSkalapost',[App\Http\Controllers\LancamentosController::class,'ExportarSkalapost'])->name('lancamentos.exportarskalapost');
    Route::post('lancamentos/lancamentotabelaprice',[App\Http\Controllers\LancamentosController::class,'lancamentotabelaprice'])->name('lancamentos.lancamentotabelaprice');
    Route::get('lancamentos/exportarskala',[App\Http\Controllers\LancamentosController::class,'ExportarSkala'])->name('lancamentos.ExportarSkala');
    Route::get('lancamentos/exportarskalaexcel',[App\Http\Controllers\LancamentosController::class,'ExportarSkalaExcel'])->name('lancamentos.ExportarSkalaExcel');
    Route::get('Lancamentos/lancamentoinformaprice',[App\Http\Controllers\LancamentosController::class,'lancamentoinformaprice'])->name('lancamentos.lancamentoinformaprice');
    Route::get('Lancamentos/Informaprice',[App\Http\Controllers\LancamentosController::class,'Informaprice'])->name('lancamentos.informaprice');
    Route::post('lancamentos/tabelaprice',[App\Http\Controllers\LancamentosController::class,'tabelaprice'])->name('lancamentos.tabelaprice');
    Route::get('lancamentos/download/{id}',[App\Http\Controllers\LancamentosController::class,'baixarArquivo'])->name('lancamentos.download');
    Route::get('Lancamentos/tabelaprice', function () { return view('Lancamento.tabelaprice');})->name('lancamentos.tabelapriceresultado');



    #Contas
    Route::get('Contas/Extrato/{contaID}', [App\Http\Controllers\ContaController::class, 'extrato']);
    Route::get('Contas/GerarExtratoPDF', [App\Http\Livewire\Conta\Extrato::class, 'GerarExtratoPDF'])->name('Extrato.gerarpdf');
    Route::resource('Contas', App\Http\Controllers\ContaController::class);
    Route::resource('ContasCobranca', App\Http\Controllers\ContaCobrancaController::class);

    #Funções
    Route::resource('Funcoes', App\Http\Controllers\RoleController::class);
    Route::post('Funcoes/salvarpermissao/{id}', [App\Http\Controllers\RoleController::class, 'salvarpermissao']);

    #Moedas e valores
    Route::get('Moedas/dashboard', [App\Http\Controllers\MoedaController::class, 'dashboard'])->name('moedas.dashboard');
    Route::resource('Moedas', App\Http\Controllers\MoedaController::class);

    Route::resource('MoedasValores', App\Http\Controllers\MoedaValoresController::class);

#LANCAMENTO - DOCUMENTO
// Route::get('Moedas/dashboard', [App\Http\Controllers\MoedaController::class, 'dashboard'])->name('moedas.dashboard');
Route::post('LancamentosDocumentos/pesquisaavancada', [App\Http\Controllers\LancamentosDocumentosController::class, 'pesquisaavancada'])->name('lancamentosdocumentos.pesquisaavancada');
Route::get('/lancamentosdocumentos/{id}',[App\Http\Controllers\LancamentosDocumentosController::class,'indexpost'])->name('LancamentosDocumentosID.index');
Route::resource('LancamentosDocumentos', App\Http\Controllers\LancamentosDocumentosController::class);

    #ARQUIVOS

    Route::get('LeituraArquivo/GerarPDF', [App\Http\Controllers\LeituraArquivoController::class, 'GerarPDF'])->name('LeituraArquivo.gerarpdf');
    Route::post('LeituraArquivo/SelecionaDatas', [App\Http\Controllers\LeituraArquivoController::class, 'SelecionaDatas'])->name('LeituraArquivo.SelecionaDatas');
    Route::post('LeituraArquivo/SelecionaLinha', [App\Http\Controllers\LeituraArquivoController::class, 'SelecionaLinha'])->name('LeituraArquivo.SelecionaLinha');
    Route::post('LeituraArquivo/SelecionaDatasExtratoSicrediPJ', [App\Http\Controllers\LeituraArquivoController::class, 'SelecionaDatasExtratoSicrediPJ'])->name('LeituraArquivo.SelecionaDatasExtratoSicrediPJ');
    // Route::post('LeituraArquivo/SelecionaDatasFaturaEmAberto', [App\Http\Controllers\LeituraArquivoController::class, 'SelecionaDatasFaturaEmAberto'])->name('LeituraArquivo.SelecionaDatasFaturaEmAberto');
    Route::post('FaturaSicrediAberto/SelecionaDatasFaturaEmAberto', [App\Http\Controllers\FaturaCartaoCreditoSicrediAbertoController::class, 'SelecionaDatasFaturaEmAberto'])->name('FaturaSicrediAberto.SelecionaDatasFaturaEmAberto');
 Route::get('LeituraArquivo/SomenteLinha', function () { return view('LeituraArquivo.SomenteLinha');})->name('LeituraArquivo.SomenteLinha');
    Route::resource('LeituraArquivo', App\Http\Controllers\LeituraArquivoController::class);



    #TRADEIDEA
    Route::post('/salvarTradeidea', [App\Http\Controllers\TradeideaController::class,'salvarTradeidea'])->name('salvar.tradeidea');
    Route::post('Tradeidea/ImportaArquivoExcelTradeIdea', [App\Http\Controllers\TradeideaController::class,
                'ImportaArquivoExcelTradeIdea'])->name('Tradeidea.ImportaArquivoExcelTradeIdea');
    Route::get('Tradeidea/Importaexceltradeidea', function () { return view('Tradeidea.importarexceltradeidea');})->name('Tradeidea.importarexceltradeidea');
    Route::get('Tradeidea/Mostraexceltradeidea', function () { return view('Tradeidea.mostraexceltradeidea');})->name('Tradeidea.mostraexceltradeidea');
    Route::resource('Tradeidea', App\Http\Controllers\TradeideaController::class);


    #EXTRATO CONECTCAR
    Route::post('ConectCar/ExtratoConectCar', [App\Http\Controllers\ExtratoConectCarController::class, 'ExtratoConectar'])->name('ConectCar.ExtratoConectCar');

    #Faturamentos
    Route::resource('Faturamentos', App\Http\Controllers\FaturamentosController::class);

    #Sicredi
    Route::get('Sicredi/ConsultaBoleto', App\Http\Livewire\Sicredi\ConsultaBoleto::class);
    Route::resource('DevSicredi', App\Http\Controllers\DevSicrediController::class);
    #Faturamentos Sicredi
    Route::resource('Sicredi', App\Http\Controllers\SicrediController::class);

    #Historicos
    Route::post('Historicos/pesquisapost', [App\Http\Controllers\HistoricoController::class, 'pesquisapost'])->name('pesquisapost');
    Route::resource('Historicos', App\Http\Controllers\HistoricoController::class);

    #Contabilidade
    Route::get('/Contabilidade', function () {
        return view('Contabilidade.dashboard');
    })->name('dashboardContabilidade');

    Route::get('/ContasCarro', function () {
        return view('Contas.carros');
    })->name('ContasCarros');

    #Cobrança
    // Route::resource('Cobranca', App\Http\Controllers\CobrancaController::class);

    Route::get('/Cobranca', function () {
        return view('Cobranca/dashboard');
    });

    #Cadastros
    Route::get('/Cadastros', function () {
        return view('Cadastros/dashboard');
    });
});

require __DIR__ . '/auth.php';
