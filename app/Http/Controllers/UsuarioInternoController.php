<?php

namespace App\Http\Controllers;

use App\Models\UsuarioInterno;
use App\Enums\NivelAcesso;
use Illuminate\Http\Request;

class UsuarioInternoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = UsuarioInterno::query();

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

        return view('admin.usuarios-internos.index', compact('usuarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $municipios = \App\Models\Municipio::orderBy('nome')->get();
        $tipoSetores = \App\Models\TipoSetor::where('ativo', true)->orderBy('nome')->get();
        return view('admin.usuarios-internos.create', compact('municipios', 'tipoSetores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
            'matricula' => 'nullable|string|max:50',
            'cargo' => 'nullable|string|max:100',
            'setor' => 'nullable|string|max:100',
            'nivel_acesso' => 'required|string',
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
        ]);

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
        $usuarioInterno->load('municipioRelacionado');
        return view('admin.usuarios-internos.show', compact('usuarioInterno'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UsuarioInterno $usuarioInterno)
    {
        $municipios = \App\Models\Municipio::orderBy('nome')->get();
        $tipoSetores = \App\Models\TipoSetor::where('ativo', true)->orderBy('nome')->get();
        return view('admin.usuarios-internos.edit', compact('usuarioInterno', 'municipios', 'tipoSetores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UsuarioInterno $usuarioInterno)
    {
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
            'matricula' => 'nullable|string|max:50',
            'cargo' => 'nullable|string|max:100',
            'setor' => 'nullable|string|max:100',
            'nivel_acesso' => 'required|string',
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
        ]);

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
        $usuarioInterno->delete();

        return redirect()->route('admin.usuarios-internos.index')
            ->with('success', 'Usuário interno excluído com sucesso!');
    }
}
