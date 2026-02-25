@extends('layouts.admin')

@section('title', 'Editar Atividade - Responsável Técnico')
@section('page-title', 'Editar Atividade com RT Obrigatório')

@section('content')
<div class="max-w-8xl mx-auto space-y-6">
    <div class="flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('admin.configuracoes.responsaveis-tecnicos.index') }}" class="hover:text-gray-700">Responsável Técnico</a>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-900 font-medium">Editar Atividade</span>
    </div>

    <div>
        <h2 class="text-xl font-bold text-gray-900">Editar Atividade</h2>
        <p class="text-sm text-gray-500 mt-1">Atualize os dados da atividade que exige responsável técnico.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <form action="{{ route('admin.configuracoes.responsaveis-tecnicos.update', $atividade) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">
                <div>
                    <label for="codigo_atividade" class="block text-sm font-semibold text-gray-700 mb-2">Código da Atividade (CNAE) <span class="text-red-500">*</span></label>
                    <input type="text" name="codigo_atividade" id="codigo_atividade" value="{{ old('codigo_atividade', $atividade->codigo_atividade) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm @error('codigo_atividade') border-red-500 @enderror" required>
                    @error('codigo_atividade')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="descricao_atividade" class="block text-sm font-semibold text-gray-700 mb-2">Descrição da Atividade <span class="text-red-500">*</span></label>
                    <input type="text" name="descricao_atividade" id="descricao_atividade" value="{{ old('descricao_atividade', $atividade->descricao_atividade) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm @error('descricao_atividade') border-red-500 @enderror" required>
                    @error('descricao_atividade')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="observacoes" class="block text-sm font-semibold text-gray-700 mb-2">Observações</label>
                    <textarea name="observacoes" id="observacoes" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm resize-none @error('observacoes') border-red-500 @enderror">{{ old('observacoes', $atividade->observacoes) }}</textarea>
                    @error('observacoes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="flex items-center gap-3">
                    <input type="hidden" name="ativo" value="0">
                    <input type="checkbox" name="ativo" id="ativo" value="1" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" {{ old('ativo', $atividade->ativo) ? 'checked' : '' }}>
                    <label for="ativo" class="text-sm font-medium text-gray-700">Ativo</label>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-xl flex items-center justify-between gap-3">
                <button type="submit"
                        form="form-excluir-atividade-rt"
                        class="px-4 py-2.5 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors"
                        onclick="return confirm('Tem certeza que deseja excluir esta atividade?')">
                    Excluir
                </button>

                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.configuracoes.responsaveis-tecnicos.index') }}" class="px-4 py-2.5 text-gray-700 text-sm font-medium bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancelar</a>
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors shadow-sm">Salvar Alterações</button>
                </div>
            </div>
        </form>

        <form id="form-excluir-atividade-rt" action="{{ route('admin.configuracoes.responsaveis-tecnicos.destroy', $atividade) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>
@endsection
