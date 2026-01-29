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
        $iaApiKey = ConfiguracaoSistema::where('chave', 'ia_api_key')->first();
        $iaApiUrl = ConfiguracaoSistema::where('chave', 'ia_api_url')->first();
        $iaModel = ConfiguracaoSistema::where('chave', 'ia_model')->first();
        $iaBuscaWeb = ConfiguracaoSistema::where('chave', 'ia_busca_web')->first();
        
        return view('admin.configuracoes.sistema.index', compact(
            'logomarcaEstadual',
            'iaAtiva',
            'iaApiKey',
            'iaApiUrl',
            'iaModel',
            'iaBuscaWeb'
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
            'ia_api_key' => 'nullable|string',
            'ia_api_url' => 'nullable|url',
            'ia_model' => 'nullable|string',
        ], [
            'logomarca_estadual.image' => 'O arquivo deve ser uma imagem',
            'logomarca_estadual.mimes' => 'A logomarca deve ser um arquivo: jpeg, png, jpg ou svg',
            'logomarca_estadual.max' => 'A logomarca não pode ser maior que 2MB',
            'ia_api_url.url' => 'A URL da API deve ser válida',
        ]);
        
        // Atualiza configurações da IA
        // Checkbox desmarcado não envia valor, então sempre atualiza
        ConfiguracaoSistema::updateOrCreate(
            ['chave' => 'ia_ativa'],
            ['valor' => $request->has('ia_ativa') ? 'true' : 'false']
        );
        
        if ($request->filled('ia_api_key')) {
            ConfiguracaoSistema::where('chave', 'ia_api_key')
                ->update(['valor' => $request->ia_api_key]);
        }
        
        if ($request->filled('ia_api_url')) {
            ConfiguracaoSistema::where('chave', 'ia_api_url')
                ->update(['valor' => $request->ia_api_url]);
        }
        
        if ($request->filled('ia_model')) {
            ConfiguracaoSistema::where('chave', 'ia_model')
                ->update(['valor' => $request->ia_model]);
        }
        
        // Busca na web
        ConfiguracaoSistema::where('chave', 'ia_busca_web')
            ->update(['valor' => $request->has('ia_busca_web') ? 'true' : 'false']);
        
        // Verifica se foi apenas atualização de IA (sem logomarca)
        $atualizouIA = $request->has('ia_ativa') || 
                       $request->filled('ia_api_key') || 
                       $request->filled('ia_api_url') || 
                       $request->filled('ia_model') ||
                       $request->has('ia_busca_web');

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
