<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfiguracaoController extends Controller
{
    /**
     * Exibe a página principal de configurações
     */
    public function index()
    {
        return view('configuracoes.index');
    }
}
