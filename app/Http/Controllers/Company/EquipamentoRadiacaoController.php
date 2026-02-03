<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Estabelecimento;
use App\Models\EquipamentoRadiacao;
use App\Models\AtividadeEquipamentoRadiacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EquipamentoRadiacaoController extends Controller
{
    /**
     * Verifica se o usuário tem acesso de gestor ao estabelecimento.
     * Se não tiver, redireciona com mensagem de erro.
     * 
     * @param Estabelecimento $estabelecimento
     * @return \Illuminate\Http\RedirectResponse|null
     */
    private function verificarAcessoGestor(Estabelecimento $estabelecimento)
    {
        if ($estabelecimento->usuarioEhVisualizador()) {
            return redirect()->route('company.estabelecimentos.show', $estabelecimento->id)
                ->with('error', 'Acesso restrito: sua conta possui permissão apenas para visualização. Entre em contato com o responsável do estabelecimento para solicitar permissões de edição.');
        }
        return null;
    }

    /**
     * Lista equipamentos de radiação do estabelecimento
     */
    public function index($estabelecimentoId)
    {
        $user = Auth::guard('externo')->user();
        
        $estabelecimento = Estabelecimento::where('id', $estabelecimentoId)
            ->where(function($query) use ($user) {
                $query->where('usuario_externo_id', $user->id)
                      ->orWhereHas('usuariosVinculados', function($q) use ($user) {
                          $q->where('usuario_externo_id', $user->id);
                      });
            })
            ->firstOrFail();

        // Verifica se o estabelecimento precisa cadastrar equipamentos
        $exigeEquipamentos = AtividadeEquipamentoRadiacao::estabelecimentoExigeEquipamentos($estabelecimento);
        
        if (!$exigeEquipamentos) {
            return redirect()->route('company.estabelecimentos.show', $estabelecimento->id)
                ->with('info', 'Este estabelecimento não possui atividades que exigem cadastro de equipamentos de imagem.');
        }

        $equipamentos = EquipamentoRadiacao::where('estabelecimento_id', $estabelecimento->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $tiposEquipamento = EquipamentoRadiacao::getTiposEquipamento();
        $statusOptions = EquipamentoRadiacao::getStatusOptions();

        return view('company.estabelecimentos.equipamentos-radiacao.index', compact(
            'estabelecimento',
            'equipamentos',
            'tiposEquipamento',
            'statusOptions'
        ));
    }

    /**
     * Armazena um novo equipamento
     */
    public function store(Request $request, $estabelecimentoId)
    {
        $user = Auth::guard('externo')->user();
        
        $estabelecimento = Estabelecimento::where('id', $estabelecimentoId)
            ->where(function($query) use ($user) {
                $query->where('usuario_externo_id', $user->id)
                      ->orWhereHas('usuariosVinculados', function($q) use ($user) {
                          $q->where('usuario_externo_id', $user->id);
                      });
            })
            ->firstOrFail();

        // Verifica se o usuário tem permissão de edição
        if ($redirect = $this->verificarAcessoGestor($estabelecimento)) {
            return $redirect;
        }

        // Verifica se o estabelecimento precisa cadastrar equipamentos
        $exigeEquipamentos = AtividadeEquipamentoRadiacao::estabelecimentoExigeEquipamentos($estabelecimento);
        
        if (!$exigeEquipamentos) {
            return back()->with('error', 'Este estabelecimento não possui atividades que exigem cadastro de equipamentos de imagem.');
        }

        $validated = $request->validate([
            'tipo_equipamento' => 'required|string|max:255',
            'fabricante' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'registro_anvisa' => 'required|string|max:255',
            'numero_serie' => 'nullable|string|max:255',
            'ano_fabricacao' => 'nullable|integer|min:1950|max:' . date('Y'),
            'setor_localizacao' => 'nullable|string|max:255',
            'observacoes' => 'nullable|string|max:1000',
        ], [
            'tipo_equipamento.required' => 'O tipo de equipamento é obrigatório.',
            'fabricante.required' => 'A marca/fabricante é obrigatória.',
            'modelo.required' => 'O modelo é obrigatório.',
            'registro_anvisa.required' => 'O número do registro MS/ANVISA é obrigatório.',
        ]);

        $validated['estabelecimento_id'] = $estabelecimento->id;
        $validated['usuario_externo_id'] = $user->id;
        $validated['status'] = EquipamentoRadiacao::STATUS_ATIVO;

        EquipamentoRadiacao::create($validated);

        return back()->with('success', 'Equipamento cadastrado com sucesso!');
    }

    /**
     * Atualiza um equipamento
     */
    public function update(Request $request, $estabelecimentoId, $equipamentoId)
    {
        $user = Auth::guard('externo')->user();
        
        $estabelecimento = Estabelecimento::where('id', $estabelecimentoId)
            ->where(function($query) use ($user) {
                $query->where('usuario_externo_id', $user->id)
                      ->orWhereHas('usuariosVinculados', function($q) use ($user) {
                          $q->where('usuario_externo_id', $user->id);
                      });
            })
            ->firstOrFail();

        // Verifica se o usuário tem permissão de edição
        if ($redirect = $this->verificarAcessoGestor($estabelecimento)) {
            return $redirect;
        }

        $equipamento = EquipamentoRadiacao::where('id', $equipamentoId)
            ->where('estabelecimento_id', $estabelecimento->id)
            ->firstOrFail();

        $validated = $request->validate([
            'tipo_equipamento' => 'required|string|max:255',
            'fabricante' => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'registro_anvisa' => 'required|string|max:255',
            'numero_serie' => 'nullable|string|max:255',
            'ano_fabricacao' => 'nullable|integer|min:1950|max:' . date('Y'),
            'setor_localizacao' => 'nullable|string|max:255',
            'status' => 'required|string|in:' . implode(',', array_keys(EquipamentoRadiacao::getStatusOptions())),
            'observacoes' => 'nullable|string|max:1000',
        ]);

        $equipamento->update($validated);

        return back()->with('success', 'Equipamento atualizado com sucesso!');
    }

    /**
     * Atualiza apenas o status de um equipamento
     */
    public function updateStatus(Request $request, $estabelecimentoId, $equipamentoId)
    {
        $user = Auth::guard('externo')->user();
        
        $estabelecimento = Estabelecimento::where('id', $estabelecimentoId)
            ->where(function($query) use ($user) {
                $query->where('usuario_externo_id', $user->id)
                      ->orWhereHas('usuariosVinculados', function($q) use ($user) {
                          $q->where('usuario_externo_id', $user->id);
                      });
            })
            ->firstOrFail();

        // Verifica se o usuário tem permissão de edição
        if ($redirect = $this->verificarAcessoGestor($estabelecimento)) {
            return $redirect;
        }

        $equipamento = EquipamentoRadiacao::where('id', $equipamentoId)
            ->where('estabelecimento_id', $estabelecimento->id)
            ->firstOrFail();

        $validated = $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(EquipamentoRadiacao::getStatusOptions())),
        ]);

        $statusLabels = EquipamentoRadiacao::getStatusOptions();
        $statusAnterior = $statusLabels[$equipamento->status] ?? $equipamento->status;
        $statusNovo = $statusLabels[$validated['status']] ?? $validated['status'];

        $equipamento->update($validated);

        return back()->with('success', "Status do equipamento alterado de '{$statusAnterior}' para '{$statusNovo}'.");
    }

    /**
     * Remove um equipamento
     */
    public function destroy($estabelecimentoId, $equipamentoId)
    {
        $user = Auth::guard('externo')->user();
        
        $estabelecimento = Estabelecimento::where('id', $estabelecimentoId)
            ->where(function($query) use ($user) {
                $query->where('usuario_externo_id', $user->id)
                      ->orWhereHas('usuariosVinculados', function($q) use ($user) {
                          $q->where('usuario_externo_id', $user->id);
                      });
            })
            ->firstOrFail();

        // Verifica se o usuário tem permissão de edição
        if ($redirect = $this->verificarAcessoGestor($estabelecimento)) {
            return $redirect;
        }

        $equipamento = EquipamentoRadiacao::where('id', $equipamentoId)
            ->where('estabelecimento_id', $estabelecimento->id)
            ->firstOrFail();

        $equipamento->delete();

        return back()->with('success', 'Equipamento removido com sucesso!');
    }

    /**
     * Registra declaração de que o estabelecimento não possui equipamentos de imagem
     */
    public function declararSemEquipamentos(Request $request, $estabelecimentoId)
    {
        $user = Auth::guard('externo')->user();
        
        $estabelecimento = Estabelecimento::where('id', $estabelecimentoId)
            ->where(function($query) use ($user) {
                $query->where('usuario_externo_id', $user->id)
                      ->orWhereHas('usuariosVinculados', function($q) use ($user) {
                          $q->where('usuario_externo_id', $user->id);
                      });
            })
            ->firstOrFail();

        // Verifica se o usuário tem permissão de edição
        if ($redirect = $this->verificarAcessoGestor($estabelecimento)) {
            return $redirect;
        }

        // Verifica se o estabelecimento precisa cadastrar equipamentos
        $exigeEquipamentos = AtividadeEquipamentoRadiacao::estabelecimentoExigeEquipamentos($estabelecimento);
        
        if (!$exigeEquipamentos) {
            return back()->with('error', 'Este estabelecimento não possui atividades que exigem cadastro de equipamentos de imagem.');
        }

        $validated = $request->validate([
            'justificativa' => 'nullable|string|min:30|max:1000',
            'confirmacao' => 'required|accepted',
            'opcao_1' => 'nullable',
            'opcao_2' => 'nullable',
            'opcao_3' => 'nullable',
        ], [
            'justificativa.min' => 'A justificativa deve ter pelo menos 30 caracteres.',
            'confirmacao.required' => 'Você precisa confirmar a declaração.',
            'confirmacao.accepted' => 'Você precisa confirmar a declaração.',
        ]);

        // Salva as opções marcadas
        $opcoesMarcadas = [];
        if ($request->has('opcao_1')) $opcoesMarcadas[] = 'opcao_1';
        if ($request->has('opcao_2')) $opcoesMarcadas[] = 'opcao_2';
        if ($request->has('opcao_3')) $opcoesMarcadas[] = 'opcao_3';

        $estabelecimento->update([
            'declaracao_sem_equipamentos_imagem' => true,
            'declaracao_sem_equipamentos_imagem_data' => now(),
            'declaracao_sem_equipamentos_imagem_justificativa' => $validated['justificativa'],
            'declaracao_sem_equipamentos_imagem_usuario_id' => $user->id,
            'declaracao_sem_equipamentos_opcoes' => json_encode($opcoesMarcadas),
        ]);

        return back()->with('success', 'Declaração registrada com sucesso! O sistema reconhece que este estabelecimento não possui equipamentos de imagem.');
    }

    /**
     * Revoga declaração de que o estabelecimento não possui equipamentos de imagem
     */
    public function revogarDeclaracao($estabelecimentoId)
    {
        $user = Auth::guard('externo')->user();
        
        $estabelecimento = Estabelecimento::where('id', $estabelecimentoId)
            ->where(function($query) use ($user) {
                $query->where('usuario_externo_id', $user->id)
                      ->orWhereHas('usuariosVinculados', function($q) use ($user) {
                          $q->where('usuario_externo_id', $user->id);
                      });
            })
            ->firstOrFail();

        // Verifica se o usuário tem permissão de edição
        if ($redirect = $this->verificarAcessoGestor($estabelecimento)) {
            return $redirect;
        }

        $estabelecimento->update([
            'declaracao_sem_equipamentos_imagem' => false,
            'declaracao_sem_equipamentos_imagem_data' => null,
            'declaracao_sem_equipamentos_imagem_justificativa' => null,
            'declaracao_sem_equipamentos_imagem_usuario_id' => null,
            'declaracao_sem_equipamentos_opcoes' => null,
        ]);

        return redirect()->route('company.estabelecimentos.equipamentos-radiacao.index', $estabelecimentoId)
            ->with('success', 'Declaração revogada com sucesso! Você pode agora cadastrar equipamentos ou fazer uma nova declaração.');
    }
}
