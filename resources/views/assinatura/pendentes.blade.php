@extends('layouts.admin')

@section('title', 'Assinaturas Pendentes')

@section('content')
<div class="container-fluid px-4 py-6" x-data="assinaturaEmLote()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Assinaturas Pendentes</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $assinaturasPendentes->count() }} documento(s) aguardando sua assinatura</p>
        </div>
        <a href="{{ route('admin.assinatura.configurar-senha') }}" 
           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Configurar Senha
        </a>
    </div>

    @if($assinaturasPendentes->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Tudo em dia!</h3>
            <p class="text-sm text-gray-500">Você não possui documentos aguardando assinatura.</p>
        </div>
    @else
        <!-- Barra de ações em lote -->
        <div x-show="selecionados.length > 0" x-cloak
             class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-blue-900">
                    <span x-text="selecionados.length"></span> documento(s) selecionado(s)
                </span>
                <button @click="selecionados = []" class="text-sm text-blue-600 hover:text-blue-800">
                    Limpar seleção
                </button>
            </div>
            <button @click="abrirModalAssinaturaLote()" 
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                Assinar Selecionados
            </button>
        </div>

        <!-- Lista de documentos -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <!-- Header da tabela -->
            <div class="px-5 py-3 bg-gray-50 border-b border-gray-200 flex items-center gap-4">
                <label class="flex items-center">
                    <input type="checkbox" 
                           @change="toggleTodos($event.target.checked)"
                           :checked="selecionados.length === {{ $assinaturasPendentes->count() }}"
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                </label>
                <span class="text-xs font-medium text-gray-500 uppercase">Selecionar todos</span>
            </div>

            <div class="divide-y divide-gray-100">
                @foreach($assinaturasPendentes as $assinatura)
                @php
                    $doc = $assinatura->documentoDigital;
                    $processo = $doc->processo ?? null;
                @endphp
                <div class="p-4 hover:bg-gray-50 transition-colors flex items-center gap-4">
                    <!-- Checkbox -->
                    <label class="flex items-center">
                        <input type="checkbox" 
                               value="{{ $doc->id }}"
                               x-model="selecionados"
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    </label>

                    <!-- Ícone -->
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>

                    <!-- Info do documento -->
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900">
                            {{ $doc->tipoDocumento->nome ?? 'Documento' }}
                            <span class="text-gray-400 font-normal">#{{ $doc->id }}</span>
                        </p>
                        @if($processo)
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $processo->estabelecimento->nome_fantasia ?? $processo->estabelecimento->razao_social ?? 'Estabelecimento' }}
                            <span class="text-gray-400">• Processo {{ $processo->numero_processo }}</span>
                        </p>
                        @endif
                    </div>

                    <!-- Ordem de assinatura -->
                    <div class="text-center px-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-700">
                            {{ $assinatura->ordem }}º
                        </span>
                    </div>

                    <!-- Data -->
                    <div class="text-right w-24">
                        <p class="text-sm text-gray-900">{{ $assinatura->created_at->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-400">{{ $assinatura->created_at->format('H:i') }}</p>
                    </div>

                    <!-- Ações -->
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.documentos.visualizar-pdf', $doc->id) }}" target="_blank"
                           class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg" title="Visualizar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        <a href="{{ route('admin.assinatura.assinar', $doc->id) }}"
                           class="px-3 py-1.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                            Assinar
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Aviso -->
        <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-amber-900">Atenção</p>
                <p class="text-xs text-amber-700 mt-0.5">
                    Ao assinar um documento, você confirma que leu e concorda com seu conteúdo. A assinatura digital tem validade jurídica.
                </p>
            </div>
        </div>

        <!-- Modal de Assinatura em Lote -->
        <div x-show="modalLote" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.5)">
            <div @click.away="modalLote = false" class="bg-white rounded-xl shadow-xl w-full max-w-md">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Assinar Documentos em Lote</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Você está prestes a assinar <span class="font-medium text-blue-600" x-text="selecionados.length"></span> documento(s)
                    </p>
                </div>
                
                <form @submit.prevent="assinarEmLote()" class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Senha de Assinatura Digital</label>
                        <input type="password" x-model="senhaAssinatura" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Digite sua senha de assinatura">
                        <p x-show="erroSenha" x-text="erroSenha" class="text-sm text-red-600 mt-1"></p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-3 mb-4">
                        <p class="text-xs text-gray-600">
                            <strong>Documentos a assinar:</strong>
                        </p>
                        <ul class="mt-2 space-y-1 max-h-32 overflow-y-auto">
                            <template x-for="id in selecionados" :key="id">
                                <li class="text-xs text-gray-500 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span x-text="'Documento #' + id"></span>
                                </li>
                            </template>
                        </ul>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" @click="modalLote = false" 
                                class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="assinando"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 flex items-center justify-center gap-2">
                            <svg x-show="assinando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="assinando ? 'Assinando...' : 'Assinar Todos'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
function assinaturaEmLote() {
    return {
        selecionados: [],
        modalLote: false,
        senhaAssinatura: '',
        assinando: false,
        erroSenha: '',
        
        toggleTodos(checked) {
            if (checked) {
                this.selecionados = [@foreach($assinaturasPendentes as $a){{ $a->documentoDigital->id }},@endforeach].map(String);
            } else {
                this.selecionados = [];
            }
        },
        
        abrirModalAssinaturaLote() {
            this.senhaAssinatura = '';
            this.erroSenha = '';
            this.modalLote = true;
        },
        
        async assinarEmLote() {
            if (!this.senhaAssinatura) {
                this.erroSenha = 'Digite sua senha de assinatura';
                return;
            }
            
            this.assinando = true;
            this.erroSenha = '';
            
            try {
                const response = await fetch('{{ route("admin.assinatura.processar-lote") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        documentos: this.selecionados,
                        senha_assinatura: this.senhaAssinatura
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.reload();
                } else {
                    this.erroSenha = data.error || 'Erro ao assinar documentos';
                }
            } catch (e) {
                this.erroSenha = 'Erro ao processar assinaturas';
            }
            
            this.assinando = false;
        }
    }
}
</script>
@endsection
