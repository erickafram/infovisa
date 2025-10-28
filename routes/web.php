<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Auth\RegistroController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\EstabelecimentoController;

/*
|--------------------------------------------------------------------------
| Rotas Públicas
|--------------------------------------------------------------------------
*/

// Página Inicial
Route::get('/', [HomeController::class, 'index'])->name('home');

// Consultar Processo
Route::post('/consultar-processo', [HomeController::class, 'consultarProcesso'])->name('consultar.processo');

// Verificar Documento
Route::post('/verificar-documento', [HomeController::class, 'verificarDocumento'])->name('verificar.documento');

// Verificar Autenticidade de Documento
Route::get('/verificar-autenticidade', [\App\Http\Controllers\AutenticidadeController::class, 'index'])->name('verificar.autenticidade.form');
Route::post('/verificar-autenticidade', [\App\Http\Controllers\AutenticidadeController::class, 'verificar'])->name('verificar.autenticidade.verificar');
Route::get('/verificar-autenticidade/{codigo}', [\App\Http\Controllers\AutenticidadeController::class, 'verificar'])->name('verificar.autenticidade');
Route::get('/documento-autenticado/{codigo}/pdf', [\App\Http\Controllers\AutenticidadeController::class, 'visualizarPdf'])->name('documento.autenticado.pdf');

/*
|--------------------------------------------------------------------------
| Rotas de Autenticação - Login Unificado
|--------------------------------------------------------------------------
*/

// Registro (somente usuários externos)
Route::middleware('guest:externo,interno')->group(function () {
    Route::get('/registro', [RegistroController::class, 'showRegistroForm'])->name('registro');
    Route::post('/registro', [RegistroController::class, 'registro'])->name('registro.submit');
    
    // Login Unificado (detecta automaticamente o tipo de usuário)
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
});

// Logout (funciona para ambos os guards)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Rotas Protegidas - Área da Empresa
|--------------------------------------------------------------------------
*/

Route::middleware('auth:externo')->prefix('company')->name('company.')->group(function () {
    Route::get('/dashboard', function () {
        return 'Dashboard da Empresa - Em Desenvolvimento';
    })->name('dashboard');
});

/*
|--------------------------------------------------------------------------
| Rotas Protegidas - Área Administrativa
|--------------------------------------------------------------------------
*/

