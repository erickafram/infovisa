@extends('layouts.admin')

@section('title', 'Editar Ação')
@section('page-title', 'Editar Ação')

@section('content')
<div class="max-w-8xl mx-auto space-y-6">
    {{-- Formulário Principal --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900">Editar Tipo de Ação</h2>
            <p class="mt-1 text-sm text-gray-600">Atualize os dados da ação</p>
        </div>

        <form action="{{ route('admin.configuracoes.tipo-acoes.update', $tipoAcao) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-5">
                <label for="descricao" class="block text-sm font-medium text-gray-700 mb-2">
                    Descrição <span class="text-red-500">*</span>
                </label>
                <input type="text" id="descricao" name="descricao" 
                       value="{{ old('descricao', $tipoAcao->descricao) }}"
                       required maxlength="255"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-5">
                <label for="codigo_procedimento" class="block text-sm font-medium text-gray-700 mb-2">
                    Código do Procedimento <span class="text-red-500">*</span>
                </label>
                <input type="text" id="codigo_procedimento" name="codigo_procedimento" 
                       value="{{ old('codigo_procedimento', $tipoAcao->codigo_procedimento) }}"
                       required maxlength="255"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono">
            </div>

            <div class="mb-5">
                <label for="competencia" class="block text-sm font-medium text-gray-700 mb-2">
                    Competência <span class="text-red-500">*</span>
                </label>
                <select id="competencia" name="competencia" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="">Selecione...</option>
                    <option value="estadual" {{ old('competencia', $tipoAcao->competencia) === 'estadual' ? 'selected' : '' }}>Estadual</option>
                    <option value="municipal" {{ old('competencia', $tipoAcao->competencia) === 'municipal' ? 'selected' : '' }}>Municipal</option>
                    <option value="ambos" {{ old('competencia', $tipoAcao->competencia) === 'ambos' ? 'selected' : '' }}>Ambos</option>
                </select>
            </div>

            <div class="mb-6 space-y-3">
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="atividade_sia" value="1"
                           {{ old('atividade_sia', $tipoAcao->atividade_sia) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <span class="text-sm text-gray-700">Atividade SIA</span>
                </label>
                <label class="flex items-center gap-3">
                    <input type="checkbox" name="ativo" value="1"
                           {{ old('ativo', $tipoAcao->ativo) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                    <span class="text-sm text-gray-700">Ativo</span>
                </label>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('admin.configuracoes.tipo-acoes.index') }}" 
                   class="flex-1 px-4 py-3 text-center text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit" class="flex-1 px-4 py-3 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    Salvar
                </button>
            </div>
        </form>
    </div>

    {{-- SEÇÃO DE SUBAÇÕES --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-white">Subações</h3>
                <p class="text-sm text-indigo-100">Cadastre subações para detalhar esta ação</p>
            </div>
            <button type="button" onclick="document.getElementById('modal-subacao').classList.remove('hidden')"
                    class="px-4 py-2 bg-white text-indigo-600 text-sm font-semibold rounded-lg hover:bg-indigo-50">
                + Nova Subação
            </button>
        </div>

        <div class="p-6">
            @if($tipoAcao->subAcoes->isEmpty())
                <div class="text-center py-8 text-gray-500">
                    <p class="font-medium">Nenhuma subação cadastrada</p>
                    <p class="text-sm">O técnico executará a ação diretamente.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($tipoAcao->subAcoes as $subAcao)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center text-sm font-bold">{{ $subAcao->ordem }}</span>
                            <div>
                                <div class="font-medium text-gray-900">{{ $subAcao->descricao }}</div>
                                @if($subAcao->codigo_procedimento)
                                    <div class="text-xs text-gray-500 font-mono">{{ $subAcao->codigo_procedimento }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 text-xs rounded {{ $subAcao->ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $subAcao->ativo ? 'Ativo' : 'Inativo' }}
                            </span>
                            <button type="button" onclick="editarSub({{ $subAcao->id }}, '{{ addslashes($subAcao->descricao) }}', '{{ $subAcao->codigo_procedimento }}', {{ $subAcao->ordem }}, {{ $subAcao->ativo ? 'true' : 'false' }})"
                                    class="text-blue-600 hover:underline text-sm">Editar</button></div>
                            <form action="{{ route('admin.configuracoes.tipo-acoes.sub-acoes.destroy', [$tipoAcao, $subAcao]) }}" method="POST" class="inline" onsubmit="return confirm('Excluir?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-sm">Excluir</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="px-6 pb-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-700">
                <strong>Como funciona:</strong> Se a ação tem subações, o usuário escolhe a subação na OS. O técnico verá apenas a subação selecionada.
            </div>
        </div>
    </div>
</div>


{{-- Modal Nova/Editar Subação --}}
<div id="modal-subacao" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modal-titulo" class="text-lg font-bold">Nova Subação</h3>
            <button onclick="fecharModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <form id="form-subacao" action="{{ route('admin.configuracoes.tipo-acoes.sub-acoes.store', $tipoAcao) }}" method="POST">
            @csrf
            <div id="method-field"></div>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição *</label>
                    <input type="text" name="descricao" id="sub_descricao" required maxlength="255"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Código (opcional)</label>
                    <input type="text" name="codigo_procedimento" id="sub_codigo" maxlength="255"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 font-mono">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ordem</label>
                    <input type="number" name="ordem" id="sub_ordem" min="0" value="{{ $tipoAcao->subAcoes->max('ordem') + 1 }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="ativo" id="sub_ativo" value="1" checked class="h-4 w-4 text-indigo-600 rounded">
                    <span class="text-sm text-gray-700">Ativo</span>
                </label>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="fecharModal()" class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancelar</button>
                <button type="submit" class="flex-1 px-4 py-2 text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script>
function fecharModal() {
    document.getElementById('modal-subacao').classList.add('hidden');
    // Reset form
    document.getElementById('form-subacao').action = '{{ route("admin.configuracoes.tipo-acoes.sub-acoes.store", $tipoAcao) }}';
    document.getElementById('method-field').innerHTML = '';
    document.getElementById('modal-titulo').textContent = 'Nova Subação';
    document.getElementById('sub_descricao').value = '';
    document.getElementById('sub_codigo').value = '';
    document.getElementById('sub_ordem').value = '{{ $tipoAcao->subAcoes->max("ordem") + 1 }}';
    document.getElementById('sub_ativo').checked = true;
}

function editarSub(id, descricao, codigo, ordem, ativo) {
    document.getElementById('modal-titulo').textContent = 'Editar Subação';
    document.getElementById('form-subacao').action = '{{ url("admin/configuracoes/tipo-acoes") }}/{{ $tipoAcao->id }}/sub-acoes/' + id;
    document.getElementById('method-field').innerHTML = '@method("PUT")';
    document.getElementById('sub_descricao').value = descricao;
    document.getElementById('sub_codigo').value = codigo || '';
    document.getElementById('sub_ordem').value = ordem;
    document.getElementById('sub_ativo').checked = ativo;
    document.getElementById('modal-subacao').classList.remove('hidden');
}
</script>
@endsection
