@extends('layouts.admin')

@section('title', 'Painel WhatsApp')
@section('page-title', 'Painel de Mensagens WhatsApp')

@section('content')
<div class="max-w-8xl mx-auto" x-data="whatsappPainel()">

    {{-- Estatísticas --}}
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-gray-900">{{ number_format($estatisticas['total']) }}</p>
            <p class="text-xs text-gray-500 mt-1">Total</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ number_format($estatisticas['pendentes']) }}</p>
            <p class="text-xs text-gray-500 mt-1">Pendentes</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ number_format($estatisticas['enviados']) }}</p>
            <p class="text-xs text-gray-500 mt-1">Enviados</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ number_format($estatisticas['entregues']) }}</p>
            <p class="text-xs text-gray-500 mt-1">Entregues</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600">{{ number_format($estatisticas['lidos']) }}</p>
            <p class="text-xs text-gray-500 mt-1">Lidos</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-red-600">{{ number_format($estatisticas['erros']) }}</p>
            <p class="text-xs text-gray-500 mt-1">Erros</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
            <p class="text-2xl font-bold text-indigo-600">{{ number_format($estatisticas['hoje']) }}</p>
            <p class="text-xs text-gray-500 mt-1">Hoje</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <form method="GET" action="{{ route('admin.whatsapp.painel') }}" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-12 gap-3.5 items-end">
            <div class="xl:col-span-2">
                <label class="block text-[11px] font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Status</label>
                <select name="status" class="w-full h-11 rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Todos</option>
                    <option value="pendente" {{ request('status') === 'pendente' ? 'selected' : '' }}>Pendente</option>
                    <option value="enviado" {{ request('status') === 'enviado' ? 'selected' : '' }}>Enviado</option>
                    <option value="entregue" {{ request('status') === 'entregue' ? 'selected' : '' }}>Entregue</option>
                    <option value="lido" {{ request('status') === 'lido' ? 'selected' : '' }}>Lido</option>
                    <option value="erro" {{ request('status') === 'erro' ? 'selected' : '' }}>Erro</option>
                </select>
            </div>
            <div class="xl:col-span-2">
                <label class="block text-[11px] font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Data Início</label>
                <input type="date" name="data_inicio" value="{{ request('data_inicio') }}"
                       class="w-full h-11 rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="xl:col-span-2">
                <label class="block text-[11px] font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Data Fim</label>
                <input type="date" name="data_fim" value="{{ request('data_fim') }}"
                       class="w-full h-11 rounded-lg border-gray-300 shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="sm:col-span-2 xl:col-span-2">
                <label class="block text-[11px] font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Estabelecimento</label>
                <input type="text" name="estabelecimento" value="{{ request('estabelecimento') }}" placeholder="Buscar..."
                       class="w-full h-11 rounded-lg border-gray-300 shadow-sm text-sm placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="sm:col-span-2 xl:col-span-2">
                <label class="block text-[11px] font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Destinatário</label>
                <input type="text" name="destinatario" value="{{ request('destinatario') }}" placeholder="Buscar..."
                       class="w-full h-11 rounded-lg border-gray-300 shadow-sm text-sm placeholder:text-gray-400 focus:border-blue-500 focus:ring-blue-500">
            </div>
            <div class="sm:col-span-2 xl:col-span-2">
                <div class="flex gap-2 w-full">
                <button type="submit" class="inline-flex items-center justify-center gap-1.5 h-11 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors w-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Filtrar
                </button>
                <a href="{{ route('admin.whatsapp.painel') }}" class="inline-flex items-center justify-center gap-1.5 h-11 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition-colors w-full">
                    Limpar
                </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Ações em lote --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.whatsapp.configuracao') }}" 
               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Configurações
            </a>
            @if($estatisticas['erros'] > 0)
            <button @click="reenviarTodas()" 
                    :disabled="reenviando"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-orange-100 text-orange-700 rounded-lg hover:bg-orange-200 text-sm font-medium transition-colors disabled:opacity-50">
                <svg class="w-4 h-4" :class="{'animate-spin': reenviando}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Reenviar com Erro ({{ $estatisticas['erros'] }})
            </button>
            @endif
        </div>
        <a href="{{ route('admin.whatsapp.exportar', request()->query()) }}" 
           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Exportar CSV
        </a>
    </div>

    {{-- Tabela de Mensagens --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        @if($mensagens->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destinatário</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estabelecimento</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($mensagens as $mensagem)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                            {{ $mensagem->created_at->format('d/m/Y') }}
                            <br>
                            <span class="text-xs text-gray-400">{{ $mensagem->created_at->format('H:i') }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-900">
                            <div class="font-medium">{{ $mensagem->nome_destinatario }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                            {{ $mensagem->telefone }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            @if($mensagem->documentoDigital)
                                <div class="font-medium text-gray-900">{{ $mensagem->documentoDigital->tipoDocumento->nome ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500">#{{ $mensagem->documentoDigital->numero_formatado ?? $mensagem->documento_digital_id }}</div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 max-w-[200px] truncate" title="{{ $mensagem->estabelecimento->nome_fantasia ?? '' }}">
                            {{ $mensagem->estabelecimento->nome_fantasia ?? $mensagem->estabelecimento->razao_social ?? '-' }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @php
                                $corStatus = match($mensagem->status) {
                                    'pendente' => 'bg-yellow-100 text-yellow-800',
                                    'enviado' => 'bg-blue-100 text-blue-800',
                                    'entregue' => 'bg-green-100 text-green-800',
                                    'lido' => 'bg-emerald-100 text-emerald-800',
                                    'erro' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $corStatus }}">
                                {{ $mensagem->status_icone }} {{ $mensagem->status_texto }}
                            </span>
                            @if($mensagem->status === 'erro' && $mensagem->erro_mensagem)
                                <div class="text-xs text-red-500 mt-1 max-w-[200px] truncate" title="{{ $mensagem->erro_mensagem }}">
                                    {{ $mensagem->erro_mensagem }}
                                </div>
                            @endif
                            @if($mensagem->enviado_em)
                                <div class="text-xs text-gray-400 mt-1">
                                    Enviado: {{ $mensagem->enviado_em->format('d/m H:i') }}
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <div class="flex items-center gap-2">
                                <button @click="verDetalhes({{ $mensagem->id }})" 
                                        class="text-blue-600 hover:text-blue-800 transition-colors" title="Ver detalhes">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                                @if($mensagem->status === 'erro')
                                <button @click="reenviar({{ $mensagem->id }})" 
                                        :disabled="reenviandoId === {{ $mensagem->id }}"
                                        class="text-orange-600 hover:text-orange-800 transition-colors disabled:opacity-50" title="Reenviar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginação --}}
        <div class="px-4 py-3 border-t border-gray-200">
            {{ $mensagens->links() }}
        </div>
        @else
        <div class="p-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-500 mb-1">Nenhuma mensagem encontrada</h3>
            <p class="text-sm text-gray-400">As mensagens enviadas via WhatsApp aparecerão aqui.</p>
        </div>
        @endif
    </div>

    {{-- Modal de Detalhes --}}
    <div x-show="modalAberto" x-transition.opacity x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
         @click.self="modalAberto = false" @keydown.escape.window="modalAberto = false">
        <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full max-h-[80vh] overflow-y-auto" x-transition.scale>
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Detalhes da Mensagem</h3>
                    <button @click="modalAberto = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <template x-if="detalhesMensagem">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs font-medium text-gray-500">Destinatário</label>
                                <p class="text-sm font-medium text-gray-900" x-text="detalhesMensagem.destinatario"></p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500">Telefone</label>
                                <p class="text-sm text-gray-900" x-text="detalhesMensagem.telefone"></p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500">Status</label>
                                <p class="text-sm font-medium" x-text="detalhesMensagem.status_texto"></p>
                            </div>
                            <div>
                                <label class="text-xs font-medium text-gray-500">Tentativas</label>
                                <p class="text-sm text-gray-900" x-text="detalhesMensagem.tentativas"></p>
                            </div>
                        </div>

                        <template x-if="detalhesMensagem.documento">
                            <div class="bg-blue-50 rounded-lg p-3">
                                <label class="text-xs font-medium text-blue-600">Documento</label>
                                <p class="text-sm font-medium text-blue-900" x-text="detalhesMensagem.documento.tipo + ' #' + detalhesMensagem.documento.numero"></p>
                            </div>
                        </template>

                        <template x-if="detalhesMensagem.estabelecimento">
                            <div class="bg-gray-50 rounded-lg p-3">
                                <label class="text-xs font-medium text-gray-500">Estabelecimento</label>
                                <p class="text-sm font-medium text-gray-900" x-text="detalhesMensagem.estabelecimento.nome"></p>
                            </div>
                        </template>

                        <div>
                            <label class="text-xs font-medium text-gray-500">Mensagem</label>
                            <pre class="text-sm text-gray-700 whitespace-pre-wrap bg-gray-50 rounded-lg p-3 mt-1 font-sans" x-text="detalhesMensagem.mensagem"></pre>
                        </div>

                        <template x-if="detalhesMensagem.erro">
                            <div class="bg-red-50 rounded-lg p-3">
                                <label class="text-xs font-medium text-red-600">Erro</label>
                                <p class="text-sm text-red-800" x-text="detalhesMensagem.erro"></p>
                            </div>
                        </template>

                        <div class="grid grid-cols-2 gap-4 text-xs text-gray-500 border-t pt-3 mt-3">
                            <div><span class="font-medium">Criado:</span> <span x-text="detalhesMensagem.criado_em"></span></div>
                            <div x-show="detalhesMensagem.enviado_em"><span class="font-medium">Enviado:</span> <span x-text="detalhesMensagem.enviado_em"></span></div>
                            <div x-show="detalhesMensagem.entregue_em"><span class="font-medium">Entregue:</span> <span x-text="detalhesMensagem.entregue_em"></span></div>
                            <div x-show="detalhesMensagem.lido_em"><span class="font-medium">Lido:</span> <span x-text="detalhesMensagem.lido_em"></span></div>
                        </div>
                    </div>
                </template>

                <div x-show="carregandoDetalhes" class="text-center py-8">
                    <svg class="w-8 h-8 text-gray-400 animate-spin mx-auto" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-sm text-gray-500 mt-2">Carregando...</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Feedback flutuante --}}
    <div x-show="feedback" x-transition
         class="fixed bottom-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-sm font-medium"
         :class="feedbackTipo === 'sucesso' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'">
        <span x-text="feedback"></span>
    </div>
</div>

<script>
function whatsappPainel() {
    return {
        modalAberto: false,
        detalhesMensagem: null,
        carregandoDetalhes: false,
        reenviandoId: null,
        reenviando: false,
        feedback: '',
        feedbackTipo: '',

        async verDetalhes(id) {
            this.modalAberto = true;
            this.carregandoDetalhes = true;
            this.detalhesMensagem = null;
            try {
                const response = await fetch(`/admin/whatsapp/mensagens/${id}/detalhes`);
                this.detalhesMensagem = await response.json();
            } catch (e) {
                this.mostrarFeedback('Erro ao carregar detalhes.', 'erro');
                this.modalAberto = false;
            }
            this.carregandoDetalhes = false;
        },

        async reenviar(id) {
            this.reenviandoId = id;
            try {
                const response = await fetch(`/admin/whatsapp/mensagens/${id}/reenviar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                this.mostrarFeedback(data.mensagem, data.sucesso ? 'sucesso' : 'erro');
                if (data.sucesso) {
                    setTimeout(() => location.reload(), 1500);
                }
            } catch (e) {
                this.mostrarFeedback('Erro ao reenviar.', 'erro');
            }
            this.reenviandoId = null;
        },

        async reenviarTodas() {
            if (!confirm('Deseja reenviar todas as mensagens com erro?')) return;
            this.reenviando = true;
            try {
                const response = await fetch('{{ route("admin.whatsapp.reenviar-todas") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
                this.mostrarFeedback(data.mensagem, 'sucesso');
                setTimeout(() => location.reload(), 2000);
            } catch (e) {
                this.mostrarFeedback('Erro ao reenviar mensagens.', 'erro');
            }
            this.reenviando = false;
        },

        mostrarFeedback(msg, tipo) {
            this.feedback = msg;
            this.feedbackTipo = tipo;
            setTimeout(() => { this.feedback = ''; }, 5000);
        }
    }
}
</script>
@endsection
