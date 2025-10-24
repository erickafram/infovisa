@extends('layouts.admin')

@section('title', 'Pactuação - Competências')
@section('page-title', 'Pactuação de Competências')

@section('content')
<div class="max-w-7xl mx-auto" x-data="pactuacaoManager()">
    
    {{-- Informações --}}
    <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-blue-800 mb-1">Como funciona a Pactuação?</h3>
                <p class="text-sm text-blue-700">
                    Configure quais atividades (CNAEs) são de competência <strong>Municipal</strong> ou <strong>Estadual</strong>. 
                    Um estabelecimento será considerado <strong>Estadual</strong> se <strong>pelo menos uma</strong> de suas atividades for estadual.
                    Caso contrário, será <strong>Municipal</strong>.
                </p>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button @click="abaAtiva = 'estadual'" 
                        :class="abaAtiva === 'estadual' ? 'border-orange-500 text-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Competência Estadual
                        <span class="ml-2 bg-orange-100 text-orange-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            {{ count($pactuacoesEstaduais) }}
                        </span>
                    </div>
                </button>
                
                <button @click="abaAtiva = 'municipal'" 
                        :class="abaAtiva === 'municipal' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        Competência Municipal
                        <span class="ml-2 bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                            {{ $municipios->count() }}
                        </span>
                    </div>
                </button>
            </nav>
        </div>
    </div>

    {{-- Conteúdo Estadual --}}
    <div x-show="abaAtiva === 'estadual'" x-cloak>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Atividades de Competência Estadual</h3>
                <button @click="modalAdicionar = true; tipoModal = 'estadual'; municipioModal = null"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Adicionar Atividade
                </button>
            </div>

            @if($pactuacoesEstaduais->isEmpty())
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhuma atividade cadastrada</h3>
                    <p class="mt-1 text-sm text-gray-500">Adicione as atividades que são de competência estadual</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código CNAE</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($pactuacoesEstaduais as $pactuacao)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $pactuacao->cnae_codigo }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $pactuacao->cnae_descricao }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $pactuacao->ativo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $pactuacao->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="toggleStatus({{ $pactuacao->id }})" 
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                        {{ $pactuacao->ativo ? 'Desativar' : 'Ativar' }}
                                    </button>
                                    <button @click="remover({{ $pactuacao->id }})" 
                                            class="text-red-600 hover:text-red-900">
                                        Remover
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Conteúdo Municipal --}}
    <div x-show="abaAtiva === 'municipal'" x-cloak>
        @foreach($municipios as $municipio)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">{{ $municipio }}</h3>
                <button @click="modalAdicionar = true; tipoModal = 'municipal'; municipioModal = '{{ $municipio }}'"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Adicionar Atividade
                </button>
            </div>

            @php
                $pactuacoesMunicipio = $pactuacoesMunicipais->get($municipio, collect());
            @endphp

            @if($pactuacoesMunicipio->isEmpty())
                <div class="text-center py-8 bg-gray-50 rounded-lg">
                    <p class="text-sm text-gray-500">Nenhuma atividade cadastrada para este município</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código CNAE</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($pactuacoesMunicipio as $pactuacao)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $pactuacao->cnae_codigo }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $pactuacao->cnae_descricao }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $pactuacao->ativo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $pactuacao->ativo ? 'Ativo' : 'Inativo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="toggleStatus({{ $pactuacao->id }})" 
                                            class="text-blue-600 hover:text-blue-900 mr-3">
                                        {{ $pactuacao->ativo ? 'Desativar' : 'Ativar' }}
                                    </button>
                                    <button @click="remover({{ $pactuacao->id }})" 
                                            class="text-red-600 hover:text-red-900">
                                        Remover
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Modal Adicionar Atividade --}}
    <div x-show="modalAdicionar" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="modalAdicionar"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="modalAdicionar = false"
                 class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

            <div x-show="modalAdicionar"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-4 py-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-white">
                            <span x-text="tipoModal === 'estadual' ? 'Competência Estadual' : municipioModal"></span>
                        </h3>
                        <button @click="modalAdicionar = false" class="text-white hover:text-gray-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <form @submit.prevent="adicionarAtividades" class="p-4">
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Códigos CNAE
                            <span class="text-xs text-gray-500">(separados por vírgula)</span>
                        </label>
                        <textarea 
                            x-model="cnaesTexto" 
                            rows="3"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Ex: 4711-3/01, 4712-1/00, 4713-0/02"
                            required></textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            Digite os códigos CNAE separados por vírgula. As descrições serão buscadas automaticamente.
                        </p>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" 
                                @click="modalAdicionar = false"
                                class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit"
                                :disabled="processando"
                                class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="!processando">Adicionar</span>
                            <span x-show="processando">Processando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function pactuacaoManager() {
    return {
        abaAtiva: 'estadual',
        modalAdicionar: false,
        tipoModal: 'estadual',
        municipioModal: null,
        cnaesTexto: '',
        processando: false,

        async adicionarAtividades() {
            if (!this.cnaesTexto.trim()) {
                alert('Digite pelo menos um código CNAE');
                return;
            }

            this.processando = true;

            try {
                // Separa os CNAEs por vírgula e limpa espaços
                const cnaes = this.cnaesTexto.split(',').map(c => c.trim()).filter(c => c);
                
                if (cnaes.length === 0) {
                    alert('Nenhum código CNAE válido encontrado');
                    this.processando = false;
                    return;
                }

                // Busca as descrições dos CNAEs
                const atividades = [];
                for (const cnae of cnaes) {
                    // Busca a descrição do CNAE
                    try {
                        const response = await fetch(`{{ route('admin.configuracoes.pactuacao.buscar-cnaes') }}?termo=${cnae}`);
                        const data = await response.json();
                        
                        if (data.length > 0) {
                            // Pega a primeira correspondência exata ou mais próxima
                            const match = data.find(d => d.codigo === cnae) || data[0];
                            atividades.push({
                                codigo: cnae,
                                descricao: match.descricao
                            });
                        } else {
                            // Se não encontrar, usa o código como descrição
                            atividades.push({
                                codigo: cnae,
                                descricao: `Atividade ${cnae}`
                            });
                        }
                    } catch (error) {
                        console.error(`Erro ao buscar CNAE ${cnae}:`, error);
                        atividades.push({
                            codigo: cnae,
                            descricao: `Atividade ${cnae}`
                        });
                    }
                }

                // Envia todas as atividades de uma vez
                const response = await fetch('{{ route('admin.configuracoes.pactuacao.store-multiple') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        tipo: this.tipoModal,
                        municipio: this.municipioModal,
                        atividades: atividades
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao adicionar atividades');
            } finally {
                this.processando = false;
            }
        },

        async toggleStatus(id) {
            if (!confirm('Deseja alterar o status desta atividade?')) return;

            try {
                const response = await fetch(`{{ route('admin.configuracoes.pactuacao.index') }}/${id}/toggle`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao alterar status');
            }
        },

        async remover(id) {
            if (!confirm('Deseja realmente remover esta atividade?')) return;

            try {
                const response = await fetch(`{{ route('admin.configuracoes.pactuacao.index') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('Erro ao remover atividade');
            }
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection
