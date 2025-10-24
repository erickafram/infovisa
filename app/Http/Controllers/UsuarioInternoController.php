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

        // Paginação
        $usuarios = $query->paginate(15)->withQueryString();

        return view('admin.usuarios-internos.index', compact('usuarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.usuarios-internos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|size:11|unique:usuarios_internos,cpf',
            'email' => 'required|email|unique:usuarios_internos,email',
            'telefone' => 'nullable|string|max:20',
            'matricula' => 'nullable|string|max:50',
            'cargo' => 'nullable|string|max:100',
            'nivel_acesso' => 'required|string',
            'municipio' => 'nullable|string|max:100',
            'password' => 'required|string|min:8|confirmed',
            'ativo' => 'boolean',
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
        return view('admin.usuarios-internos.show', compact('usuarioInterno'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UsuarioInterno $usuarioInterno)
    {
        return view('admin.usuarios-internos.edit', compact('usuarioInterno'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UsuarioInterno $usuarioInterno)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|size:11|unique:usuarios_internos,cpf,' . $usuarioInterno->id,
            'email' => 'required|email|unique:usuarios_internos,email,' . $usuarioInterno->id,
            'telefone' => 'nullable|string|max:20',
            'matricula' => 'nullable|string|max:50',
            'cargo' => 'nullable|string|max:100',
            'nivel_acesso' => 'required|string',
            'municipio' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:8|confirmed',
            'ativo' => 'boolean',
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
