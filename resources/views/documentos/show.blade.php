@extends('layouts.admin')

@section('title', 'Visualizar Documento')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-8xl mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="mb-4">
            <div class="flex items-center gap-2 text-xs text-gray-600 mb-2">
                @if($documento->processo)
                    <a href="{{ route('admin.estabelecimentos.processos.show', [$documento->processo->estabelecimento_id, $documento->processo->id]) }}" class="hover:text-blue-600 transition">
                        Processo {{ $documento->processo->numero_processo }}
                    </a>
                @else
                    <a href="{{ route('admin.documentos.index') }}" class="hover:text-blue-600 transition">
                        Documentos
                    </a>
                @endif
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-gray-900 font-medium">{{ $documento->nome ?? $documento->tipoDocumento->nome }}</span>
            </div>
        </div>

        {{-- Documento --}}
        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
            {{-- Cabeçalho do Documento --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-5">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h1 class="text-xl font-semibold mb-1">{{ $documento->nome ?? $documento->tipoDocumento->nome }}</h1>
                        <p class="text-blue-100 text-sm">{{ $documento->numero_documento }}</p>
                    </div>
                    <div class="flex gap-2">
                        @if($documento->status !== 'rascunho' && $documento->arquivo_pdf)
                            <a href="{{ route('admin.documentos.pdf', $documento->id) }}" 
                               class="px-3 py-1.5 text-sm bg-white text-blue-600 rounded-lg hover:bg-blue-50 transition flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Baixar PDF
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Informações do Documento --}}
            <div class="p-5 border-b border-gray-200 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Processo --}}
                    @if($documento->processo)
                        <div>
                            <h3 class="text-xs font-semibold text-gray-700 mb-1.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Processo Vinculado
                            </h3>
                            <a href="{{ route('admin.estabelecimentos.processos.show', [$documento->processo->estabelecimento_id, $documento->processo->id]) }}" 
                               class="block text-sm text-blue-600 hover:text-blue-800 font-medium hover:underline transition-colors">
                                {{ $documento->processo->numero_processo }}
                            </a>
                            <p class="text-xs text-gray-600 mt-0.5">
                                <span class="font-medium">Estabelecimento:</span> {{ $documento->processo->estabelecimento->nome_fantasia ?? $documento->processo->estabelecimento->razao_social }}
                            </p>
                        </div>
                    @endif

                    {{-- Tipo de Documento --}}
                    <div>
                        <h3 class="text-xs font-semibold text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                            Tipo de Documento
                        </h3>
                        <p class="text-sm text-gray-900 font-medium">{{ $documento->tipoDocumento->nome }}</p>
                    </div>

                    {{-- Criado por --}}
                    <div>
                        <h3 class="text-xs font-semibold text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Criado por
                        </h3>
                        <p class="text-sm text-gray-900 font-medium">{{ $documento->usuarioCriador->nome }}</p>
                        <p class="text-xs text-gray-600">{{ $documento->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    {{-- Status --}}
                    <div>
                        <h3 class="text-xs font-semibold text-gray-700 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Status
                        </h3>
                        @if($documento->status === 'rascunho')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                Rascunho
                            </span>
                        @elseif($documento->status === 'aguardando_assinatura')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Aguardando Assinatura
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Finalizado
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Conteúdo do Documento --}}
            <div class="p-5">
                <h2 class="text-base font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Conteúdo
                </h2>
                <div class="prose prose-sm max-w-none">
                    {!! $documento->conteudo !!}
                </div>
            </div>

            {{-- Assinaturas --}}
            @if($documento->assinaturas->count() > 0)
                <div class="p-5 border-t border-gray-200 bg-gray-50">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-base font-semibold text-gray-900 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                            Assinaturas Digitais
                        </h2>
                        @php
                            $temAssinaturaFeita = $documento->assinaturas->where('status', 'assinado')->count() > 0;
                            $usuarioLogado = auth('interno')->user();
                            $isAdmin = $usuarioLogado->isAdmin();
                        @endphp
                        @if((!$temAssinaturaFeita || $isAdmin) && $documento->status !== 'assinado')
                            <button onclick="abrirModalGerenciarAssinantes()" 
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                Gerenciar Assinantes
                            </button>
                        @endif
                    </div>
                    <div class="space-y-2">
                        @foreach($documento->assinaturas as $assinatura)
                            <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $assinatura->usuarioInterno->nome }}</p>
                                        <p class="text-xs text-gray-600">{{ $assinatura->usuarioInterno->cargo ?? 'Cargo não informado' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($assinatura->status === 'assinado')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Assinado
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Pendente
                                        </span>
                                        @if(!$temAssinaturaFeita || $isAdmin)
                                            <button onclick="removerAssinante({{ $assinatura->id }})" 
                                                    class="text-red-600 hover:text-red-800 transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if((!$temAssinaturaFeita || $isAdmin) && $documento->status !== 'assinado')
                        <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-xs text-blue-800">
                                <strong>💡 Dica:</strong> 
                                @if($isAdmin)
                                    Como administrador, você pode adicionar ou remover assinantes a qualquer momento.
                                @else
                                    Você pode adicionar ou remover assinantes enquanto nenhuma assinatura foi feita.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Botões de Ação --}}
        <div class="mt-6 flex flex-wrap gap-3">
            {{-- Botão Voltar --}}
            <a href="{{ route('admin.documentos.index') }}" 
               class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                Voltar
            </a>

            {{-- Botão Ver Processo (se houver processo vinculado) --}}
            @if($documento->processo)
                <a href="{{ route('admin.estabelecimentos.processos.show', [$documento->processo->estabelecimento_id, $documento->processo->id]) }}" 
                   class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
                    Ver Processo
                </a>
            @endif

            {{-- Botão Editar Rascunho (se for rascunho) --}}
            @if($documento->status === 'rascunho' && $documento->usuario_criador_id === auth('interno')->id())
                <a href="{{ route('admin.documentos.edit', $documento->id) }}" 
                   class="px-5 py-2.5 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
                    Editar Rascunho
                </a>
            @endif
        </div>
    </div>
