@extends('layouts.admin')

@section('title', 'Editar Atividade - Equipamentos de Radiação')
@section('page-title', 'Editar Atividade de Radiação')

@section('content')
<div class="max-w-8xl mx-auto space-y-6" x-data="{ obrigatorioProcesso: {{ $atividade->obrigatorio_processo ? 'true' : 'false' }} }">
    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin.configuracoes.equipamentos-radiacao.index') }}" class="hover:text-gray-700">
            Equipamentos de Radiação
        </a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-gray-900 font-medium">Editar</span>
    </div>

    {{-- Header --}}
    <div class="flex items-start justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Editar Atividade</h2>
            <p class="text-sm text-gray-500 mt-1">
                Altere as informações da atividade econômica.
            </p>
        </div>
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $atividade->ativo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
            {{ $atividade->ativo ? 'Ativo' : 'Inativo' }}
        </span>
    </div>

    {{-- Formulário --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <form action="{{ route('admin.configuracoes.equipamentos-radiacao.update', $atividade) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">
                {{-- Código da Atividade --}}
                <div>
                    <label for="codigo_atividade" class="block text-sm font-semibold text-gray-700 mb-2">
                        Código da Atividade (CNAE) <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="codigo_atividade" 
                           id="codigo_atividade"
                           value="{{ old('codigo_atividade', $atividade->codigo_atividade) }}"
                           placeholder="Ex: 8640-2/02"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm @error('codigo_atividade') border-red-500 @enderror"
                           required>
                    @error('codigo_atividade')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Informe o código CNAE da atividade econômica</p>
                </div>

                {{-- Descrição da Atividade --}}
                <div>
                    <label for="descricao_atividade" class="block text-sm font-semibold text-gray-700 mb-2">
                        Descrição da Atividade <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="descricao_atividade" 
                           id="descricao_atividade"
                           value="{{ old('descricao_atividade', $atividade->descricao_atividade) }}"
                           placeholder="Ex: Atividades de serviços de diagnóstico por imagem"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm @error('descricao_atividade') border-red-500 @enderror"
                           required>
                    @error('descricao_atividade')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Observações --}}
                <div>
                    <label for="observacoes" class="block text-sm font-semibold text-gray-700 mb-2">
                        Observações
                    </label>
                    <textarea name="observacoes" 
                              id="observacoes"
                              rows="3"
                              placeholder="Informações adicionais sobre esta atividade..."
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm resize-none @error('observacoes') border-red-500 @enderror">{{ old('observacoes', $atividade->observacoes) }}</textarea>
                    @error('observacoes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status Ativo --}}
                <div class="flex items-center gap-3">
                    <input type="hidden" name="ativo" value="0">
                    <input type="checkbox" 
                           name="ativo" 
                           id="ativo"
                           value="1"
                           class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                           {{ old('ativo', $atividade->ativo) ? 'checked' : '' }}>
                    <label for="ativo" class="text-sm font-medium text-gray-700">
                        Ativo
                        <span class="font-normal text-gray-500">- Estabelecimentos com esta atividade serão obrigados a cadastrar equipamentos</span>
                    </label>
                </div>

                {{-- Obrigatório para abertura de processo --}}
                <div class="border-t border-gray-200 pt-6">
                    <div class="flex items-start gap-3">
                        <input type="hidden" name="obrigatorio_processo" value="0">
                        <input type="checkbox" 
                               name="obrigatorio_processo" 
                               id="obrigatorio_processo"
                               value="1"
                               x-model="obrigatorioProcesso"
                               class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-0.5"
                               {{ old('obrigatorio_processo', $atividade->obrigatorio_processo) ? 'checked' : '' }}>
                        <div>
                            <label for="obrigatorio_processo" class="text-sm font-medium text-gray-700">
                                Obrigatório cadastrar equipamento para abrir processo
                            </label>
                            <p class="text-xs text-gray-500 mt-1">
                                Se marcado, o estabelecimento será obrigado a cadastrar no mínimo um equipamento de radiação antes de abrir o processo selecionado.
                            </p>
                        </div>
                    </div>

                    {{-- Tipos de Processo (aparecem quando obrigatório está marcado) --}}
                    <div x-show="obrigatorioProcesso" x-cloak class="mt-4 ml-8">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Tipos de Processo <span class="text-red-500">*</span>
                        </label>
                        <p class="text-xs text-gray-500 mb-3">
                            Selecione em quais tipos de processo será obrigatório o cadastro de equipamentos.
                        </p>
                        <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-gray-50">
                            @forelse($tiposProcesso as $tipo)
                            <label class="flex items-center gap-3 cursor-pointer hover:bg-white p-2 rounded-lg transition-colors">
                                <input type="checkbox" 
                                       name="tipos_processo[]" 
                                       value="{{ $tipo->id }}"
                                       class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                       {{ in_array($tipo->id, old('tipos_processo', $tiposProcessoSelecionados)) ? 'checked' : '' }}>
                                <div>
                                    <span class="text-sm font-medium text-gray-900">{{ $tipo->nome }}</span>
                                    @if($tipo->codigo)
                                    <span class="text-xs text-gray-500 ml-1">({{ $tipo->codigo }})</span>
                                    @endif
                                </div>
                            </label>
                            @empty
                            <p class="text-sm text-gray-500 text-center py-2">Nenhum tipo de processo estadual cadastrado.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ações --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl flex items-center justify-between">
                {{-- Botão Excluir --}}
                <button type="button" 
                        onclick="document.getElementById('form-delete').submit()"
                        class="px-4 py-2.5 text-red-600 text-sm font-medium bg-white border border-red-300 rounded-lg hover:bg-red-50 transition-colors">
                    Excluir Atividade
                </button>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.configuracoes.equipamentos-radiacao.index') }}" 
                       class="px-4 py-2.5 text-gray-700 text-sm font-medium bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                        Salvar Alterações
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Form de exclusão oculto --}}
    <form id="form-delete" 
          action="{{ route('admin.configuracoes.equipamentos-radiacao.destroy', $atividade) }}" 
          method="POST"
          onsubmit="return confirm('Tem certeza que deseja excluir esta atividade? Esta ação não pode ser desfeita.')">
        @csrf
        @method('DELETE')
    </form>

    {{-- Informações da atividade --}}
    <div class="bg-gray-50 rounded-xl border border-gray-200 p-4">
        <h4 class="text-sm font-semibold text-gray-700 mb-3">Informações do Cadastro</h4>
        <dl class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Cadastrado em</dt>
                <dd class="font-medium text-gray-900">{{ $atividade->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Última atualização</dt>
                <dd class="font-medium text-gray-900">{{ $atividade->updated_at->format('d/m/Y H:i') }}</dd>
            </div>
            @if($atividade->criadoPor)
            <div>
                <dt class="text-gray-500">Cadastrado por</dt>
                <dd class="font-medium text-gray-900">{{ $atividade->criadoPor->name }}</dd>
            </div>
            @endif
        </dl>
    </div>
</div>
@endsection
