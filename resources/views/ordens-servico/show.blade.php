@extends('layouts.admin')

@section('title', 'Detalhes da Ordem de Serviço')

@section('content')
<div class="min-h-screen bg-gray-50">
    {{-- Header Clean --}}
    <div class="bg-white border-b border-gray-200">
        <div class="container-fluid px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.ordens-servico.index') }}" 
                       class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <h1 class="text-lg font-semibold text-gray-900">OS #{{ $ordemServico->numero }}</h1>
                </div>
                <div class="flex items-center gap-2">
                    {!! $ordemServico->status_badge !!}
                    {!! $ordemServico->competencia_badge !!}
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4 py-6">
        {{-- Layout de 2 Colunas: Menu Lateral (25%) + Conteúdo (75%) --}}
        <div class="flex flex-col lg:flex-row gap-6">
            
            {{-- ========================================
                COLUNA ESQUERDA: Menu de Ações (25%)
            ======================================== --}}
            <aside class="lg:w-1/4 space-y-5">
                {{-- Card de Menu de Opções --}}
                <div class="bg-white rounded-lg border border-gray-200 sticky top-6">
                    <div class="p-3 space-y-1.5">
                        @php
                            $isTecnicoAtribuido = $ordemServico->tecnicos_ids && in_array(auth()->id(), $ordemServico->tecnicos_ids);
                            $isGestor = auth('interno')->user()->isAdmin() || auth('interno')->user()->isEstadual() || auth('interno')->user()->isMunicipal();
                        @endphp
                        
                        @if($ordemServico->status === 'finalizada')
                            {{-- Botão Reiniciar OS (apenas para gestores) --}}
                            @if($isGestor)
                            <form method="POST" action="{{ route('admin.ordens-servico.reiniciar', $ordemServico) }}" 
                                  onsubmit="return confirm('Tem certeza que deseja reiniciar esta OS? Ela voltará ao status \'Em Andamento\'.')">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center gap-2 px-3 py-2.5 text-sm font-medium text-orange-700 bg-orange-50 rounded-md hover:bg-orange-100 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Reiniciar OS
                                </button>
                            </form>
                            @endif
                        @else
                            {{-- Botão Finalizar OS (apenas para técnicos atribuídos) --}}
                            @if($isTecnicoAtribuido)
                            <button type="button" 
                                    onclick="abrirModalFinalizarOS()"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Finalizar OS
                            </button>
                            @endif
                            
                            {{-- Botão Editar --}}
                            <a href="{{ route('admin.ordens-servico.edit', $ordemServico) }}" 
                               class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Editar
                            </a>
                        @endif
                        
                        {{-- Botão Voltar --}}
                        <a href="{{ route('admin.ordens-servico.index') }}" 
                           class="w-full flex items-center gap-2 px-3 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Voltar
                        </a>
                    </div>
                </div>

                {{-- Card de Informações Rápidas --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="p-3 space-y-2">
                        @if($ordemServico->processo)
                        <div>
                            <label class="text-xs font-medium text-gray-500">Processo</label>
                            <p class="text-sm font-semibold text-gray-900">{{ $ordemServico->processo->numero_processo }}</p>
                        </div>
                        <div class="border-t border-gray-100 pt-2"></div>
                        @endif
                        @if($ordemServico->municipio)
                        <div>
                            <label class="text-xs font-medium text-gray-500">Município</label>
                            <p class="text-sm font-semibold text-gray-900">{{ $ordemServico->municipio->nome }}/{{ $ordemServico->municipio->uf }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Card de Datas --}}
                <div class="bg-white rounded-lg border border-gray-200">
                    <div class="p-3 space-y-1.5">
                        <div class="flex justify-between items-center py-1.5">
                            <label class="text-xs text-gray-500">Abertura</label>
                            <p class="text-xs font-medium text-gray-900">
                                {{ $ordemServico->data_abertura ? $ordemServico->data_abertura->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-t border-gray-100">
                            <label class="text-xs text-gray-500">Início</label>
                            <p class="text-xs font-medium text-gray-900">
                                {{ $ordemServico->data_inicio ? $ordemServico->data_inicio->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-t border-gray-100">
                            <label class="text-xs text-gray-500">Término</label>
                            <p class="text-xs font-medium text-gray-900">
                                {{ $ordemServico->data_fim ? $ordemServico->data_fim->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                        @if($ordemServico->data_conclusao)
                        <div class="flex justify-between items-center py-1.5 border-t border-gray-100">
                            <label class="text-xs text-gray-500">Conclusão</label>
                            <p class="text-xs font-medium text-gray-900">
                                {{ $ordemServico->data_conclusao->format('d/m/Y') }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

            </aside>

            {{-- ========================================
                COLUNA DIREITA: Conteúdo Principal (75%)
            ======================================== --}}
            <main class="lg:w-3/4 space-y-6">
            {{-- Informações do Estabelecimento --}}
            @if($ordemServico->estabelecimento)
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900">Estabelecimento</h2>
                </div>
                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500">Razão Social</label>
                            <p class="text-sm font-medium text-gray-900">{{ $ordemServico->estabelecimento->razao_social }}</p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">Nome Fantasia</label>
                            <p class="text-sm font-medium text-gray-900">{{ $ordemServico->estabelecimento->nome_fantasia }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="text-xs text-gray-500">
                                {{ $ordemServico->estabelecimento->tipo_pessoa === 'fisica' ? 'CPF' : 'CNPJ' }}
                            </label>
                            <p class="text-sm font-medium text-gray-900 font-mono">
                                @if($ordemServico->estabelecimento->tipo_pessoa === 'fisica')
                                    {{ $ordemServico->estabelecimento->cpf_formatado ?? '-' }}
                                @else
                                    {{ $ordemServico->estabelecimento->cnpj_formatado ?? '-' }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">CEP</label>
                            <p class="text-sm font-medium text-gray-900 font-mono">{{ $ordemServico->estabelecimento->cep ?? '-' }}</p>
                        </div>
                    </div>
                    <div class="pt-2 border-t border-gray-100">
                        <label class="text-xs text-gray-500">Endereço</label>
                        <p class="text-sm font-medium text-gray-900" id="endereco-completo">
                            {{ $ordemServico->estabelecimento->logradouro }}
                            @if($ordemServico->estabelecimento->numero), {{ $ordemServico->estabelecimento->numero }}@endif
                            @if($ordemServico->estabelecimento->complemento) - {{ $ordemServico->estabelecimento->complemento }}@endif
                            , {{ $ordemServico->estabelecimento->bairro }}
                            @if(is_object($ordemServico->estabelecimento->municipio)) - {{ $ordemServico->estabelecimento->municipio->nome }}/{{ $ordemServico->estabelecimento->municipio->uf }}@endif
                        </p>
                    </div>

                    {{-- Mapa de Localização --}}
                    <div class="bg-white rounded-lg p-3 border border-gray-200">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Localização
                            </label>
                            @php
                                $endereco = urlencode(
                                    $ordemServico->estabelecimento->logradouro . ', ' .
                                    ($ordemServico->estabelecimento->numero ?? 'S/N') . ', ' .
                                    $ordemServico->estabelecimento->bairro . ', ' .
                                    ($ordemServico->estabelecimento->municipio->nome ?? '') . ', ' .
                                    ($ordemServico->estabelecimento->municipio->uf ?? '') . ', Brasil'
                                );
                                $googleMapsUrl = "https://www.google.com/maps/search/?api=1&query=" . $endereco;
                                $googleMapsEmbedUrl = "https://www.google.com/maps?q=" . $endereco . "&output=embed";
                            @endphp
                            <a href="{{ $googleMapsUrl }}" 
                               target="_blank"
                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                Abrir no Maps
                            </a>
                        </div>
                        
                        {{-- Iframe do Google Maps --}}
                        <div class="relative w-full rounded-lg overflow-hidden border border-gray-300 bg-gray-100" style="height: 300px;">
                            <iframe 
                                width="100%" 
                                height="100%" 
                                frameborder="0" 
                                style="border:0" 
                                referrerpolicy="no-referrer-when-downgrade"
                                src="{{ $googleMapsEmbedUrl }}"
                                allowfullscreen>
                            </iframe>
                        </div>
                        
                        <p class="mt-2 text-xs text-gray-500 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Clique em "Abrir no Maps" para ver rotas e mais detalhes
                        </p>
                    </div>
                </div>
            </div>
            @else
            {{-- Aviso quando não há estabelecimento --}}
            <div class="bg-amber-50 rounded-lg shadow border border-amber-200 p-6">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-amber-900 mb-1">Ordem de Serviço sem Estabelecimento</h3>
                        <p class="text-sm text-amber-800 mb-3">
                            Esta OS foi criada sem um estabelecimento vinculado. Você pode vincular um estabelecimento ao editar ou finalizar a ordem de serviço.
                        </p>
                        @if($ordemServico->status !== 'finalizada')
                        <a href="{{ route('admin.ordens-servico.edit', $ordemServico) }}" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Editar e Vincular Estabelecimento
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Ações Executadas --}}
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900">Ações Executadas</h2>
                    @if($ordemServico->tiposAcao()->count() > 0)
                    <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-semibold rounded">
                        {{ $ordemServico->tiposAcao()->count() }}
                    </span>
                    @endif
                </div>
                <div class="px-5 py-5">
                    @if($ordemServico->tiposAcao()->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($ordemServico->tiposAcao() as $tipoAcao)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-purple-600 text-white text-xs font-medium rounded-lg">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ $tipoAcao->descricao }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-4">Nenhuma ação cadastrada</p>
                    @endif
                </div>
            </div>

            {{-- Técnicos Responsáveis --}}
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900">Técnicos Responsáveis</h2>
                    @if($ordemServico->tecnicos()->count() > 0)
                    <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded">
                        {{ $ordemServico->tecnicos()->count() }}
                    </span>
                    @endif
                </div>
                <div class="px-5 py-5">
                    @if($ordemServico->tecnicos()->count() > 0)
                        <div class="flex flex-wrap gap-2">
                            @foreach($ordemServico->tecnicos() as $tecnico)
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-50 rounded-lg border border-green-200">
                                    <div class="w-6 h-6 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-bold text-xs">
                                            {{ strtoupper(substr($tecnico->nome, 0, 2)) }}
                                        </span>
                                    </div>
                                    <span class="text-xs font-medium text-gray-900">{{ $tecnico->nome }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 text-center py-4">Nenhum técnico atribuído</p>
                    @endif
                </div>
            </div>

            {{-- Observações --}}
            @if($ordemServico->observacoes)
            <div class="bg-white rounded-lg border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h2 class="text-base font-semibold text-gray-900">Observações</h2>
                </div>
                <div class="px-5 py-5">
                    <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $ordemServico->observacoes }}</p>
                </div>
            </div>
            @endif
            </main>
        </div>
    </div>
</div>
@endsection

@push('styles')
{{-- Estilos removidos - mapa agora usa Google Maps --}}
@endpush

@push('scripts')
<script>

    // Função para abrir modal de finalizar OS
    function abrirModalFinalizarOS() {
        document.getElementById('modalFinalizarOS').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // Função para fechar modal
    function fecharModalFinalizarOS() {
        document.getElementById('modalFinalizarOS').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Função para finalizar OS
    async function finalizarOS() {
        const form = document.getElementById('formFinalizarOS');
        const formData = new FormData(form);
        const btnFinalizar = document.getElementById('btnFinalizar');
        
        // Desabilita botão
        btnFinalizar.disabled = true;
        btnFinalizar.innerHTML = '<svg class="animate-spin h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Finalizando...';

        try {
            const response = await fetch('{{ route("admin.ordens-servico.finalizar", $ordemServico) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok) {
                // Sucesso
                alert('✅ ' + data.message);
                window.location.reload();
            } else {
                // Erro
                alert('❌ ' + (data.message || 'Erro ao finalizar OS'));
                btnFinalizar.disabled = false;
                btnFinalizar.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Confirmar Finalização';
            }
        } catch (error) {
            console.error('Erro:', error);
            alert('❌ Erro ao finalizar OS. Tente novamente.');
            btnFinalizar.disabled = false;
            btnFinalizar.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Confirmar Finalização';
        }
    }
</script>

{{-- Modal de Finalizar OS --}}
<div id="modalFinalizarOS" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4 border-b border-green-700">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Finalizar Ordem de Serviço
                </h3>
                <button type="button" onclick="fecharModalFinalizarOS()" class="text-white/80 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <form id="formFinalizarOS" class="p-6 space-y-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-800">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <strong>Atenção:</strong> Ao finalizar esta OS, você está confirmando que todas as atividades listadas foram executadas conforme planejado.
                </p>
            </div>

            @if(!$ordemServico->estabelecimento_id)
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <p class="text-sm text-amber-800 mb-3">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <strong>Esta OS não possui estabelecimento vinculado.</strong> Você pode vincular um estabelecimento agora ao finalizar, mas isso é opcional.
                </p>
                
                <div>
                    <label for="estabelecimento_id_finalizar" class="block text-sm font-semibold text-gray-700 mb-2">
                        Vincular Estabelecimento (Opcional)
                    </label>
                    <select name="estabelecimento_id" 
                            id="estabelecimento_id_finalizar"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">Não vincular estabelecimento</option>
                        @php
                            $usuario = Auth::guard('interno')->user();
                            $estabelecimentosDisponiveis = \App\Models\Estabelecimento::query();
                            
                            if ($usuario->isMunicipal()) {
                                $estabelecimentosDisponiveis->where('municipio_id', $usuario->municipio_id);
                            } elseif ($usuario->isEstadual()) {
                                // Estadual vê estabelecimentos de competência estadual
                                $estabelecimentosDisponiveis->where(function($q) {
                                    $q->where('competencia_manual', 'estadual')
                                      ->orWhereNull('competencia_manual');
                                });
                            }
                            
                            $estabelecimentosDisponiveis = $estabelecimentosDisponiveis->orderBy('nome_fantasia')->get();
                        @endphp
                        @foreach($estabelecimentosDisponiveis as $estab)
                        <option value="{{ $estab->id }}">
                            {{ $estab->nome_fantasia }} - {{ $estab->razao_social }}
                        </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-600">
                        💡 Se o estabelecimento tiver um processo ativo, a OS será automaticamente vinculada a ele.
                    </p>
                </div>
            </div>
            @endif

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    As atividades foram realizadas? <span class="text-red-500">*</span>
                </label>
                <div class="space-y-2">
                    <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                        <input type="radio" name="atividades_realizadas" value="sim" required class="w-4 h-4 text-green-600 focus:ring-green-500">
                        <span class="ml-3 text-sm font-medium text-gray-900">✅ Sim, todas as atividades foram realizadas</span>
                    </label>
                    <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                        <input type="radio" name="atividades_realizadas" value="parcial" required class="w-4 h-4 text-yellow-600 focus:ring-yellow-500">
                        <span class="ml-3 text-sm font-medium text-gray-900">⚠️ Parcialmente (algumas atividades não foram realizadas)</span>
                    </label>
                    <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                        <input type="radio" name="atividades_realizadas" value="nao" required class="w-4 h-4 text-red-600 focus:ring-red-500">
                        <span class="ml-3 text-sm font-medium text-gray-900">❌ Não, as atividades não foram realizadas</span>
                    </label>
                </div>
            </div>

            <div>
                <label for="observacoes_finalizacao" class="block text-sm font-semibold text-gray-700 mb-2">
                    Observações sobre a execução <span class="text-red-500">*</span>
                </label>
                <textarea 
                    id="observacoes_finalizacao" 
                    name="observacoes_finalizacao" 
                    rows="4" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 resize-none"
                    placeholder="Descreva como foi a execução das atividades, se houve alguma dificuldade, observações importantes, etc."></textarea>
                <p class="mt-1 text-xs text-gray-500">Mínimo de 20 caracteres</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <button type="button" 
                        onclick="fecharModalFinalizarOS()"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                    Cancelar
                </button>
                <button type="button" 
                        id="btnFinalizar"
                        onclick="finalizarOS()"
                        class="inline-flex items-center gap-2 px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Confirmar Finalização
                </button>
            </div>
        </form>
    </div>
</div>
@endpush
