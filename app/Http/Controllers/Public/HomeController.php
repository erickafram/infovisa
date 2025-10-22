<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Exibe a página inicial pública
     */
    public function index()
    {
        return view('public.home');
    }

    /**
     * Consulta processo por CNPJ
     */
    public function consultarProcesso(Request $request)
    {
        $request->validate([
            'cnpj' => 'required|string|min:14|max:18',
        ]);

        // TODO: Implementar lógica de consulta
        return redirect()->back()->with('success', 'Consulta realizada com sucesso!');
    }

    /**
     * Verifica autenticidade de documento
     */
    public function verificarDocumento(Request $request)
    {
        $request->validate([
            'codigo_verificador' => 'required|string|min:6',
        ]);

        // TODO: Implementar lógica de verificação
        return redirect()->back()->with('success', 'Documento verificado com sucesso!');
    }
}