Route::middleware('auth:interno')->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Estabelecimentos
    Route::get('/estabelecimentos/pendentes', [EstabelecimentoController::class, 'pendentes'])->name('estabelecimentos.pendentes');
    Route::get('/estabelecimentos/rejeitados', [EstabelecimentoController::class, 'rejeitados'])->name('estabelecimentos.rejeitados');
    Route::get('/estabelecimentos/desativados', [EstabelecimentoController::class, 'desativados'])->name('estabelecimentos.desativados');
    Route::get('/estabelecimentos/create/juridica', [EstabelecimentoController::class, 'createJuridica'])->name('estabelecimentos.create.juridica');
    Route::get('/estabelecimentos/create/fisica', [EstabelecimentoController::class, 'createFisica'])->name('estabelecimentos.create.fisica');
    Route::get('/estabelecimentos/buscar-por-cpf/{cpf}', [EstabelecimentoController::class, 'buscarPorCpf'])->name('estabelecimentos.buscar-cpf');
    Route::get('/estabelecimentos/{id}/atividades', [EstabelecimentoController::class, 'editAtividades'])->name('estabelecimentos.atividades.edit');
    Route::post('/estabelecimentos/{id}/atividades', [EstabelecimentoController::class, 'updateAtividades'])->name('estabelecimentos.atividades.update');
    Route::post('/estabelecimentos/{id}/alterar-competencia', [EstabelecimentoController::class, 'alterarCompetencia'])->name('estabelecimentos.alterar-competencia');
    Route::get('/estabelecimentos/{id}/historico', [EstabelecimentoController::class, 'historico'])->name('estabelecimentos.historico');
    Route::post('/estabelecimentos/{id}/aprovar', [EstabelecimentoController::class, 'aprovar'])->name('estabelecimentos.aprovar');
    Route::post('/estabelecimentos/{id}/rejeitar', [EstabelecimentoController::class, 'rejeitar'])->name('estabelecimentos.rejeitar');
    Route::post('/estabelecimentos/{id}/reiniciar', [EstabelecimentoController::class, 'reiniciar'])->name('estabelecimentos.reiniciar');
    Route::post('/estabelecimentos/{id}/voltar-pendente', [EstabelecimentoController::class, 'voltarPendente'])->name('estabelecimentos.voltar-pendente');
    Route::post('/estabelecimentos/{id}/desativar', [EstabelecimentoController::class, 'desativar'])->name('estabelecimentos.desativar');
    Route::post('/estabelecimentos/{id}/ativar', [EstabelecimentoController::class, 'ativar'])->name('estabelecimentos.ativar');
    
    // Usuários Vinculados ao Estabelecimento
    Route::get('/estabelecimentos/{id}/usuarios', [EstabelecimentoController::class, 'usuariosIndex'])->name('estabelecimentos.usuarios.index');
    Route::post('/estabelecimentos/{id}/usuarios/vincular', [EstabelecimentoController::class, 'vincularUsuario'])->name('estabelecimentos.usuarios.vincular');
    Route::delete('/estabelecimentos/{id}/usuarios/{usuario_id}', [EstabelecimentoController::class, 'desvincularUsuario'])->name('estabelecimentos.usuarios.desvincular');
    Route::put('/estabelecimentos/{id}/usuarios/{usuario_id}', [EstabelecimentoController::class, 'atualizarVinculo'])->name('estabelecimentos.usuarios.atualizar');
    Route::get('/usuarios-externos/buscar', [EstabelecimentoController::class, 'buscarUsuarios'])->name('usuarios-externos.buscar');
    
    // Responsáveis
    Route::get('/estabelecimentos/{id}/responsaveis', [\App\Http\Controllers\ResponsavelController::class, 'index'])->name('estabelecimentos.responsaveis.index');
    Route::get('/estabelecimentos/{id}/responsaveis/create/{tipo}', [\App\Http\Controllers\ResponsavelController::class, 'create'])->name('estabelecimentos.responsaveis.create');
    Route::post('/estabelecimentos/{id}/responsaveis', [\App\Http\Controllers\ResponsavelController::class, 'store'])->name('estabelecimentos.responsaveis.store');
    Route::delete('/estabelecimentos/{estabelecimento}/responsaveis/{responsavel}', [\App\Http\Controllers\ResponsavelController::class, 'destroy'])->name('estabelecimentos.responsaveis.destroy');
    Route::post('/responsaveis/buscar-cpf', [\App\Http\Controllers\ResponsavelController::class, 'buscarPorCpf'])->name('responsaveis.buscar-cpf');
    
    // Rotas de Responsáveis Globais
    Route::get('/responsaveis', [\App\Http\Controllers\Admin\ResponsavelGlobalController::class, 'index'])->name('responsaveis.index');
    Route::get('/responsaveis/{id}', [\App\Http\Controllers\Admin\ResponsavelGlobalController::class, 'show'])->name('responsaveis.show');
    
    // Documentos Digitais
    Route::get('/documentos', [\App\Http\Controllers\DocumentoDigitalController::class, 'index'])->name('documentos.index');
    Route::get('/documentos/create', [\App\Http\Controllers\DocumentoDigitalController::class, 'create'])->name('documentos.create');
    Route::post('/documentos', [\App\Http\Controllers\DocumentoDigitalController::class, 'store'])->name('documentos.store');
    Route::get('/documentos/{id}', [\App\Http\Controllers\DocumentoDigitalController::class, 'show'])->name('documentos.show');
    Route::get('/documentos/{id}/edit', [\App\Http\Controllers\DocumentoDigitalController::class, 'edit'])->name('documentos.edit');
    Route::put('/documentos/{id}', [\App\Http\Controllers\DocumentoDigitalController::class, 'update'])->name('documentos.update');
    Route::delete('/documentos/{id}', [\App\Http\Controllers\DocumentoDigitalController::class, 'destroy'])->name('documentos.destroy');
    Route::post('/documentos/{id}/mover-pasta', [\App\Http\Controllers\DocumentoDigitalController::class, 'moverPasta'])->name('documentos.mover-pasta');
    Route::post('/documentos/{id}/renomear', [\App\Http\Controllers\DocumentoDigitalController::class, 'renomear'])->name('documentos.renomear');
    Route::get('/documentos/modelos/{tipoId}', [\App\Http\Controllers\DocumentoDigitalController::class, 'buscarModelos'])->name('documentos.modelos');
    
    // Assinatura Digital
    Route::get('/assinatura/configurar-senha', [\App\Http\Controllers\AssinaturaDigitalController::class, 'configurarSenha'])->name('assinatura.configurar-senha');
    Route::post('/assinatura/salvar-senha', [\App\Http\Controllers\AssinaturaDigitalController::class, 'salvarSenha'])->name('assinatura.salvar-senha');
    Route::get('/assinatura/pendentes', [\App\Http\Controllers\AssinaturaDigitalController::class, 'documentosPendentes'])->name('assinatura.pendentes');
    Route::get('/assinatura/assinar/{documentoId}', [\App\Http\Controllers\AssinaturaDigitalController::class, 'assinar'])->name('assinatura.assinar');
    Route::post('/assinatura/processar/{documentoId}', [\App\Http\Controllers\AssinaturaDigitalController::class, 'processar'])->name('assinatura.processar');
    Route::get('/documentos/{id}/pdf', [\App\Http\Controllers\DocumentoDigitalController::class, 'gerarPdf'])->name('documentos.pdf');
    Route::post('/documentos/{id}/assinar', [\App\Http\Controllers\DocumentoDigitalController::class, 'assinar'])->name('documentos.assinar');
    Route::post('/documentos/{id}/versoes/{versao}/restaurar', [\App\Http\Controllers\DocumentoDigitalController::class, 'restaurarVersao'])->name('documentos.restaurarVersao');
    Route::post('/documentos/{id}/gerenciar-assinantes', [\App\Http\Controllers\DocumentoDigitalController::class, 'gerenciarAssinantes'])->name('documentos.gerenciar-assinantes');
    Route::delete('/documentos/assinaturas/{id}', [\App\Http\Controllers\DocumentoDigitalController::class, 'removerAssinante'])->name('documentos.remover-assinante');
    
    // Processos - Listagem Geral
    Route::get('/processos', [\App\Http\Controllers\ProcessoController::class, 'indexGeral'])->name('processos.index-geral');
    
    // Processos por Estabelecimento
    Route::get('/estabelecimentos/{id}/processos', [\App\Http\Controllers\ProcessoController::class, 'index'])->name('estabelecimentos.processos.index');
    Route::get('/estabelecimentos/{id}/processos/create', [\App\Http\Controllers\ProcessoController::class, 'create'])->name('estabelecimentos.processos.create');
    Route::post('/estabelecimentos/{id}/processos', [\App\Http\Controllers\ProcessoController::class, 'store'])->name('estabelecimentos.processos.store');
    Route::get('/estabelecimentos/{id}/processos/{processo}', [\App\Http\Controllers\ProcessoController::class, 'show'])->name('estabelecimentos.processos.show');
    Route::patch('/estabelecimentos/{id}/processos/{processo}/status', [\App\Http\Controllers\ProcessoController::class, 'updateStatus'])->name('estabelecimentos.processos.updateStatus');
    Route::post('/estabelecimentos/{id}/processos/{processo}/acompanhar', [\App\Http\Controllers\ProcessoController::class, 'toggleAcompanhamento'])->name('estabelecimentos.processos.toggleAcompanhamento');
    Route::delete('/estabelecimentos/{id}/processos/{processo}', [\App\Http\Controllers\ProcessoController::class, 'destroy'])->name('estabelecimentos.processos.destroy');
    
    // Upload de arquivos em processos
    Route::post('/estabelecimentos/{id}/processos/{processo}/upload', [\App\Http\Controllers\ProcessoController::class, 'uploadArquivo'])->name('estabelecimentos.processos.upload');
    Route::get('/estabelecimentos/{id}/processos/{processo}/documentos/{documento}/visualizar', [\App\Http\Controllers\ProcessoController::class, 'visualizarArquivo'])->name('estabelecimentos.processos.visualizar');
    Route::get('/estabelecimentos/{id}/processos/{processo}/documentos/{documento}/download', [\App\Http\Controllers\ProcessoController::class, 'downloadArquivo'])->name('estabelecimentos.processos.download');
    Route::patch('/estabelecimentos/{id}/processos/{processo}/documentos/{documento}/nome', [\App\Http\Controllers\ProcessoController::class, 'updateNomeArquivo'])->name('estabelecimentos.processos.updateNome');
    Route::delete('/estabelecimentos/{id}/processos/{processo}/documentos/{documento}', [\App\Http\Controllers\ProcessoController::class, 'deleteArquivo'])->name('estabelecimentos.processos.deleteArquivo');
    
    // Gerar documento digital
    Route::post('/estabelecimentos/{id}/processos/{processo}/gerar-documento', [\App\Http\Controllers\ProcessoController::class, 'gerarDocumento'])->name('estabelecimentos.processos.gerarDocumento');
    
    // Pastas do Processo
    Route::get('/estabelecimentos/{id}/processos/{processo}/pastas', [\App\Http\Controllers\ProcessoPastaController::class, 'index'])->name('estabelecimentos.processos.pastas.index');
    Route::post('/estabelecimentos/{id}/processos/{processo}/pastas', [\App\Http\Controllers\ProcessoPastaController::class, 'store'])->name('estabelecimentos.processos.pastas.store');
    Route::put('/estabelecimentos/{id}/processos/{processo}/pastas/{pasta}', [\App\Http\Controllers\ProcessoPastaController::class, 'update'])->name('estabelecimentos.processos.pastas.update');
    Route::delete('/estabelecimentos/{id}/processos/{processo}/pastas/{pasta}', [\App\Http\Controllers\ProcessoPastaController::class, 'destroy'])->name('estabelecimentos.processos.pastas.destroy');
    Route::post('/estabelecimentos/{id}/processos/{processo}/pastas/mover', [\App\Http\Controllers\ProcessoPastaController::class, 'moverItem'])->name('estabelecimentos.processos.pastas.mover');
    
    Route::resource('/estabelecimentos', EstabelecimentoController::class)->names([
        'index' => 'estabelecimentos.index',
        'create' => 'estabelecimentos.create',
        'store' => 'estabelecimentos.store',
        'show' => 'estabelecimentos.show',
        'edit' => 'estabelecimentos.edit',
        'update' => 'estabelecimentos.update',
        'destroy' => 'estabelecimentos.destroy',
    ]);

    // Usuários Internos
    Route::resource('usuarios-internos', \App\Http\Controllers\UsuarioInternoController::class)->parameters([
        'usuarios-internos' => 'usuarioInterno'
    ]);
    
    // Usuários Externos
    Route::resource('usuarios-externos', \App\Http\Controllers\UsuarioExternoController::class)->parameters([
        'usuarios-externos' => 'usuarioExterno'
    ]);

    // Configurações
    Route::prefix('configuracoes')->name('configuracoes.')->group(function () {
        // Página principal de configurações
        Route::get('/', [\App\Http\Controllers\ConfiguracaoController::class, 'index'])->name('index');
        
        // Tipos de Processo
        Route::resource('tipos-processo', \App\Http\Controllers\TipoProcessoController::class)->parameters([
            'tipos-processo' => 'tipoProcesso'
        ]);
        
        // Tipos de Documento
        Route::resource('tipos-documento', \App\Http\Controllers\TipoDocumentoController::class)->parameters([
            'tipos-documento' => 'tipoDocumento'
        ]);
        
        // Modelos de Documentos
        Route::resource('modelos-documento', \App\Http\Controllers\ModeloDocumentoController::class)->parameters([
            'modelos-documento' => 'modeloDocumento'
        ]);
        
        // Pactuação (Competências Municipais e Estaduais)
        Route::prefix('pactuacao')->name('pactuacao.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\PactuacaoController::class, 'index'])->name('index');
            Route::get('/{id}', [\App\Http\Controllers\Admin\PactuacaoController::class, 'show'])->name('show');
            Route::post('/', [\App\Http\Controllers\Admin\PactuacaoController::class, 'store'])->name('store');
            Route::post('/multiple', [\App\Http\Controllers\Admin\PactuacaoController::class, 'storeMultiple'])->name('store-multiple');
            Route::put('/{id}', [\App\Http\Controllers\Admin\PactuacaoController::class, 'update'])->name('update');
            Route::post('/{id}/toggle', [\App\Http\Controllers\Admin\PactuacaoController::class, 'toggleStatus'])->name('toggle');
            Route::post('/{id}/adicionar-excecao', [\App\Http\Controllers\Admin\PactuacaoController::class, 'adicionarExcecao'])->name('adicionar-excecao');
            Route::post('/{id}/remover-excecao', [\App\Http\Controllers\Admin\PactuacaoController::class, 'removerExcecao'])->name('remover-excecao');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\PactuacaoController::class, 'destroy'])->name('destroy');
            Route::get('/buscar-cnaes', [\App\Http\Controllers\Admin\PactuacaoController::class, 'buscarCnaes'])->name('buscar-cnaes');
            Route::post('/buscar-questionarios', [\App\Http\Controllers\Admin\PactuacaoController::class, 'buscarQuestionarios'])->name('buscar-questionarios');
        });
        
        // Municípios
        Route::prefix('municipios')->name('municipios.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\MunicipioController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\MunicipioController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\MunicipioController::class, 'store'])->name('store');
            Route::get('/{id}', [\App\Http\Controllers\Admin\MunicipioController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [\App\Http\Controllers\Admin\MunicipioController::class, 'edit'])->name('edit');
            Route::put('/{id}', [\App\Http\Controllers\Admin\MunicipioController::class, 'update'])->name('update');
            Route::post('/{id}/toggle', [\App\Http\Controllers\Admin\MunicipioController::class, 'toggleStatus'])->name('toggle');
            Route::delete('/{id}', [\App\Http\Controllers\Admin\MunicipioController::class, 'destroy'])->name('destroy');
            Route::get('/buscar', [\App\Http\Controllers\Admin\MunicipioController::class, 'buscar'])->name('buscar');
        });
    });
});

// Rota temporária para consulta de CNPJ (sem middleware CSRF para AJAX)
Route::post('/api/consultar-cnpj', [App\Http\Controllers\Api\CnpjController::class, 'consultar'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

/*
|--------------------------------------------------------------------------
| Rotas Protegidas - Área da Empresa (Usuários Externos)
|--------------------------------------------------------------------------
*/

