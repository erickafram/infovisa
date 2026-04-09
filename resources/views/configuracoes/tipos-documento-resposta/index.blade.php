@extends('layouts.admin')
@section('title', 'Tipos de Documento Resposta')
@section('page-title', 'Tipos de Documento Resposta')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-sm text-gray-500">Documentos que o estabelecimento deve enviar como resposta a notificações</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.configuracoes.tipos-documento.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Voltar</a>
            <a href="{{ route('admin.configuracoes.tipos-documento-resposta.create') }}" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Novo Tipo</a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @forelse($tipos as $tipo)
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50">
            <div class="flex items-center gap-3">
                <span class="w-2 h-2 rounded-full {{ $tipo->ativo ? 'bg-green-500' : 'bg-gray-300' }}"></span>
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $tipo->nome }}</p>
                    @if($tipo->descricao)
                    <p class="text-xs text-gray-500">{{ Str::limit($tipo->descricao, 80) }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.configuracoes.tipos-documento-resposta.edit', $tipo) }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Editar</a>
                <form action="{{ route('admin.configuracoes.tipos-documento-resposta.destroy', $tipo) }}" method="POST" onsubmit="return confirm('Excluir este tipo?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">Excluir</button>
                </form>
            </div>
        </div>
        @empty
        <div class="p-8 text-center text-gray-500 text-sm">Nenhum tipo de documento resposta cadastrado.</div>
        @endforelse
    </div>
</div>
@endsection
