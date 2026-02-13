<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsappConfiguracao;
use App\Services\WhatsappService;
use Illuminate\Http\Request;

class WhatsappConfiguracaoController extends Controller
{
    /**
     * Página de configuração do WhatsApp
     */
    public function index()
    {
        $config = WhatsappConfiguracao::getOrCreate();
        return view('admin.whatsapp.configuracao', compact('config'));
    }

    /**
     * Salvar configurações do WhatsApp
     */
    public function salvar(Request $request)
    {
        $request->validate([
            'baileys_server_url' => 'required|url',
            'api_key' => 'nullable|string|max:255',
            'session_name' => 'required|string|max:100',
            'ativo' => 'boolean',
            'enviar_ao_assinar' => 'boolean',
            'mensagem_template' => 'required|string|min:10',
        ], [
            'baileys_server_url.required' => 'A URL do servidor Baileys é obrigatória.',
            'baileys_server_url.url' => 'Informe uma URL válida.',
            'session_name.required' => 'O nome da sessão é obrigatório.',
            'mensagem_template.required' => 'O template da mensagem é obrigatório.',
            'mensagem_template.min' => 'O template deve ter pelo menos 10 caracteres.',
        ]);

        $config = WhatsappConfiguracao::getOrCreate();

        $config->update([
            'baileys_server_url' => $request->baileys_server_url,
            'api_key' => $request->api_key,
            'session_name' => $request->session_name,
            'ativo' => $request->boolean('ativo'),
            'enviar_ao_assinar' => $request->boolean('enviar_ao_assinar'),
            'mensagem_template' => $request->mensagem_template,
            'configurado_por' => auth('interno')->id(),
        ]);

        return redirect()->route('admin.whatsapp.configuracao')
            ->with('success', 'Configurações do WhatsApp salvas com sucesso!');
    }

    /**
     * Restaurar template padrão da mensagem
     */
    public function restaurarTemplate()
    {
        $config = WhatsappConfiguracao::getOrCreate();
        $config->update([
            'mensagem_template' => WhatsappConfiguracao::getTemplatePadrao(),
        ]);

        return response()->json([
            'sucesso' => true,
            'template' => WhatsappConfiguracao::getTemplatePadrao(),
        ]);
    }

    /**
     * Verificar status da conexão com Baileys
     */
    public function verificarStatus()
    {
        $service = new WhatsappService();
        $resultado = $service->verificarStatus();

        return response()->json($resultado);
    }

    /**
     * Iniciar sessão do WhatsApp (gerar QR Code)
     */
    public function iniciarSessao()
    {
        $service = new WhatsappService();
        $resultado = $service->iniciarSessao();

        return response()->json($resultado);
    }

    /**
     * Encerrar sessão do WhatsApp
     */
    public function encerrarSessao()
    {
        $service = new WhatsappService();
        $resultado = $service->encerrarSessao();

        return response()->json($resultado);
    }

    /**
     * Enviar mensagem de teste
     */
    public function enviarTeste(Request $request)
    {
        $request->validate([
            'telefone' => 'required|string|min:10',
        ], [
            'telefone.required' => 'Informe o número de telefone para teste.',
            'telefone.min' => 'O telefone deve ter pelo menos 10 dígitos.',
        ]);

        $service = new WhatsappService();
        $resultado = $service->enviarMensagemTeste($request->telefone);

        return response()->json($resultado);
    }
}
