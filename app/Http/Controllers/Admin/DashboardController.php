<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UsuarioExterno;
use App\Models\UsuarioInterno;
use App\Models\Estabelecimento;
use App\Models\Processo;
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

        return view('admin.dashboard', compact(
            'stats',
            'usuarios_externos_recentes',
            'usuarios_internos_recentes',
            'estabelecimentos_pendentes',
            'processos_acompanhados'
        ));
    }
}

