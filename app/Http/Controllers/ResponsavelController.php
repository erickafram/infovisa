<?php

namespace App\Http\Controllers;

use App\Models\Responsavel;
use App\Models\Estabelecimento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResponsavelController extends Controller
{
    /**
     * Exibe a página de gerenciamento de responsáveis do estabelecimento
     */
    public function index($estabelecimentoId)
    {
        $estabelecimento = Estabelecimento::with(['responsaveisLegais', 'responsaveisTecnicos'])->findOrFail($estabelecimentoId);
        
        return view('estabelecimentos.responsaveis.index', compact('estabelecimento'));
    }

    /**
     * Busca responsável por CPF (qualquer tipo)
     */
    public function buscarPorCpf(Request $request)
    {
        $cpf = preg_replace('/[^0-9]/', '', $request->cpf);
        $tipo = $request->tipo;
        
        // Primeiro busca pelo CPF e tipo específico
        $responsavel = Responsavel::where('cpf', $cpf)
                                   ->where('tipo', $tipo)
                                   ->first();
        
        // Se não encontrou, busca qualquer responsável com esse CPF
        if (!$responsavel) {
            $responsavel = Responsavel::where('cpf', $cpf)->first();
        }
        
        if ($responsavel) {
            return response()->json([
                'encontrado' => true,
                'responsavel' => $responsavel,
                'mesmo_tipo' => $responsavel->tipo === $tipo
            ]);
        }
        
        return response()->json([
            'encontrado' => false
        ]);
    }

    /**
     * Exibe formulário para cadastrar novo responsável
     */
    public function create($estabelecimentoId, $tipo)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        
        return view('estabelecimentos.responsaveis.create', compact('estabelecimento', 'tipo'));
    }

    /**
     * Salva novo responsável e vincula ao estabelecimento
     */
    public function store(Request $request, $estabelecimentoId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        
        $tipo = $request->tipo; // 'legal' ou 'tecnico'
        
        // Limpar CPF primeiro
        $cpfLimpo = preg_replace('/[^0-9]/', '', $request->cpf);
        
        // Verificar se já existe responsável com este CPF e tipo
        $responsavel = Responsavel::where('cpf', $cpfLimpo)
                                   ->where('tipo', $tipo)
                                   ->first();
        
        // Se o responsável existe, verificar se já está vinculado ANTES de processar
        if ($responsavel) {
            $jaVinculado = $estabelecimento->responsaveis()
                                           ->where('responsavel_id', $responsavel->id)
                                           ->where('tipo_vinculo', $tipo)
                                           ->exists();
            
            if ($jaVinculado) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Este responsável já está vinculado a este estabelecimento como Responsável ' . ($tipo === 'legal' ? 'Legal' : 'Técnico') . '!');
            }
        }
        
        // Validação completa sempre
        $rules = [
            'tipo' => 'required|in:legal,tecnico',
            'cpf' => 'required|string',
            'nome' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telefone' => 'required|string|max:20',
        ];
        
        // Validação específica para Responsável Legal (apenas se for novo)
        if ($tipo === 'legal' && !$responsavel) {
            $rules['tipo_documento'] = 'required|in:rg,cnh';
            $rules['documento_identificacao'] = 'required|file|mimes:pdf|max:5120'; // 5MB
        }
        
        // Validação específica para Responsável Técnico (apenas se for novo)
        if ($tipo === 'tecnico' && !$responsavel) {
            $rules['conselho'] = 'required|string|max:50';
            $rules['numero_registro_conselho'] = 'required|string|max:50';
            $rules['carteirinha_conselho'] = 'required|file|mimes:pdf,jpg,jpeg,png|max:5120'; // 5MB
        }
        
        $validated = $request->validate($rules);
        
        // Limpar CPF
        $validated['cpf'] = $cpfLimpo;
        
        if (!$responsavel) {
            // Upload de arquivos com nomes únicos
            if ($tipo === 'legal' && $request->hasFile('documento_identificacao')) {
                $file = $request->file('documento_identificacao');
                $cpfLimpo = preg_replace('/[^0-9]/', '', $validated['cpf']);
                $timestamp = time();
                $extensao = $file->getClientOriginalExtension();
                $nomeArquivo = "doc_legal_{$cpfLimpo}_{$timestamp}.{$extensao}";
                
                $validated['documento_identificacao'] = $file->storeAs(
                    'responsaveis/legal',
                    $nomeArquivo,
                    'public'
                );
            }
            
            if ($tipo === 'tecnico' && $request->hasFile('carteirinha_conselho')) {
                $file = $request->file('carteirinha_conselho');
                $cpfLimpo = preg_replace('/[^0-9]/', '', $validated['cpf']);
                $timestamp = time();
                $extensao = $file->getClientOriginalExtension();
                $nomeArquivo = "carteirinha_tecnico_{$cpfLimpo}_{$timestamp}.{$extensao}";
                
                $validated['carteirinha_conselho'] = $file->storeAs(
                    'responsaveis/tecnico',
                    $nomeArquivo,
                    'public'
                );
            }
            
            // Criar novo responsável
            $responsavel = Responsavel::create($validated);
        }
        
        // Vincular ao estabelecimento
        $estabelecimento->responsaveis()->attach($responsavel->id, [
            'tipo_vinculo' => $tipo,
            'ativo' => true
        ]);
        
        return redirect()
            ->route('admin.estabelecimentos.responsaveis.index', $estabelecimento->id)
            ->with('success', 'Responsável vinculado com sucesso!');
    }

    /**
     * Remove vínculo do responsável com o estabelecimento
     */
    public function destroy($estabelecimentoId, $responsavelId)
    {
        $estabelecimento = Estabelecimento::findOrFail($estabelecimentoId);
        $responsavel = Responsavel::findOrFail($responsavelId);
        
        // Verificar se é responsável legal e se é o último
        if ($responsavel->tipo === 'legal') {
            $totalLegais = $estabelecimento->responsaveisLegais()->count();
            
            if ($totalLegais <= 1) {
                return redirect()
                    ->back()
                    ->with('error', 'Não é possível remover o último responsável legal! O estabelecimento deve ter pelo menos um responsável legal.');
            }
        }
        
        $estabelecimento->responsaveis()->detach($responsavelId);
        
        return redirect()
            ->route('admin.estabelecimentos.responsaveis.index', $estabelecimento->id)
            ->with('success', 'Responsável removido com sucesso!');
    }
}
