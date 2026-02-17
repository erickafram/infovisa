<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfiguracaoSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ConfiguracaoSistemaController extends Controller
{
    /**
     * Exibe a página de configurações gerais do sistema
     */
    public function index()
    {
        $logomarcaEstadual = ConfiguracaoSistema::where('chave', 'logomarca_estadual')->first();
        
        // Configurações da IA
        $iaAtiva = ConfiguracaoSistema::where('chave', 'ia_ativa')->first();
        $iaExternaAtiva = ConfiguracaoSistema::where('chave', 'ia_externa_ativa')->first();
        $iaApiKey = ConfiguracaoSistema::where('chave', 'ia_api_key')->first();
        $iaApiUrl = ConfiguracaoSistema::where('chave', 'ia_api_url')->first();
        $iaModel = ConfiguracaoSistema::where('chave', 'ia_model')->first();
        $iaBuscaWeb = ConfiguracaoSistema::where('chave', 'ia_busca_web')->first();
        
        // Configurações do Chat Interno
        $chatInternoAtivo = ConfiguracaoSistema::where('chave', 'chat_interno_ativo')->first();
        
        // Configuração do Assistente de Redação
        $assistenteRedacaoAtivo = ConfiguracaoSistema::where('chave', 'assistente_redacao_ativo')->first();
        
        return view('admin.configuracoes.sistema.index', compact(
            'logomarcaEstadual',
            'iaAtiva',
            'iaExternaAtiva',
            'iaApiKey',
            'iaApiUrl',
            'iaModel',
            'iaBuscaWeb',
            'chatInternoAtivo',
            'assistenteRedacaoAtivo'
        ));
    }

    /**
     * Atualiza as configurações do sistema
     */
    public function update(Request $request)
    {
        $request->validate([
            'logomarca_estadual' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'remover_logomarca_estadual' => 'nullable|boolean',
            'ia_ativa' => 'nullable|boolean',
            'ia_externa_ativa' => 'nullable|boolean',
            'ia_api_key' => 'nullable|string',
            'ia_api_url' => 'nullable|url',
            'ia_model' => 'nullable|string',
            'chat_interno_ativo' => 'nullable|boolean',
            'assistente_redacao_ativo' => 'nullable|boolean',
        ], [
            'logomarca_estadual.image' => 'O arquivo deve ser uma imagem',
            'logomarca_estadual.mimes' => 'A logomarca deve ser um arquivo: jpeg, png, jpg ou svg',
            'logomarca_estadual.max' => 'A logomarca não pode ser maior que 2MB',
            'ia_api_url.url' => 'A URL da API deve ser válida',
        ]);
        
        // Identifica qual formulário foi submetido baseado nos campos presentes
        $isFormularioIA = $request->has('_form_ia') || 
                          $request->filled('ia_api_key') || 
                          $request->filled('ia_api_url') || 
                          $request->filled('ia_model');
        
        $isFormularioChat = $request->has('_form_chat');
        
        $isFormularioLogomarca = $request->hasFile('logomarca_estadual') || 
                                  $request->has('remover_logomarca_estadual');
        
        // Atualiza configurações da IA apenas se for o formulário de IA
        if ($isFormularioIA) {
            ConfiguracaoSistema::updateOrCreate(
                ['chave' => 'ia_ativa'],
                ['valor' => $request->has('ia_ativa') ? 'true' : 'false']
            );

            ConfiguracaoSistema::updateOrCreate(
                ['chave' => 'ia_externa_ativa'],
                ['valor' => $request->has('ia_externa_ativa') ? 'true' : 'false']
            );
            
            ConfiguracaoSistema::updateOrCreate(
                ['chave' => 'ia_api_key'],
                ['valor' => $request->ia_api_key ?? '']
            );
            
            ConfiguracaoSistema::updateOrCreate(
                ['chave' => 'ia_api_url'],
                ['valor' => $request->ia_api_url ?? '']
            );
            
            ConfiguracaoSistema::updateOrCreate(
                ['chave' => 'ia_model'],
                ['valor' => $request->ia_model ?? '']
            );
            
            // Busca na web
            ConfiguracaoSistema::updateOrCreate(
                ['chave' => 'ia_busca_web'],
                ['valor' => $request->has('ia_busca_web') ? 'true' : 'false']
            );
            
            return redirect()
                ->route('admin.configuracoes.sistema.index')
                ->with('success', 'Configurações do Assistente de IA atualizadas com sucesso!');
        }
        
        // Atualiza configurações do Chat Interno apenas se for o formulário de Chat
        if ($isFormularioChat) {
            ConfiguracaoSistema::updateOrCreate(
                ['chave' => 'chat_interno_ativo'],
                ['valor' => $request->has('chat_interno_ativo') ? 'true' : 'false']
            );
            
            ConfiguracaoSistema::updateOrCreate(
                ['chave' => 'assistente_redacao_ativo'],
                ['valor' => $request->has('assistente_redacao_ativo') ? 'true' : 'false']
            );
            
            return redirect()
                ->route('admin.configuracoes.sistema.index')
                ->with('success', 'Configurações do Chat Interno atualizadas com sucesso!');
        }
        
        // Verifica se foi apenas atualização de IA (sem logomarca) - fallback para compatibilidade
        $atualizouIA = $request->has('ia_ativa') || 
                       $request->has('ia_externa_ativa') || 
                       $request->filled('ia_api_key') || 
                       $request->filled('ia_api_url') || 
                       $request->filled('ia_model') ||
                       $request->has('ia_busca_web') ||
                       $request->has('chat_interno_ativo') ||
                       $request->has('assistente_redacao_ativo');

        $config = ConfiguracaoSistema::where('chave', 'logomarca_estadual')->first();
        
        // Remove logomarca se solicitado
        if ($request->has('remover_logomarca_estadual') && $config && $config->valor) {
            Storage::disk('public')->delete('sistema/logomarcas/' . basename($config->valor));
            $config->update(['valor' => null]);
            
            return redirect()
                ->route('admin.configuracoes.sistema.index')
                ->with('success', 'Logomarca estadual removida com sucesso!');
        }

        // Upload da nova logomarca
        if ($request->hasFile('logomarca_estadual')) {
            // Remove logomarca antiga se existir
            if ($config && $config->valor) {
                Storage::disk('public')->delete('sistema/logomarcas/' . basename($config->valor));
            }
            
            $arquivo = $request->file('logomarca_estadual');
            $nomeArquivo = 'logomarca_estado_tocantins_' . time() . '.' . $arquivo->getClientOriginalExtension();
            
            // Usa o disco 'public' que aponta para storage/app/public
            $caminho = $arquivo->storeAs('sistema/logomarcas', $nomeArquivo, 'public');
            
            if (!$caminho) {
                return redirect()
                    ->route('admin.configuracoes.sistema.index')
                    ->with('error', 'Erro ao salvar o arquivo. Verifique as permissões.');
            }
            
            $caminhoPublico = 'storage/' . $caminho;
            
            ConfiguracaoSistema::definir(
                'logomarca_estadual',
                $caminhoPublico,
                'imagem',
                'Logomarca do Estado do Tocantins (usada em documentos de usuários estaduais)'
            );
            
            return redirect()
                ->route('admin.configuracoes.sistema.index')
                ->with('success', 'Logomarca estadual atualizada com sucesso!');
        }
        
        // Se atualizou apenas IA, retorna com sucesso
        if ($atualizouIA) {
            return redirect()
                ->route('admin.configuracoes.sistema.index')
                ->with('success', 'Configurações do Assistente de IA atualizadas com sucesso!');
        }

        return redirect()
            ->route('admin.configuracoes.sistema.index')
            ->with('info', 'Nenhuma alteração foi realizada.');
    }
}
