<?php

namespace App\Http\Controllers;

use App\Models\UsuarioInterno;
use App\Enums\NivelAcesso;
use Illuminate\Http\Request;

class UsuarioInternoController extends Controller
{
    /**
     * Retorna os níveis de acesso permitidos para o usuário logado criar/gerenciar
     */
    private function getNiveisPermitidos(): array
    {
        $usuarioLogado = auth('interno')->user();
        
        if ($usuarioLogado->isAdmin()) {
            // Admin pode criar todos os níveis
            return NivelAcesso::cases();
        }
        
        if ($usuarioLogado->nivel_acesso === NivelAcesso::GestorEstadual) {
            // Gestor Estadual pode criar: Gestor Estadual e Técnico Estadual
            return [
                NivelAcesso::GestorEstadual,
                NivelAcesso::TecnicoEstadual,
            ];
        }
        
        if ($usuarioLogado->nivel_acesso === NivelAcesso::GestorMunicipal) {
            // Gestor Municipal pode criar: Gestor Municipal e Técnico Municipal
            return [
                NivelAcesso::GestorMunicipal,
                NivelAcesso::TecnicoMunicipal,
            ];
        }
        
        // Outros níveis não podem criar usuários
        return [];
    }

    /**
     * Verifica se o usuário logado pode gerenciar usuários internos
     */
    private function podeGerenciarUsuarios(): bool
    {
        $usuarioLogado = auth('interno')->user();
        return $usuarioLogado->isAdmin() || $usuarioLogado->isGestor();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Verifica permissão
        if (!$this->podeGerenciarUsuarios()) {
            abort(403, 'Você não tem permissão para acessar esta área.');
        }

        $usuarioLogado = auth('interno')->user();
        $query = UsuarioInterno::query();

        // Filtro por escopo do usuário logado
        if (!$usuarioLogado->isAdmin()) {
            $niveisPermitidos = array_map(fn($n) => $n->value, $this->getNiveisPermitidos());
            $query->whereIn('nivel_acesso', $niveisPermitidos);
            
            // Gestor Municipal só vê usuários do seu município
            if ($usuarioLogado->nivel_acesso === NivelAcesso::GestorMunicipal && $usuarioLogado->municipio_id) {
                $query->where('municipio_id', $usuarioLogado->municipio_id);
            }
        }

        // Filtro por nome
        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . $request->nome . '%');
        }

        // Filtro por CPF
        if ($request->filled('cpf')) {
            $cpf = preg_replace('/\D/', '', $request->cpf);
            $query->where('cpf', 'like', '%' . $cpf . '%');
        }

        // Filtro por email
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }

        // Filtro por município
        if ($request->filled('municipio')) {
            $query->where('municipio', 'like', '%' . $request->municipio . '%');
        }

        // Filtro por nível de acesso
        if ($request->filled('nivel_acesso')) {
            $query->where('nivel_acesso', $request->nivel_acesso);
        }

        // Filtro por status
        if ($request->filled('ativo')) {
            $query->where('ativo', $request->ativo === '1');
        }

        // Ordenação
        $sortField = $request->get('sort', 'nome');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        // Paginação com relacionamento
        $usuarios = $query->with('municipioRelacionado')->paginate(15)->withQueryString();
        
        // Níveis permitidos para filtro
        $niveisPermitidos = $this->getNiveisPermitidos();

        return view('admin.usuarios-internos.index', compact('usuarios', 'niveisPermitidos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Verifica permissão
        if (!$this->podeGerenciarUsuarios()) {
            abort(403, 'Você não tem permissão para criar usuários.');
        }

        $usuarioLogado = auth('interno')->user();
        $municipios = \App\Models\Municipio::orderBy('nome')->get();
        
        // Tenta buscar os tipos de setor, retorna coleção vazia se a tabela não existir
        try {
            $tipoSetores = \App\Models\TipoSetor::where('ativo', true)->orderBy('nome')->get();
        } catch (\Exception $e) {
            $tipoSetores = collect([]);
        }
        
        // Níveis de acesso permitidos para o usuário logado
        $niveisPermitidos = $this->getNiveisPermitidos();
        
        // Se for Gestor Municipal, pré-seleciona o município
        $municipioPreSelecionado = null;
        if ($usuarioLogado->nivel_acesso === NivelAcesso::GestorMunicipal && $usuarioLogado->municipio_id) {
            $municipioPreSelecionado = $usuarioLogado->municipio_id;
        }
        
        return view('admin.usuarios-internos.create', compact('municipios', 'tipoSetores', 'niveisPermitidos', 'municipioPreSelecionado'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Verifica permissão
        if (!$this->podeGerenciarUsuarios()) {
            abort(403, 'Você não tem permissão para criar usuários.');
        }

        $usuarioLogado = auth('interno')->user();
        $niveisPermitidos = array_map(fn($n) => $n->value, $this->getNiveisPermitidos());

        // Remove máscara do CPF e telefone antes de validar
        $request->merge([
            'cpf' => preg_replace('/[^0-9]/', '', $request->cpf),
            'telefone' => $request->telefone ? preg_replace('/[^0-9]/', '', $request->telefone) : null,
        ]);

        $rules = [
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|size:11|unique:usuarios_internos,cpf',
            'email' => 'required|email|unique:usuarios_internos,email',
            'telefone' => 'nullable|string|max:11',
            'data_nascimento' => 'nullable|date|before_or_equal:today',
            'matricula' => 'nullable|string|max:50',
            'cargo' => 'nullable|string|max:100',
            'setor' => 'nullable|string|max:100',
            'nivel_acesso' => ['required', 'string', 'in:' . implode(',', $niveisPermitidos)],
            'password' => 'required|string|min:8|confirmed',
            'ativo' => 'boolean',
        ];

        // Município é obrigatório para perfis municipais
        if (in_array($request->nivel_acesso, ['gestor_municipal', 'tecnico_municipal'])) {
            $rules['municipio_id'] = 'required|exists:municipios,id';
        } else {
            $rules['municipio_id'] = 'nullable|exists:municipios,id';
        }

        $validated = $request->validate($rules, [
            'cpf.size' => 'O CPF deve ter exatamente 11 dígitos',
            'cpf.unique' => 'Este CPF já está cadastrado',
            'telefone.max' => 'O telefone deve ter no máximo 11 dígitos',
            'municipio_id.required' => 'O município é obrigatório para usuários municipais',
            'municipio_id.exists' => 'Município inválido',
            'nivel_acesso.in' => 'Você não tem permissão para criar usuários com este nível de acesso.',
        ]);

        // Se for Gestor Municipal, força o município do usuário logado
        if ($usuarioLogado->nivel_acesso === NivelAcesso::GestorMunicipal && $usuarioLogado->municipio_id) {
            $validated['municipio_id'] = $usuarioLogado->municipio_id;
        }

        $validated['password'] = bcrypt($validated['password']);
        $validated['ativo'] = $request->has('ativo');

        UsuarioInterno::create($validated);

        return redirect()->route('admin.usuarios-internos.index')
            ->with('success', 'Usuário interno criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(UsuarioInterno $usuarioInterno)
    {
        // Verifica permissão
        if (!$this->podeGerenciarUsuarios()) {
            abort(403, 'Você não tem permissão para visualizar usuários.');
        }

        // Verifica se pode visualizar este usuário específico
        $this->verificarPermissaoUsuario($usuarioInterno);

        $usuarioInterno->load('municipioRelacionado');
        return view('admin.usuarios-internos.show', compact('usuarioInterno'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UsuarioInterno $usuarioInterno)
    {
        // Verifica permissão
        if (!$this->podeGerenciarUsuarios()) {
            abort(403, 'Você não tem permissão para editar usuários.');
        }

        // Verifica se pode editar este usuário específico
        $this->verificarPermissaoUsuario($usuarioInterno);

        $municipios = \App\Models\Municipio::orderBy('nome')->get();
        
        // Tenta buscar os tipos de setor, retorna coleção vazia se a tabela não existir
        try {
            $tipoSetores = \App\Models\TipoSetor::where('ativo', true)->orderBy('nome')->get();
        } catch (\Exception $e) {
            $tipoSetores = collect([]);
        }
        
        // Níveis de acesso permitidos para o usuário logado
        $niveisPermitidos = $this->getNiveisPermitidos();
        
        return view('admin.usuarios-internos.edit', compact('usuarioInterno', 'municipios', 'tipoSetores', 'niveisPermitidos'));
    }

    /**
     * Verifica se o usuário logado pode gerenciar o usuário especificado
     */
    private function verificarPermissaoUsuario(UsuarioInterno $usuarioInterno): void
    {
        $usuarioLogado = auth('interno')->user();
        
        // Admin pode tudo
        if ($usuarioLogado->isAdmin()) {
            return;
        }
        
        $niveisPermitidos = array_map(fn($n) => $n->value, $this->getNiveisPermitidos());
        
        // Verifica se o nível do usuário está nos permitidos
        if (!in_array($usuarioInterno->nivel_acesso->value, $niveisPermitidos)) {
            abort(403, 'Você não tem permissão para gerenciar este usuário.');
        }
        
        // Gestor Municipal só pode gerenciar usuários do seu município
        if ($usuarioLogado->nivel_acesso === NivelAcesso::GestorMunicipal && $usuarioLogado->municipio_id) {
            if ($usuarioInterno->municipio_id !== $usuarioLogado->municipio_id) {
                abort(403, 'Você só pode gerenciar usuários do seu município.');
            }
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UsuarioInterno $usuarioInterno)
    {
        // Verifica permissão
        if (!$this->podeGerenciarUsuarios()) {
            abort(403, 'Você não tem permissão para editar usuários.');
        }

        // Verifica se pode editar este usuário específico
        $this->verificarPermissaoUsuario($usuarioInterno);

        $usuarioLogado = auth('interno')->user();
        $niveisPermitidos = array_map(fn($n) => $n->value, $this->getNiveisPermitidos());

        // Remove máscara do CPF e telefone antes de validar
        $request->merge([
            'cpf' => preg_replace('/[^0-9]/', '', $request->cpf),
            'telefone' => $request->telefone ? preg_replace('/[^0-9]/', '', $request->telefone) : null,
        ]);

        $rules = [
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|size:11|unique:usuarios_internos,cpf,' . $usuarioInterno->id,
            'email' => 'required|email|unique:usuarios_internos,email,' . $usuarioInterno->id,
            'telefone' => 'nullable|string|max:11',
            'data_nascimento' => 'nullable|date|before_or_equal:today',
            'matricula' => 'nullable|string|max:50',
            'cargo' => 'nullable|string|max:100',
            'setor' => 'nullable|string|max:100',
            'nivel_acesso' => ['required', 'string', 'in:' . implode(',', $niveisPermitidos)],
            'password' => 'nullable|string|min:8|confirmed',
            'ativo' => 'boolean',
        ];

        // Município é obrigatório para perfis municipais
        if (in_array($request->nivel_acesso, ['gestor_municipal', 'tecnico_municipal'])) {
            $rules['municipio_id'] = 'required|exists:municipios,id';
        } else {
            $rules['municipio_id'] = 'nullable|exists:municipios,id';
        }

        $validated = $request->validate($rules, [
            'cpf.size' => 'O CPF deve ter exatamente 11 dígitos',
            'cpf.unique' => 'Este CPF já está cadastrado',
            'telefone.max' => 'O telefone deve ter no máximo 11 dígitos',
            'municipio_id.required' => 'O município é obrigatório para usuários municipais',
            'municipio_id.exists' => 'Município inválido',
            'nivel_acesso.in' => 'Você não tem permissão para definir este nível de acesso.',
        ]);

        // Se for Gestor Municipal, força o município do usuário logado
        if ($usuarioLogado->nivel_acesso === NivelAcesso::GestorMunicipal && $usuarioLogado->municipio_id) {
            $validated['municipio_id'] = $usuarioLogado->municipio_id;
        }

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['ativo'] = $request->has('ativo');

        $usuarioInterno->update($validated);

        return redirect()->route('admin.usuarios-internos.index')
            ->with('success', 'Usuário interno atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UsuarioInterno $usuarioInterno)
    {
        // Verifica permissão
        if (!$this->podeGerenciarUsuarios()) {
            abort(403, 'Você não tem permissão para excluir usuários.');
        }

        // Verifica se pode excluir este usuário específico
        $this->verificarPermissaoUsuario($usuarioInterno);

        $usuarioInterno->delete();

        return redirect()->route('admin.usuarios-internos.index')
            ->with('success', 'Usuário interno excluído com sucesso!');
    }
}
