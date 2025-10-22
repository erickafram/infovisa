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
    Route::get('/estabelecimentos/create/juridica', [EstabelecimentoController::class, 'createJuridica'])->name('estabelecimentos.create.juridica');
    Route::get('/estabelecimentos/create/fisica', [EstabelecimentoController::class, 'createFisica'])->name('estabelecimentos.create.fisica');
    Route::get('/estabelecimentos/{id}/atividades', [EstabelecimentoController::class, 'editAtividades'])->name('estabelecimentos.atividades.edit');
    Route::post('/estabelecimentos/{id}/atividades', [EstabelecimentoController::class, 'updateAtividades'])->name('estabelecimentos.atividades.update');
    
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
    Route::get('/documentos/modelos/{tipoId}', [\App\Http\Controllers\DocumentoDigitalController::class, 'buscarModelos'])->name('documentos.modelos');
    Route::get('/documentos/{id}/pdf', [\App\Http\Controllers\DocumentoDigitalController::class, 'gerarPdf'])->name('documentos.pdf');
    Route::post('/documentos/{id}/assinar', [\App\Http\Controllers\DocumentoDigitalController::class, 'assinar'])->name('documentos.assinar');
    
    // Processos - Listagem Geral
    Route::get('/processos', [\App\Http\Controllers\ProcessoController::class, 'indexGeral'])->name('processos.index-geral');
    
    // Processos por Estabelecimento
    Route::get('/estabelecimentos/{id}/processos', [\App\Http\Controllers\ProcessoController::class, 'index'])->name('estabelecimentos.processos.index');
    Route::get('/estabelecimentos/{id}/processos/create', [\App\Http\Controllers\ProcessoController::class, 'create'])->name('estabelecimentos.processos.create');
    Route::post('/estabelecimentos/{id}/processos', [\App\Http\Controllers\ProcessoController::class, 'store'])->name('estabelecimentos.processos.store');
    Route::get('/estabelecimentos/{id}/processos/{processo}', [\App\Http\Controllers\ProcessoController::class, 'show'])->name('estabelecimentos.processos.show');
    Route::patch('/estabelecimentos/{id}/processos/{processo}/status', [\App\Http\Controllers\ProcessoController::class, 'updateStatus'])->name('estabelecimentos.processos.updateStatus');
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
    });
});

// Rota temporária para consulta de CNPJ (sem middleware CSRF para AJAX)
Route::post('/api/consultar-cnpj', [App\Http\Controllers\Api\CnpjController::class, 'consultar'])->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

/*
|--------------------------------------------------------------------------
| Rotas Protegidas - Área da Empresa (Usuários Externos)
|--------------------------------------------------------------------------
*/

