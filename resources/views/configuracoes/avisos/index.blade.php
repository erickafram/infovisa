@extends('layouts.admin')

@section('title', 'Avisos do Sistema')
@section('page-title', 'Avisos do Sistema')

@section('content')
<div class="max-w-6xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('admin.configuracoes.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Voltar para Configurações
            </a>
            <p class="text-gray-600">Gerencie avisos que aparecem no dashboard dos usuários internos</p>
        </div>
        <a href="{{ route('admin.configuracoes.avisos.create') }}" class="px-4 py-2 bg-cyan-600 text-white text-sm font-medium rounded-lg hover:bg-cyan-700 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo Aviso
        </a>
    </div>

    {{-- Lista de Avisos --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        @if($avisos->count() > 0)
        <div class="divide-y divide-gray-100">
            @foreach($avisos as $aviso)
            <div class="p-4 hover:bg-gray-50 transition {{ !$aviso->ativo || $aviso->isExpirado() ? 'opacity-60' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full border {{ $aviso->tipo_color }}">
                                {{ ucfirst($aviso->tipo) }}
                            </span>
                            @if(!$aviso->ativo)
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600">Inativo</span>
                            @elseif($aviso->isExpirado())
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-600">Expirado</span>
                            @else
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-600">Ativo</span>
                            @endif
                        </div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ $aviso->titulo }}</h3>
                        <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $aviso->mensagem }}</p>
                        @if($aviso->link)
                        <p class="text-xs text-cyan-600 mt-1 truncate">🔗 {{ $aviso->link }}</p>
                        @endif
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            <span class="text-xs text-gray-500">
                                Para: 
                                @foreach($aviso->niveis_labels as $label)
                                <span class="inline-block px-1.5 py-0.5 bg-gray-100 rounded text-gray-700">{{ $label }}</span>
                                @endforeach
                            </span>
                            @if($aviso->data_expiracao)
                            <span class="text-xs text-gray-500">• Expira: {{ $aviso->data_expiracao->format('d/m/Y') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <form action="{{ route('admin.configuracoes.avisos.toggle', $aviso) }}" method="POST" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="p-2 rounded-lg hover:bg-gray-100 transition" title="{{ $aviso->ativo ? 'Desativar' : 'Ativar' }}">
                                @if($aviso->ativo)
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                @else
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                                @endif
                            </button>
                        </form>
                        <a href="{{ route('admin.configuracoes.avisos.edit', $aviso) }}" class="p-2 rounded-lg hover:bg-gray-100 transition" title="Editar">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                            </svg>
                        </a>
                        <form action="{{ route('admin.configuracoes.avisos.destroy', $aviso) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este aviso?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-lg hover:bg-red-50 transition" title="Excluir">
                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $avisos->links() }}
        </div>
        @else
        <div class="p-8 text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p class="text-gray-500">Nenhum aviso cadastrado</p>
            <a href="{{ route('admin.configuracoes.avisos.create') }}" class="inline-block mt-3 text-sm text-cyan-600 hover:text-cyan-700 font-medium">
                Criar primeiro aviso
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
