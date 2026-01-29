@extends('layouts.admin')

@section('title', 'Mensagens do Suporte')
@section('page-title', 'Mensagens do Suporte InfoVISA')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <p class="text-gray-600">Envie mensagens para todos os usuários do sistema por nível de acesso.</p>
        </div>
        <a href="{{ route('admin.configuracoes.chat-broadcast.create') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nova Mensagem
        </a>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
        <p class="text-sm text-green-700">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Lista de Mensagens --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($broadcasts->isEmpty())
        <div class="p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-1">Nenhuma mensagem enviada</h3>
            <p class="text-gray-500">Clique em "Nova Mensagem" para enviar um aviso aos usuários.</p>
        </div>
        @else
        <div class="divide-y divide-gray-200">
            @foreach($broadcasts as $broadcast)
            <div class="p-4 hover:bg-gray-50" x-data="{ showStats: false }">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-gray-900">Suporte InfoVISA</span>
                            <span class="text-xs text-gray-400">{{ $broadcast->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <p class="text-gray-700 whitespace-pre-wrap">{{ $broadcast->conteudo }}</p>
                        
                        @if($broadcast->arquivo_path)
                        <div class="mt-2">
                            <a href="{{ Storage::url($broadcast->arquivo_path) }}" target="_blank" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                {{ $broadcast->arquivo_nome }}
                            </a>
                        </div>
                        @endif

                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            @php
                                $niveis = $broadcast->niveis_acesso;
                                $labels = [
                                    'todos' => 'Todos',
                                    'administrador' => 'Administrador',
                                    'gestor_estadual' => 'Gestor Estadual',
                                    'tecnico_estadual' => 'Técnico Estadual',
                                    'gestor_municipal' => 'Gestor Municipal',
                                    'tecnico_municipal' => 'Técnico Municipal',
                                ];
                            @endphp
                            @foreach($niveis as $nivel)
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded-full">
                                {{ $labels[$nivel] ?? $nivel }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="showStats = !showStats" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition" title="Ver estatísticas">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </button>
                        <form action="{{ route('admin.configuracoes.chat-broadcast.destroy', $broadcast) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta mensagem?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Excluir">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                
                {{-- Estatísticas --}}
                <div x-show="showStats" x-cloak class="mt-4 p-3 bg-gray-50 rounded-lg" x-data="{ stats: null }" x-init="
                    $watch('showStats', async (value) => {
                        if (value && !stats) {
                            const r = await fetch('{{ route('admin.configuracoes.chat-broadcast.estatisticas', $broadcast) }}');
                            stats = await r.json();
                        }
                    })
                ">
                    <div x-show="!stats" class="text-center text-gray-500 text-sm">Carregando...</div>
                    <div x-show="stats" class="flex items-center gap-6">
                        <div>
                            <span class="text-2xl font-bold text-gray-900" x-text="stats?.lidos || 0"></span>
                            <span class="text-gray-500">/ <span x-text="stats?.total || 0"></span></span>
                            <p class="text-xs text-gray-500">usuários leram</p>
                        </div>
                        <div class="flex-1">
                            <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-green-500 rounded-full transition-all" :style="'width: ' + (stats?.percentual || 0) + '%'"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1"><span x-text="stats?.percentual || 0"></span>% de leitura</p>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $broadcasts->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
