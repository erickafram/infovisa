<?php

namespace App\Http\Controllers;

use App\Models\UsuarioExterno;
use App\Enums\VinculoEstabelecimento;
use Illuminate\Http\Request;

class UsuarioExternoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = UsuarioExterno::query();

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

        // Filtro por vínculo
        if ($request->filled('vinculo_estabelecimento')) {
            $query->where('vinculo_estabelecimento', $request->vinculo_estabelecimento);
        }

        // Filtro por status
        if ($request->filled('ativo')) {
            $query->where('ativo', $request->ativo === '1');
        }

        // Filtro por aceite de termos
        if ($request->filled('aceite_termos')) {
            if ($request->aceite_termos === '1') {
                $query->whereNotNull('aceite_termos_em');
            } else {
                $query->whereNull('aceite_termos_em');
            }
        }

        // Ordenação
        $sortField = $request->get('sort', 'nome');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        // Paginação
        $usuarios = $query->paginate(15)->withQueryString();

        return view('admin.usuarios-externos.index', compact('usuarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.usuarios-externos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|size:11|unique:usuarios_externos,cpf',
            'email' => 'required|email|unique:usuarios_externos,email',
            'telefone' => 'nullable|string|max:20',
            'vinculo_estabelecimento' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'ativo' => 'boolean',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $validated['ativo'] = $request->has('ativo');

        UsuarioExterno::create($validated);

        return redirect()->route('admin.usuarios-externos.index')
            ->with('success', 'Usuário externo criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(UsuarioExterno $usuarioExterno)
    {
        return view('admin.usuarios-externos.show', compact('usuarioExterno'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UsuarioExterno $usuarioExterno)
    {
        return view('admin.usuarios-externos.edit', compact('usuarioExterno'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UsuarioExterno $usuarioExterno)
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf' => 'required|string|size:11|unique:usuarios_externos,cpf,' . $usuarioExterno->id,
            'email' => 'required|email|unique:usuarios_externos,email,' . $usuarioExterno->id,
            'telefone' => 'nullable|string|max:20',
            'vinculo_estabelecimento' => 'required|string',
            'password' => 'nullable|string|min:8|confirmed',
            'ativo' => 'boolean',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['ativo'] = $request->has('ativo');

        $usuarioExterno->update($validated);

        return redirect()->route('admin.usuarios-externos.index')
            ->with('success', 'Usuário externo atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UsuarioExterno $usuarioExterno)
    {
        $usuarioExterno->delete();

        return redirect()->route('admin.usuarios-externos.index')
            ->with('success', 'Usuário externo excluído com sucesso!');
    }
}