</div>

{{-- Modal para Gerenciar Assinantes --}}
<div id="modalGerenciarAssinantes" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4 pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">👥 Gerenciar Assinantes</h3>
            <button onclick="fecharModalGerenciarAssinantes()" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form action="{{ route('admin.documentos.gerenciar-assinantes', $documento->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Selecione os usuários que devem assinar este documento
                </label>
                <div class="max-h-64 overflow-y-auto border border-gray-300 rounded-lg p-3 space-y-2">
                    @php
                        $usuarioLogado = auth('interno')->user();
                        $usuariosInternosQuery = \App\Models\UsuarioInterno::where('ativo', true);
                        
                        // Filtra por município do usuário logado
                        if ($usuarioLogado->municipio_id) {
                            $usuariosInternosQuery->where('municipio_id', $usuarioLogado->municipio_id);
                        }
                        
                        // Exclui administradores do sistema (sem município)
                        $usuariosInternosQuery->whereNotNull('municipio_id');
                        
                        $usuariosInternos = $usuariosInternosQuery->orderBy('nome')->get();
                        $assinantesAtuais = $documento->assinaturas->pluck('usuario_interno_id')->toArray();
                    @endphp
                    @foreach($usuariosInternos as $usuario)
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                            <input type="checkbox" 
                                   name="assinantes[]" 
                                   value="{{ $usuario->id }}"
                                   {{ in_array($usuario->id, $assinantesAtuais) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">{{ $usuario->nome }}</p>
                                <p class="text-xs text-gray-500">{{ $usuario->cargo ?? 'Cargo não informado' }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-gray-500">
                    Selecione os usuários que devem assinar o documento. A ordem será definida automaticamente.
                </p>
            </div>
            
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="fecharModalGerenciarAssinantes()" 
                        class="px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-lg hover:bg-gray-300">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalGerenciarAssinantes() {
    document.getElementById('modalGerenciarAssinantes').classList.remove('hidden');
}

function fecharModalGerenciarAssinantes() {
    document.getElementById('modalGerenciarAssinantes').classList.add('hidden');
}

function removerAssinante(assinaturaId) {
    if (confirm('Tem certeza que deseja remover este assinante?')) {
        fetch(`/admin/documentos/assinaturas/${assinaturaId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Erro ao remover assinante');
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao remover assinante');
        });
    }
}

// Fechar modal ao clicar fora
document.getElementById('modalGerenciarAssinantes')?.addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModalGerenciarAssinantes();
    }
});
</script>
@endsection
