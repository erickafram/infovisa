<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsuarioExterno;
use App\Models\UsuarioInterno;
use App\Models\Estabelecimento;
use App\Models\Processo;
use App\Models\DocumentoAssinatura;
use App\Models\DocumentoDigital;
use App\Models\ProcessoDesignacao;
use App\Models\OrdemServico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard do administrador
     */
    public function index()
    {
        $stats = [
            'usuarios_externos' => UsuarioExterno::count(),
            'usuarios_externos_ativos' => UsuarioExterno::where('ativo', true)->count(),
            'usuarios_externos_pendentes' => UsuarioExterno::whereNull('email_verified_at')->count(),
            'usuarios_internos' => UsuarioInterno::count(),
            'usuarios_internos_ativos' => UsuarioInterno::where('ativo', true)->count(),
            'administradores' => UsuarioInterno::administradores()->count(),
            'estabelecimentos_pendentes' => Estabelecimento::pendentes()->doMunicipioUsuario()->count(),
        ];

        $usuarios_externos_recentes = UsuarioExterno::latest()
            ->take(5)
            ->get();

        $usuarios_internos_recentes = UsuarioInterno::latest()
            ->take(5)
            ->get();

        // Buscar os 5 últimos estabelecimentos pendentes
        $estabelecimentos_pendentes = Estabelecimento::pendentes()
            ->doMunicipioUsuario()
            ->with('usuarioExterno')
            ->latest()
            ->take(5)
            ->get();

        // Buscar processos que o usuário está acompanhando
        $processos_acompanhados = Processo::whereHas('acompanhamentos', function($query) {
                $query->where('usuario_interno_id', Auth::guard('interno')->id());
            })
            ->with(['estabelecimento', 'usuario'])
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        // Buscar documentos pendentes de assinatura do usuário (excluindo rascunhos)
        $documentos_pendentes_assinatura = DocumentoAssinatura::where('usuario_interno_id', Auth::guard('interno')->id())
            ->where('status', 'pendente')
            ->whereHas('documentoDigital', function($query) {
                $query->where('status', '!=', 'rascunho');
            })
            ->with(['documentoDigital.tipoDocumento', 'documentoDigital.processo'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $stats['documentos_pendentes_assinatura'] = DocumentoAssinatura::where('usuario_interno_id', Auth::guard('interno')->id())
            ->where('status', 'pendente')
            ->whereHas('documentoDigital', function($query) {
                $query->where('status', '!=', 'rascunho');
            })
            ->count();

        // Buscar documentos em rascunho que têm o usuário como assinante
        $documentos_rascunho_pendentes = DocumentoAssinatura::where('usuario_interno_id', Auth::guard('interno')->id())
            ->where('status', 'pendente')
            ->whereHas('documentoDigital', function($query) {
                $query->where('status', 'rascunho');
            })
            ->with(['documentoDigital.tipoDocumento', 'documentoDigital.processo'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $stats['documentos_rascunho_pendentes'] = DocumentoAssinatura::where('usuario_interno_id', Auth::guard('interno')->id())
            ->where('status', 'pendente')
            ->whereHas('documentoDigital', function($query) {
                $query->where('status', 'rascunho');
            })
            ->count();

        // Buscar processos designados DIRETAMENTE para o usuário (pendentes e em andamento)
        // Exclui designações apenas por setor
        $processos_designados = ProcessoDesignacao::where('usuario_designado_id', Auth::guard('interno')->id())
            ->whereIn('status', ['pendente', 'em_andamento'])
            ->with(['processo.estabelecimento', 'usuarioDesignador'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $stats['processos_designados_pendentes'] = ProcessoDesignacao::where('usuario_designado_id', Auth::guard('interno')->id())
            ->whereIn('status', ['pendente', 'em_andamento'])
            ->count();

        // Buscar Ordens de Serviço em andamento do usuário
        // Dashboard mostra APENAS OSs onde o usuário é técnico atribuído
        $usuario = Auth::guard('interno')->user();
        
        // Busca OSs onde o usuário está na lista de técnicos
        $todasOS = OrdemServico::with(['estabelecimento', 'municipio'])
            ->whereIn('status', ['aberta', 'em_andamento'])
            ->get();
        
        $ordens_servico_andamento = $todasOS
            ->filter(function($os) use ($usuario) {
                return $os->tecnicos_ids && in_array($usuario->id, $os->tecnicos_ids);
            })
            ->sortBy('data_fim')
            ->take(10);

        $stats['ordens_servico_andamento'] = $todasOS
            ->filter(function($os) use ($usuario) {
                return $os->tecnicos_ids && in_array($usuario->id, $os->tecnicos_ids);
            })
            ->count();

        // Buscar documentos assinados pelo usuário que vencem em até 5 dias
        $documentos_vencendo = DocumentoDigital::whereHas('assinaturas', function($query) {
                $query->where('usuario_interno_id', Auth::guard('interno')->id())
                      ->where('status', 'assinado');
            })
            ->whereNotNull('data_vencimento')
            ->where('data_vencimento', '>=', now()->startOfDay())
            ->where('data_vencimento', '<=', now()->addDays(5)->endOfDay())
            ->with(['tipoDocumento', 'processo'])
            ->orderBy('data_vencimento', 'asc')
            ->get();
            
        $stats['documentos_vencendo'] = $documentos_vencendo->count();

        return view('admin.dashboard', compact(
            'stats',
            'usuarios_externos_recentes',
            'usuarios_internos_recentes',
            'estabelecimentos_pendentes',
            'processos_acompanhados',
            'documentos_pendentes_assinatura',
            'documentos_rascunho_pendentes',
            'processos_designados',
            'ordens_servico_andamento',
            'documentos_vencendo'
        ));
    }
}
