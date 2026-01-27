<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentoAjuda;
use App\Models\TipoProcesso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentoAjudaController extends Controller
{
    /**
     * Lista todos os documentos de ajuda
     */
    public function index()
    {
        $documentos = DocumentoAjuda::with('tiposProcesso')
            ->ordenado()
            ->paginate(20);

        return view('admin.configuracoes.documentos-ajuda.index', compact('documentos'));
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        $tiposProcesso = TipoProcesso::orderBy('nome')->get();
        
        return view('admin.configuracoes.documentos-ajuda.create', compact('tiposProcesso'));
    }

    /**
     * Salva um novo documento de ajuda
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
            'arquivo' => 'required|file|mimes:pdf|max:10240', // Max 10MB
            'tipos_processo' => 'required|array|min:1',
            'tipos_processo.*' => 'exists:tipo_processos,id',
            'ativo' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
        ], [
            'titulo.required' => 'O título é obrigatório.',
            'arquivo.required' => 'O arquivo PDF é obrigatório.',
            'arquivo.mimes' => 'O arquivo deve ser um PDF.',
            'arquivo.max' => 'O arquivo não pode ter mais de 10MB.',
            'tipos_processo.required' => 'Selecione pelo menos um tipo de processo.',
            'tipos_processo.min' => 'Selecione pelo menos um tipo de processo.',
        ]);

        // Upload do arquivo
        $arquivo = $request->file('arquivo');
        $nomeOriginal = $arquivo->getClientOriginalName();
        $tamanho = $arquivo->getSize();
        $caminho = $arquivo->store('documentos-ajuda', 'local');

        $documento = DocumentoAjuda::create([
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'arquivo' => $caminho,
            'nome_original' => $nomeOriginal,
            'tamanho' => $tamanho,
            'ativo' => $request->boolean('ativo', true),
            'ordem' => $request->ordem ?? 0,
        ]);

        // Vincula aos tipos de processo
        $documento->tiposProcesso()->sync($request->tipos_processo);

        return redirect()
            ->route('admin.configuracoes.documentos-ajuda.index')
            ->with('success', 'Documento de ajuda criado com sucesso!');
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        $documento = DocumentoAjuda::with('tiposProcesso')->findOrFail($id);
        $tiposProcesso = TipoProcesso::orderBy('nome')->get();
        
        return view('admin.configuracoes.documentos-ajuda.edit', compact('documento', 'tiposProcesso'));
    }

    /**
     * Atualiza um documento de ajuda
     */
    public function update(Request $request, $id)
    {
        $documento = DocumentoAjuda::findOrFail($id);

        $request->validate([
            'titulo' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
            'arquivo' => 'nullable|file|mimes:pdf|max:10240',
            'tipos_processo' => 'required|array|min:1',
            'tipos_processo.*' => 'exists:tipo_processos,id',
            'ativo' => 'boolean',
            'ordem' => 'nullable|integer|min:0',
        ], [
            'titulo.required' => 'O título é obrigatório.',
            'arquivo.mimes' => 'O arquivo deve ser um PDF.',
            'arquivo.max' => 'O arquivo não pode ter mais de 10MB.',
            'tipos_processo.required' => 'Selecione pelo menos um tipo de processo.',
            'tipos_processo.min' => 'Selecione pelo menos um tipo de processo.',
        ]);

        $dados = [
            'titulo' => $request->titulo,
            'descricao' => $request->descricao,
            'ativo' => $request->boolean('ativo', true),
            'ordem' => $request->ordem ?? 0,
        ];

        // Se enviou novo arquivo, substitui o antigo
        if ($request->hasFile('arquivo')) {
            // Remove arquivo antigo
            if ($documento->arquivo && Storage::disk('local')->exists($documento->arquivo)) {
                Storage::disk('local')->delete($documento->arquivo);
            }

            $arquivo = $request->file('arquivo');
            $dados['arquivo'] = $arquivo->store('documentos-ajuda', 'local');
            $dados['nome_original'] = $arquivo->getClientOriginalName();
            $dados['tamanho'] = $arquivo->getSize();
        }

        $documento->update($dados);

        // Atualiza vínculos com tipos de processo
        $documento->tiposProcesso()->sync($request->tipos_processo);

        return redirect()
            ->route('admin.configuracoes.documentos-ajuda.index')
            ->with('success', 'Documento de ajuda atualizado com sucesso!');
    }

    /**
     * Remove um documento de ajuda
     */
    public function destroy($id)
    {
        $documento = DocumentoAjuda::findOrFail($id);

        // Remove o arquivo
        if ($documento->arquivo && Storage::disk('local')->exists($documento->arquivo)) {
            Storage::disk('local')->delete($documento->arquivo);
        }

        $documento->delete();

        return redirect()
            ->route('admin.configuracoes.documentos-ajuda.index')
            ->with('success', 'Documento de ajuda excluído com sucesso!');
    }

    /**
     * Visualiza o PDF do documento
     */
    public function visualizar($id)
    {
        $documento = DocumentoAjuda::findOrFail($id);

        if (!Storage::disk('local')->exists($documento->arquivo)) {
            abort(404, 'Arquivo não encontrado.');
        }

        $caminho = Storage::disk('local')->path($documento->arquivo);
        
        return response()->file($caminho, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $documento->nome_original . '"',
        ]);
    }

    /**
     * Download do documento
     */
    public function download($id)
    {
        $documento = DocumentoAjuda::findOrFail($id);

        if (!Storage::disk('local')->exists($documento->arquivo)) {
            abort(404, 'Arquivo não encontrado.');
        }

        return Storage::disk('local')->download($documento->arquivo, $documento->nome_original);
    }
}
