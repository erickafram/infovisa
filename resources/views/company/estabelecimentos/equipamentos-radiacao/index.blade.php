@extends('layouts.company')

@section('title', 'Equipamentos de Imagem')
@section('page-title', 'Equipamentos de Imagem')

@section('content')
<div class="space-y-6" x-data="equipamentosRadiacao()">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('company.estabelecimentos.index') }}" class="hover:text-gray-700">Estabelecimentos</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <a href="{{ route('company.estabelecimentos.show', $estabelecimento->id) }}" class="hover:text-gray-700">{{ Str::limit($estabelecimento->nome_fantasia ?: $estabelecimento->nome_razao_social, 30) }}</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-900 font-medium">Equipamentos de Imagem</span>
    </div>

    {{-- Alerta: Declaração de Não Possui Equipamentos --}}
    @if($estabelecimento->declaracao_sem_equipamentos_imagem)
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 p-2 bg-amber-100 rounded-lg">
                <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-base font-semibold text-amber-800">Declaração Registrada</h3>
                <p class="text-sm text-amber-700 mt-1">
                    Você declarou que este estabelecimento <strong>não possui equipamentos de imagem</strong>.
                </p>
                @if($estabelecimento->declaracao_sem_equipamentos_imagem_justificativa)
                <div class="mt-3 p-3 bg-white/60 rounded-lg border border-amber-200">
                    <p class="text-xs font-medium text-amber-800 uppercase tracking-wider mb-1">Justificativa informada:</p>
                    <p class="text-sm text-amber-800">{{ $estabelecimento->declaracao_sem_equipamentos_imagem_justificativa }}</p>
                </div>
                @endif
                <div class="flex items-center justify-between mt-4">
                    <p class="text-xs text-amber-600">
                        Declaração registrada em: {{ $estabelecimento->declaracao_sem_equipamentos_imagem_data?->format('d/m/Y \à\s H:i') }}
                    </p>
                    <form action="{{ route('company.estabelecimentos.equipamentos-radiacao.revogar-declaracao', $estabelecimento->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                onclick="return confirm('Deseja revogar esta declaração? Você poderá cadastrar equipamentos ou fazer uma nova declaração.')"
                                class="text-xs text-amber-700 hover:text-amber-900 underline font-medium">
                            Revogar declaração
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Equipamentos de Imagem</h2>
            <p class="text-sm text-gray-500 mt-1">
                Cadastre todos os equipamentos de imagem deste estabelecimento
            </p>
        </div>
        @if(!$estabelecimento->declaracao_sem_equipamentos_imagem)
        <button type="button"
                @click="modalAberto = true; resetForm()"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Novo Equipamento
        </button>
        @endif
    </div>

    {{-- Mensagens --}}
    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    {{-- Lista de Equipamentos --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        @if($equipamentos->count() > 0)
        <div class="overflow-x-auto" style="overflow-y: visible;">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Equipamento
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Marca / Modelo
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Registro ANVISA
                        </th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($equipamentos as $equipamento)
                    <tr class="hover:bg-gray-50 transition-colors" style="position: relative;">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $equipamento->tipo_equipamento }}</div>
                                    @if($equipamento->numero_serie)
                                    <div class="text-xs text-gray-500">Nº Série: {{ $equipamento->numero_serie }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $equipamento->fabricante }}</div>
                            <div class="text-xs text-gray-500">{{ $equipamento->modelo }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-sm text-gray-900">{{ $equipamento->registro_anvisa }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'ativo' => 'bg-green-100 text-green-800',
                                    'inativo' => 'bg-gray-100 text-gray-800',
                                    'em_manutencao' => 'bg-yellow-100 text-yellow-800',
                                    'descartado' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <div x-data="{ statusAberto: false }" class="relative" style="position: static;">
                                <button type="button" 
                                        @click="statusAberto = !statusAberto"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $statusColors[$equipamento->status] ?? 'bg-gray-100 text-gray-800' }} hover:ring-2 hover:ring-offset-1 hover:ring-gray-300 transition-all cursor-pointer">
                                    {{ $statusOptions[$equipamento->status] ?? $equipamento->status }}
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                
                                {{-- Dropdown de Status --}}
                                <div x-show="statusAberto" 
                                     @click.away="statusAberto = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="fixed bg-white rounded-lg shadow-xl border border-gray-200 py-1 z-50 w-44"
                                     style="display: none;"
                                     @click.stop>
                                    <p class="px-3 py-1.5 text-xs font-semibold text-gray-500 uppercase">Alterar Status</p>
                                    @foreach($statusOptions as $statusValue => $statusLabel)
                                    @if($statusValue !== $equipamento->status)
                                    <form action="{{ route('company.estabelecimentos.equipamentos-radiacao.update-status', [$estabelecimento->id, $equipamento->id]) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="{{ $statusValue }}">
                                        <button type="submit" class="w-full px-3 py-2 text-left text-sm hover:bg-gray-50 flex items-center gap-2">
                                            <span class="w-2 h-2 rounded-full {{ str_replace(['text-', '100'], ['bg-', '500'], $statusColors[$statusValue] ?? 'bg-gray-500') }}"></span>
                                            {{ $statusLabel }}
                                        </button>
                                    </form>
                                    @endif
                                    @endforeach
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Editar --}}
                                <button type="button"
                                        @click="editarEquipamento({{ json_encode($equipamento) }})"
                                        class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                                        title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>

                                {{-- Excluir --}}
                                <form action="{{ route('company.estabelecimentos.equipamentos-radiacao.destroy', [$estabelecimento->id, $equipamento->id]) }}" 
                                      method="POST" 
                                      class="inline"
                                      onsubmit="return confirm('Tem certeza que deseja excluir este equipamento?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                            title="Excluir">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        @if(!$estabelecimento->declaracao_sem_equipamentos_imagem)
        <div class="text-center py-12 px-6">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-gray-900">Nenhum equipamento cadastrado</h3>
            <p class="mt-1 text-sm text-gray-500 max-w-md mx-auto">
                Este estabelecimento possui atividades que exigem equipamentos de imagem. 
                Cadastre os equipamentos ou declare que não possui.
            </p>
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                <button type="button"
                        @click="modalAberto = true; resetForm()"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Cadastrar Equipamento
                </button>
                <button type="button"
                        @click="modalDeclaracaoAberto = true"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-amber-100 text-amber-800 text-sm font-medium rounded-lg hover:bg-amber-200 transition-colors border border-amber-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Não possuo equipamentos
                </button>
            </div>
        </div>
        @else
        <div class="text-center py-12 px-6">
            <svg class="mx-auto h-12 w-12 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-gray-900">Declaração de não possui equipamentos</h3>
            <p class="mt-1 text-sm text-gray-500 max-w-md mx-auto">
                Você declarou que este estabelecimento não possui equipamentos de imagem. 
                Se isso mudou, revogue a declaração acima para cadastrar equipamentos.
            </p>
        </div>
        @endif
        @endif
    </div>

    {{-- Modal de Cadastro/Edição --}}
    <div x-show="modalAberto" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            {{-- Overlay --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="modalAberto = false"></div>

            {{-- Modal --}}
            <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full mx-auto z-10"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900" x-text="editando ? 'Editar Equipamento' : 'Novo Equipamento'"></h3>
                </div>

                {{-- Form --}}
                <form :action="editando ? '{{ route('company.estabelecimentos.equipamentos-radiacao.index', $estabelecimento->id) }}/' + equipamentoId : '{{ route('company.estabelecimentos.equipamentos-radiacao.store', $estabelecimento->id) }}'" 
                      method="POST">
                    @csrf
                    <input type="hidden" name="_method" :value="editando ? 'PUT' : 'POST'">
                    
                    <div class="px-6 py-4 space-y-4 max-h-[60vh] overflow-y-auto">
                        {{-- Tipo de Equipamento com pesquisa --}}
                        <div x-data="{ 
                            open: false, 
                            search: '', 
                            tipos: {{ json_encode($tiposEquipamento) }},
                            get filteredTipos() {
                                if (this.search === '') return this.tipos;
                                return this.tipos.filter(t => t.toLowerCase().includes(this.search.toLowerCase()));
                            },
                            selectTipo(tipo) {
                                this.form.tipo_equipamento = tipo;
                                this.search = '';
                                this.open = false;
                            }
                        }">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Tipo de Equipamento <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                {{-- Campo de seleção --}}
                                <button type="button"
                                        @click="open = !open"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm text-left bg-white flex items-center justify-between">
                                    <span :class="form.tipo_equipamento ? 'text-gray-900' : 'text-gray-500'" x-text="form.tipo_equipamento || 'Selecione o tipo...'"></span>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                                
                                {{-- Input hidden para envio do form --}}
                                <input type="hidden" name="tipo_equipamento" x-model="form.tipo_equipamento" required>
                                
                                {{-- Dropdown --}}
                                <div x-show="open" 
                                     @click.away="open = false"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute z-50 mt-1 w-full bg-white rounded-lg shadow-lg border border-gray-200 max-h-60 overflow-hidden"
                                     style="display: none;">
                                    {{-- Campo de busca --}}
                                    <div class="p-2 border-b border-gray-200 sticky top-0 bg-white">
                                        <div class="relative">
                                            <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                            </svg>
                                            <input type="text" 
                                                   x-model="search"
                                                   @click.stop
                                                   placeholder="Buscar tipo de equipamento..."
                                                   class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        </div>
                                    </div>
                                    
                                    {{-- Lista de opções --}}
                                    <div class="overflow-y-auto max-h-48">
                                        <template x-for="tipo in filteredTipos" :key="tipo">
                                            <button type="button"
                                                    @click="selectTipo(tipo)"
                                                    class="w-full px-4 py-2.5 text-left text-sm hover:bg-blue-50 flex items-center gap-2 transition-colors"
                                                    :class="form.tipo_equipamento === tipo ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700'">
                                                <svg x-show="form.tipo_equipamento === tipo" class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span x-show="form.tipo_equipamento !== tipo" class="w-4"></span>
                                                <span x-text="tipo"></span>
                                            </button>
                                        </template>
                                        <div x-show="filteredTipos.length === 0" class="px-4 py-3 text-sm text-gray-500 text-center">
                                            Nenhum tipo encontrado
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Marca/Fabricante --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Marca / Fabricante <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="fabricante"
                                   x-model="form.fabricante"
                                   placeholder="Ex: SIEMENS, GE HEALTHCARE, PHILIPS..."
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm uppercase"
                                   required>
                        </div>

                        {{-- Modelo --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Modelo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="modelo"
                                   x-model="form.modelo"
                                   placeholder="Ex: MOBILETT XP DIGITAL..."
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm uppercase"
                                   required>
                        </div>

                        {{-- Registro ANVISA --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Nº do Registro MS/ANVISA <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="registro_anvisa"
                                   x-model="form.registro_anvisa"
                                   placeholder="Ex: 80219800001"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm uppercase"
                                   required>
                            <p class="mt-1 text-xs text-gray-500">Número do registro do equipamento na ANVISA</p>
                        </div>

                        {{-- Número de Série (opcional) --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Número de Série
                            </label>
                            <input type="text" 
                                   name="numero_serie"
                                   x-model="form.numero_serie"
                                   placeholder="NÚMERO DE SÉRIE DO EQUIPAMENTO (OPCIONAL)"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm uppercase">
                        </div>

                        {{-- Ano de Fabricação (opcional) --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Ano de Fabricação
                            </label>
                            <input type="number" 
                                   name="ano_fabricacao"
                                   x-model="form.ano_fabricacao"
                                   placeholder="Ex: 2020"
                                   min="1950"
                                   max="{{ date('Y') }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        </div>

                        {{-- Setor/Localização (opcional) --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Setor / Localização
                            </label>
                            <input type="text" 
                                   name="setor_localizacao"
                                   x-model="form.setor_localizacao"
                                   placeholder="Ex: SALA DE RAIO-X, CONSULTÓRIO 1..."
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm uppercase">
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status" 
                                    x-model="form.status"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                                @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Informe a situação atual do equipamento</p>
                        </div>

                        {{-- Observações (opcional) --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Observações
                            </label>
                            <textarea name="observacoes"
                                      x-model="form.observacoes"
                                      rows="2"
                                      placeholder="OBSERVAÇÕES ADICIONAIS SOBRE O EQUIPAMENTO..."
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm resize-none uppercase"></textarea>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl flex items-center justify-end gap-3">
                        <button type="button"
                                @click="modalAberto = false"
                                class="px-4 py-2.5 text-gray-700 text-sm font-medium bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                            <span x-text="editando ? 'Salvar Alterações' : 'Cadastrar'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal de Declaração de Não Possui Equipamentos --}}
    <div x-show="modalDeclaracaoAberto" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            {{-- Overlay --}}
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="modalDeclaracaoAberto = false"></div>

            {{-- Modal --}}
            <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full mx-auto z-10" x-data="{ justificativa: '', confirmado: false }">
                <form action="{{ route('company.estabelecimentos.equipamentos-radiacao.declarar-sem-equipamentos', $estabelecimento->id) }}" method="POST">
                    @csrf
                    
                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Declaração de Não Possui Equipamentos</h3>
                        <button type="button" 
                                @click="modalDeclaracaoAberto = false"
                                class="text-gray-400 hover:text-gray-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="px-6 py-5 space-y-5">
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                            <div class="flex gap-3">
                                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div class="text-sm text-amber-800">
                                    <p class="font-medium mb-1">Atenção!</p>
                                    <p>Este estabelecimento possui atividades econômicas que normalmente exigem equipamentos de imagem. Ao fazer esta declaração, você confirma que:</p>
                                    <ul class="list-disc list-inside mt-2 space-y-1">
                                        <li>Não possui nenhum equipamento de imagem (Raio-X, Tomógrafo, etc.)</li>
                                        <li>Não executa atividades que utilizam esses equipamentos no local</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Justificativa <span class="text-red-500">*</span>
                            </label>
                            <textarea name="justificativa"
                                      x-model="justificativa"
                                      required
                                      minlength="30"
                                      rows="4"
                                      placeholder="Explique por que este estabelecimento não possui equipamentos de imagem, mesmo tendo atividades que normalmente exigem..."
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 text-sm resize-none"
                                      :class="{ 'border-red-300': justificativa.length > 0 && justificativa.length < 30, 'border-green-300': justificativa.length >= 30 }"></textarea>
                            <div class="flex justify-between items-center mt-1">
                                <p class="text-xs text-gray-500">Ex: "Somente realizo consultas clínicas, os exames de imagem são terceirizados"</p>
                                <span class="text-xs" :class="justificativa.length >= 30 ? 'text-green-600' : 'text-red-500'">
                                    <span x-text="justificativa.length"></span>/30 caracteres
                                </span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <input type="checkbox" 
                                   name="confirmacao"
                                   id="confirmacao-declaracao"
                                   x-model="confirmado"
                                   required
                                   class="mt-1 h-4 w-4 text-amber-600 focus:ring-amber-500 border-gray-300 rounded">
                            <label for="confirmacao-declaracao" class="text-sm text-gray-700">
                                Declaro sob minha responsabilidade que as informações acima são verdadeiras e estou ciente de que declarações falsas podem acarretar sanções legais.
                            </label>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl flex items-center justify-end gap-3">
                        <button type="button"
                                @click="modalDeclaracaoAberto = false"
                                class="px-4 py-2.5 text-gray-700 text-sm font-medium bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                :disabled="justificativa.length < 30 || !confirmado"
                                :class="justificativa.length >= 30 && confirmado ? 'bg-amber-600 hover:bg-amber-700 cursor-pointer' : 'bg-gray-300 cursor-not-allowed'"
                                class="px-6 py-2.5 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                            Confirmar Declaração
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function equipamentosRadiacao() {
    return {
        modalAberto: false,
        modalDeclaracaoAberto: false,
        editando: false,
        equipamentoId: null,
        form: {
            tipo_equipamento: '',
            fabricante: '',
            modelo: '',
            registro_anvisa: '',
            numero_serie: '',
            ano_fabricacao: '',
            setor_localizacao: '',
            status: 'ativo',
            observacoes: ''
        },

        resetForm() {
            this.editando = false;
            this.equipamentoId = null;
            this.form = {
                tipo_equipamento: '',
                fabricante: '',
                modelo: '',
                registro_anvisa: '',
                numero_serie: '',
                ano_fabricacao: '',
                setor_localizacao: '',
                status: 'ativo',
                observacoes: ''
            };
        },

        editarEquipamento(equipamento) {
            this.editando = true;
            this.equipamentoId = equipamento.id;
            this.form = {
                tipo_equipamento: equipamento.tipo_equipamento || '',
                fabricante: equipamento.fabricante || '',
                modelo: equipamento.modelo || '',
                registro_anvisa: equipamento.registro_anvisa || '',
                numero_serie: equipamento.numero_serie || '',
                ano_fabricacao: equipamento.ano_fabricacao || '',
                setor_localizacao: equipamento.setor_localizacao || '',
                status: equipamento.status || 'ativo',
                observacoes: equipamento.observacoes || ''
            };
            this.modalAberto = true;
        }
    };
}
</script>
@endsection
