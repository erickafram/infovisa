{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Atividades</h3>
        <p class="text-sm text-gray-500">Atividades dentro de cada tipo de serviço (Restaurante, Lanchonete, etc.)</p>
    </div>
    <button @click="$dispatch('open-modal-atividade')" 
            class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nova Atividade
    </button>
</div>

{{-- Filtro por Tipo de Serviço --}}
<div class="bg-gray-50 rounded-lg p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4">
        <input type="hidden" name="tab" value="atividades">
        <div class="w-64">
            <select name="tipo_servico_id" onchange="this.form.submit()"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                <option value="">Todos os tipos de serviço</option>
                @foreach($tiposServico as $tipo)
                <option value="{{ $tipo->id }}" {{ request('tipo_servico_id') == $tipo->id ? 'selected' : '' }}>
                    {{ $tipo->nome }}
                </option>
                @endforeach
            </select>
        </div>
    </form>
</div>

{{-- Tabela --}}
@if($atividades->isEmpty())
<div class="text-center py-8">
    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <p class="text-gray-500">Nenhuma atividade cadastrada</p>
    @if($tiposServico->isEmpty())
    <p class="text-sm text-gray-400 mt-1">Cadastre primeiro um tipo de serviço</p>
    @endif
</div>
@else
<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50 border-y border-gray-200">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nome</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tipo de Serviço</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">CNAE</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Ordem</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($atividades as $atividade)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <span class="text-sm font-medium text-gray-900">{{ $atividade->nome }}</span>
                    @if($atividade->descricao)
                    <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($atividade->descricao, 40) }}</p>
                    @endif
                </td>
                <td class="px-4 py-3">
                    <span class="px-2 py-1 text-xs font-medium bg-violet-100 text-violet-800 rounded-full">
                        {{ $atividade->tipoServico->nome ?? '-' }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <span class="text-sm text-gray-600 font-mono">{{ $atividade->codigo_cnae ?: '-' }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="text-sm text-gray-600">{{ $atividade->ordem }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                    @if($atividade->ativo)
                    <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Ativo</span>
                    @else
                    <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">Inativo</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.configuracoes.atividades.edit', $atividade) }}" 
                           class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                        <form action="{{ route('admin.configuracoes.atividades.destroy', $atividade) }}" method="POST" class="inline"
                              onsubmit="return confirm('Excluir esta atividade?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg" title="Excluir">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

@if($atividades->hasPages())
<div class="mt-4">
    {{ $atividades->appends(['tab' => 'atividades'])->links() }}
</div>
@endif
@endif

{{-- Modal Importar CNAEs --}}
<div x-data="modalImportarCnaes()" 
     @open-modal-atividade.window="open = true"
     x-show="open" 
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             class="fixed inset-0 bg-black/50" @click="open = false"></div>
        
        <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="relative bg-white rounded-xl shadow-xl max-w-3xl w-full p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Importar Atividades por CNAE</h3>
                <button @click="open = false; resetar()" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form action="{{ route('admin.configuracoes.atividades.store-multiple') }}" method="POST">
                @csrf
                
                {{-- Tipo de Serviço --}}
                <div class="mb-4 p-3 bg-emerald-50 rounded-lg border border-emerald-200">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Serviço *</label>
                    <select name="tipo_servico_id" x-model="tipoServicoId" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Selecione o tipo de serviço...</option>
                        @foreach($tiposServico as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->nome }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Campo para digitar CNAEs --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Digite os códigos CNAE</label>
                    <div class="flex gap-2">
                        <input type="text" 
                               x-model="inputCnae"
                               @keydown.enter.prevent="adicionarCnae()"
                               placeholder="Ex: 5611201 ou 56.11-2-01"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <button type="button" 
                                @click="adicionarCnae()"
                                :disabled="!inputCnae || buscando"
                                class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                            <svg x-show="buscando" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="buscando ? 'Buscando...' : 'Adicionar'"></span>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Digite o código CNAE e pressione Enter ou clique em Adicionar. O sistema buscará automaticamente a descrição.
                    </p>
                </div>

                {{-- Importar múltiplos de uma vez --}}
                <div class="mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ou cole vários CNAEs de uma vez</label>
                    <textarea x-model="inputMultiplos"
                              rows="3"
                              placeholder="Cole aqui vários códigos CNAE separados por vírgula, espaço ou quebra de linha. Ex:&#10;5611201, 5611202&#10;5612100"
                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"></textarea>
                    <button type="button" 
                            @click="importarMultiplos()"
                            :disabled="!inputMultiplos || buscando"
                            class="mt-2 px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Importar Todos
                    </button>
                </div>

                {{-- Erro --}}
                <div x-show="erro" x-transition class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-700" x-text="erro"></p>
                </div>

                {{-- Lista de CNAEs adicionados --}}
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-medium text-gray-700">
                            Atividades a serem cadastradas (<span x-text="atividades.length"></span>)
                        </label>
                        <button type="button" 
                                x-show="atividades.length > 0"
                                @click="atividades = []"
                                class="text-xs text-red-600 hover:underline">
                            Limpar tudo
                        </button>
                    </div>
                    
                    <div x-show="atividades.length === 0" class="p-4 bg-gray-50 rounded-lg text-center">
                        <p class="text-sm text-gray-500">Nenhum CNAE adicionado ainda</p>
                    </div>
                    
                    <div x-show="atividades.length > 0" class="space-y-2 max-h-60 overflow-y-auto border border-gray-200 rounded-lg p-2">
                        <template x-for="(atividade, index) in atividades" :key="index">
                            <div class="flex items-center gap-3 p-2 bg-gray-50 rounded-lg">
                                <input type="hidden" :name="'atividades[' + index + '][nome]'" :value="atividade.descricao">
                                <input type="hidden" :name="'atividades[' + index + '][codigo_cnae]'" :value="atividade.codigo">
                                <input type="hidden" :name="'atividades[' + index + '][descricao]'" value="">
                                
                                <span class="px-2 py-1 bg-emerald-100 text-emerald-800 text-xs font-mono font-bold rounded" x-text="atividade.codigo"></span>
                                <span class="flex-1 text-sm text-gray-700" x-text="atividade.descricao"></span>
                                <button type="button" 
                                        @click="atividades.splice(index, 1)"
                                        class="p-1 text-red-500 hover:bg-red-50 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3">
                    <button type="button" @click="open = false; resetar()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancelar
                    </button>
                    <button type="submit" 
                            :disabled="!tipoServicoId || atividades.length === 0"
                            class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        Salvar <span x-text="atividades.length"></span> Atividade(s)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function modalImportarCnaes() {
    return {
        open: false,
        tipoServicoId: '',
        inputCnae: '',
        inputMultiplos: '',
        atividades: [],
        buscando: false,
        erro: '',
        
        // Normaliza o código CNAE para o formato padrão (só números)
        normalizarCnae(codigo) {
            return String(codigo).replace(/[^0-9]/g, '');
        },
        
        // Formata o CNAE para exibição (XX.XX-X-XX)
        formatarCnae(codigo) {
            const num = this.normalizarCnae(codigo);
            if (num.length === 7) {
                return `${num.slice(0,2)}.${num.slice(2,4)}-${num.slice(4,5)}-${num.slice(5,7)}`;
            }
            return num;
        },
        
        async buscarDescricaoCnae(codigo) {
            const codigoNormalizado = this.normalizarCnae(codigo);
            
            if (codigoNormalizado.length < 5) {
                return null;
            }
            
            try {
                // Busca na API do IBGE
                const response = await fetch(`https://servicodados.ibge.gov.br/api/v2/cnae/subclasses/${codigoNormalizado}`);
                
                if (response.ok) {
                    const data = await response.json();
                    if (data && data.id) {
                        return {
                            codigo: codigoNormalizado,
                            descricao: data.descricao
                        };
                    }
                }
                
                // Se não encontrar na API, tenta buscar como classe
                const responseClasse = await fetch(`https://servicodados.ibge.gov.br/api/v2/cnae/classes/${codigoNormalizado.slice(0,5)}`);
                if (responseClasse.ok) {
                    const dataClasse = await responseClasse.json();
                    if (dataClasse && dataClasse.id) {
                        return {
                            codigo: codigoNormalizado,
                            descricao: dataClasse.descricao + ' (classe)'
                        };
                    }
                }
                
                return null;
            } catch (error) {
                console.error('Erro ao buscar CNAE:', error);
                return null;
            }
        },
        
        async adicionarCnae() {
            if (!this.inputCnae || this.buscando) return;
            
            this.erro = '';
            this.buscando = true;
            
            const codigoNormalizado = this.normalizarCnae(this.inputCnae);
            
            // Verifica se já foi adicionado
            if (this.atividades.some(a => a.codigo === codigoNormalizado)) {
                this.erro = 'Este CNAE já foi adicionado';
                this.buscando = false;
                return;
            }
            
            const resultado = await this.buscarDescricaoCnae(codigoNormalizado);
            
            if (resultado) {
                this.atividades.push(resultado);
                this.inputCnae = '';
            } else {
                this.erro = `CNAE ${codigoNormalizado} não encontrado. Verifique se o código está correto.`;
            }
            
            this.buscando = false;
        },
        
        async importarMultiplos() {
            if (!this.inputMultiplos || this.buscando) return;
            
            this.erro = '';
            this.buscando = true;
            
            // Extrai todos os códigos do texto
            const codigos = this.inputMultiplos
                .split(/[\s,;\n]+/)
                .map(c => this.normalizarCnae(c))
                .filter(c => c.length >= 5);
            
            if (codigos.length === 0) {
                this.erro = 'Nenhum código CNAE válido encontrado';
                this.buscando = false;
                return;
            }
            
            let adicionados = 0;
            let naoEncontrados = [];
            
            for (const codigo of codigos) {
                // Pula se já existe
                if (this.atividades.some(a => a.codigo === codigo)) {
                    continue;
                }
                
                const resultado = await this.buscarDescricaoCnae(codigo);
                
                if (resultado) {
                    this.atividades.push(resultado);
                    adicionados++;
                } else {
                    naoEncontrados.push(codigo);
                }
            }
            
            if (naoEncontrados.length > 0) {
                this.erro = `${adicionados} CNAE(s) adicionado(s). Não encontrados: ${naoEncontrados.join(', ')}`;
            }
            
            this.inputMultiplos = '';
            this.buscando = false;
        },
        
        resetar() {
            this.tipoServicoId = '';
            this.inputCnae = '';
            this.inputMultiplos = '';
            this.atividades = [];
            this.erro = '';
        }
    }
}
</script>
