@extends('layouts.admin')

@section('title', 'Editar Tipo de Documento Obrigatório')
@section('page-title', 'Editar Tipo de Documento Obrigatório')

@section('content')
<div class="max-w-8xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.configuracoes.tipos-documento-obrigatorio.index') }}" 
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para Tipos de Documento
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form action="{{ route('admin.configuracoes.tipos-documento-obrigatorio.update', $tipo) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-5">
                <div>
                    <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="nome" id="nome" value="{{ old('nome', $tipo->nome) }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nome') border-red-500 @enderror">
                    @error('nome')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <textarea name="descricao" id="descricao" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('descricao') border-red-500 @enderror">{{ old('descricao', $tipo->descricao) }}</textarea>
                    @error('descricao')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ordem" class="block text-sm font-medium text-gray-700 mb-1">Ordem de Exibição</label>
                    <input type="number" name="ordem" id="ordem" value="{{ old('ordem', $tipo->ordem) }}" min="0"
                           class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Menor número aparece primeiro</p>
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" name="ativo" id="ativo" value="1" {{ old('ativo', $tipo->ativo) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="ativo" class="text-sm text-gray-700">Ativo</label>
                </div>

                {{-- Seção: Documento Comum --}}
                <div class="border-t border-gray-200 pt-5">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Configurações de Documento Comum</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="documento_comum" id="documento_comum" value="1" {{ old('documento_comum', $tipo->documento_comum) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5"
                                   onchange="document.getElementById('tipo_processo_container').style.display = this.checked ? 'block' : 'none'">
                            <div>
                                <label for="documento_comum" class="text-sm font-medium text-gray-700">Documento Comum a Todos os Serviços</label>
                                <p class="text-xs text-gray-500 mt-1">Se marcado, este documento será obrigatório para TODOS os serviços do tipo de processo selecionado.</p>
                            </div>
                        </div>

                        <div id="tipo_processo_container" style="display: {{ old('documento_comum', $tipo->documento_comum) ? 'block' : 'none' }}">
                            <label for="tipo_processo_id" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Processo *</label>
                            <select name="tipo_processo_id" id="tipo_processo_id"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione o tipo de processo...</option>
                                @foreach(\App\Models\TipoProcesso::where('ativo', true)->orderBy('nome')->get() as $tipoProcesso)
                                <option value="{{ $tipoProcesso->id }}" {{ old('tipo_processo_id', $tipo->tipo_processo_id) == $tipoProcesso->id ? 'selected' : '' }}>{{ $tipoProcesso->nome }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Selecione para qual tipo de processo este documento comum se aplica</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="escopo_competencia" class="block text-sm font-medium text-gray-700 mb-1">Escopo de Competência</label>
                                <select name="escopo_competencia" id="escopo_competencia"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="todos" {{ old('escopo_competencia', $tipo->escopo_competencia) === 'todos' ? 'selected' : '' }}>Todos (Estadual + Municipal)</option>
                                    <option value="estadual" {{ old('escopo_competencia', $tipo->escopo_competencia) === 'estadual' ? 'selected' : '' }}>Apenas Estadual</option>
                                    <option value="municipal" {{ old('escopo_competencia', $tipo->escopo_competencia) === 'municipal' ? 'selected' : '' }}>Apenas Municipal</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Define se aplica a processos estaduais, municipais ou ambos</p>
                            </div>

                            <div>
                                <label for="tipo_setor" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Setor</label>
                                <select name="tipo_setor" id="tipo_setor"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="todos" {{ old('tipo_setor', $tipo->tipo_setor) === 'todos' ? 'selected' : '' }}>Todos (Público + Privado)</option>
                                    <option value="publico" {{ old('tipo_setor', $tipo->tipo_setor) === 'publico' ? 'selected' : '' }}>Apenas Público</option>
                                    <option value="privado" {{ old('tipo_setor', $tipo->tipo_setor) === 'privado' ? 'selected' : '' }}>Apenas Privado</option>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Define se aplica a estabelecimentos públicos, privados ou ambos</p>
                            </div>
                        </div>

                        <div>
                            <label for="prazo_validade_dias" class="block text-sm font-medium text-gray-700 mb-1">Prazo de Validade (dias)</label>
                            <input type="number" name="prazo_validade_dias" id="prazo_validade_dias" value="{{ old('prazo_validade_dias', $tipo->prazo_validade_dias) }}" min="1"
                                   placeholder="Ex: 30 para CNPJ"
                                   class="w-32 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Opcional. Ex: CNPJ com data de impressão de até 30 dias</p>
                        </div>

                        <div>
                            <label for="observacao_publica" class="block text-sm font-medium text-gray-700 mb-1">Observação para Estabelecimentos Públicos</label>
                            <textarea name="observacao_publica" id="observacao_publica" rows="2"
                                      placeholder="Ex: Isento para estabelecimentos públicos"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('observacao_publica', $tipo->observacao_publica) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Observação específica mostrada para estabelecimentos públicos</p>
                        </div>

                        <div>
                            <label for="observacao_privada" class="block text-sm font-medium text-gray-700 mb-1">Observação para Estabelecimentos Privados</label>
                            <textarea name="observacao_privada" id="observacao_privada" rows="2"
                                      placeholder="Ex: Apenas para empresas privadas"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('observacao_privada', $tipo->observacao_privada) }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Observação específica mostrada para estabelecimentos privados</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200 flex items-center justify-end gap-3">
                <a href="{{ route('admin.configuracoes.tipos-documento-obrigatorio.index') }}" 
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
