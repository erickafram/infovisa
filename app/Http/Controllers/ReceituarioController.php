<?php

namespace App\Http\Controllers;

use App\Models\Receituario;
use App\Models\Municipio;
use App\Models\Processo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Barryvdh\DomPDF\Facade\Pdf;

class ReceituarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Receituario::with(['municipio', 'processo']);
        
        // Filtros
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('busca')) {
            $busca = $request->busca;
            $query->where(function($q) use ($busca) {
                $q->where('nome', 'ILIKE', "%{$busca}%")
                  ->orWhere('razao_social', 'ILIKE', "%{$busca}%")
                  ->orWhere('cpf', 'LIKE', "%{$busca}%")
                  ->orWhere('cnpj', 'LIKE', "%{$busca}%");
            });
        }
        
        $receituarios = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return view('receituarios.index', compact('receituarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $tipo = $request->get('tipo', 'medico');
        $municipios = Municipio::orderBy('nome')->get();
        
        return view('receituarios.create-wizard', compact('tipo', 'municipios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rules = [
            'tipo' => 'required|in:medico,instituicao,secretaria,talidomida',
        ];
        
        // Regras específicas por tipo
        if (in_array($request->tipo, ['medico', 'talidomida'])) {
            $rules['nome'] = 'required|string|max:255';
            $rules['cpf'] = 'required|string|max:14';
            $rules['especialidade'] = 'nullable|string|max:255';
            $rules['telefone'] = 'required|string|max:20';
            $rules['numero_conselho_classe'] = 'nullable|string|max:50';
            $rules['endereco'] = 'nullable|string|max:255';
            $rules['cep'] = 'nullable|string|max:10';
            $rules['municipio_id'] = 'nullable|exists:municipios,id';
        }
        
        if (in_array($request->tipo, ['instituicao', 'secretaria'])) {
            $rules['razao_social'] = 'required|string|max:255';
            $rules['cnpj'] = 'required|string|max:18';
            $rules['municipio_id'] = 'nullable|exists:municipios,id';
            $rules['endereco'] = 'nullable|string|max:255';
            $rules['cep'] = 'nullable|string|max:10';
            $rules['telefone'] = 'nullable|string|max:20';
            $rules['email'] = 'nullable|email|max:255';
        }
        
        if ($request->tipo === 'instituicao') {
            $rules['responsavel_nome'] = 'nullable|string|max:255';
            $rules['responsavel_cpf'] = 'nullable|string|max:14';
            $rules['responsavel_crm'] = 'nullable|string|max:50';
        }
        
        $validated = $request->validate($rules);
        
        // Processa locais de trabalho
        if ($request->has('locais_trabalho')) {
            $validated['locais_trabalho'] = array_filter($request->locais_trabalho, function($local) {
                return !empty($local['nome']);
            });
        }
        
        $validated['usuario_criacao_id'] = Auth::guard('interno')->id();
        $validated['status'] = 'pendente';
        
        $receituario = Receituario::create($validated);
        
        // Redireciona para página com PDF e instruções de assinatura
        return redirect()
            ->route('admin.receituarios.pdf-gerado', $receituario->id)
            ->with('success', 'Receituário cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $receituario = Receituario::with(['municipio', 'processo', 'usuarioCriacao'])->findOrFail($id);
        
        return view('receituarios.show', compact('receituario'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $receituario = Receituario::findOrFail($id);
        $municipios = Municipio::orderBy('nome')->get();
        
        return view('receituarios.edit', compact('receituario', 'municipios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $receituario = Receituario::findOrFail($id);
        
        $rules = [
            'status' => 'nullable|in:ativo,inativo,pendente',
            'observacoes' => 'nullable|string',
        ];
        
        // Mesmas regras do store baseadas no tipo
        if (in_array($receituario->tipo, ['medico', 'talidomida'])) {
            $rules['nome'] = 'required|string|max:255';
            $rules['cpf'] = 'required|string|max:14';
            $rules['telefone'] = 'required|string|max:20';
        }
        
        if (in_array($receituario->tipo, ['instituicao', 'secretaria'])) {
            $rules['razao_social'] = 'required|string|max:255';
            $rules['cnpj'] = 'required|string|max:18';
        }
        
        $validated = $request->validate($rules);
        
        if ($request->has('locais_trabalho')) {
            $validated['locais_trabalho'] = array_filter($request->locais_trabalho, function($local) {
                return !empty($local['nome']);
            });
        }
        
        $validated['usuario_atualizacao_id'] = Auth::guard('interno')->id();
        
        $receituario->update($validated);
        
        return redirect()
            ->route('admin.receituarios.show', $receituario->id)
            ->with('success', 'Receituário atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $receituario = Receituario::findOrFail($id);
        $receituario->delete();
        
        return redirect()
            ->route('admin.receituarios.index')
            ->with('success', 'Receituário excluído com sucesso!');
    }
    
    /**
     * Busca CNPJ na API da Receita Federal
     */
    public function buscarCnpj(Request $request)
    {
        $cnpj = preg_replace('/[^0-9]/', '', $request->cnpj);
        
        try {
            $response = Http::timeout(10)->get("https://brasilapi.com.br/api/cnpj/v1/{$cnpj}");
            
            if ($response->successful()) {
                $data = $response->json();
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'razao_social' => $data['razao_social'] ?? null,
                        'nome_fantasia' => $data['nome_fantasia'] ?? null,
                        'cnpj' => $data['cnpj'] ?? null,
                        'municipio' => $data['municipio'] ?? null,
                        'uf' => $data['uf'] ?? null,
                        'cep' => $data['cep'] ?? null,
                        'logradouro' => $data['logradouro'] ?? null,
                        'numero' => $data['numero'] ?? null,
                        'complemento' => $data['complemento'] ?? null,
                        'bairro' => $data['bairro'] ?? null,
                        'telefone' => $data['ddd_telefone_1'] ?? null,
                        'email' => $data['email'] ?? null,
                    ]
                ]);
            }
            
            return response()->json(['success' => false, 'message' => 'CNPJ não encontrado'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao buscar CNPJ'], 500);
        }
    }
    
    /**
     * Criar processo de receituário
     */
    public function criarProcesso($id)
    {
        $receituario = Receituario::findOrFail($id);
        
        if ($receituario->processo_id) {
            return redirect()
                ->back()
                ->with('error', 'Este receituário já possui um processo vinculado.');
        }
        
        // Aqui você pode implementar a lógica de criação do processo
        // Por enquanto, vou deixar como placeholder
        
        return redirect()
            ->back()
            ->with('info', 'Funcionalidade de criar processo será implementada em breve.');
    }

    /**
     * Mostra a página com o PDF gerado e instruções de assinatura
     */
    public function pdfGerado($id)
    {
        $receituario = Receituario::with(['municipio'])->findOrFail($id);
        
        return view('receituarios.pdf-gerado', compact('receituario'));
    }

    /**
     * Gera o PDF do receituário para assinatura
     */
    public function gerarPdf($id)
    {
        $receituario = Receituario::with(['municipio'])->findOrFail($id);
        
        // Define o template baseado no tipo
        $templates = [
            'medico' => 'receituarios.pdf.medico',
            'instituicao' => 'receituarios.pdf.instituicao',
            'secretaria' => 'receituarios.pdf.secretaria',
            'talidomida' => 'receituarios.pdf.talidomida',
        ];
        
        $template = $templates[$receituario->tipo] ?? 'receituarios.pdf.medico';
        
        // Gera o PDF
        $pdf = Pdf::loadView($template, ['receituario' => $receituario])
            ->setPaper('a4', 'portrait')
            ->setOption('margin-top', 10)
            ->setOption('margin-bottom', 10)
            ->setOption('margin-left', 10)
            ->setOption('margin-right', 10);
        
        // Nome do arquivo
        $nomeArquivo = 'receituario_' . $receituario->tipo . '_' . $receituario->id . '.pdf';
        
        // Retorna o PDF para visualização no navegador
        return $pdf->stream($nomeArquivo);
    }
}
