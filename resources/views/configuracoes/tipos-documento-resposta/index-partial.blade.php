@php
    $tiposResposta = \App\Models\TipoDocumentoResposta::ordenado()->get();
@endphp

<div class="flex items-center justify-between mb-4">
    <p class="text-xs text-gray-400">Documentos que o estabelecimento deve enviar como resposta a notificações</p>
    <a href="{{ route('admin.configuracoes.tipos-documento-resposta.create') }}"
       class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Novo Tipo Resposta
    </a>
</div>

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    @forelse($tiposResposta as $tipo)
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50">
        <div class="flex items-center gap-3">
            <span class="w-2 h-2 rounded-full {{ $tipo->ativo ? 'bg-green-500' : 'bg-gray-300' }}"></span>
            <div>
                <p class="text-sm font-medium text-gray-900">{{ $tipo->nome }}</p>
                <div class="flex items-center gap-2 mt-0.5">
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full font-medium
                        {{ $tipo->tipo_setor === 'todos' ? 'bg-gray-100 text-gray-600' : ($tipo->tipo_setor === 'publico' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700') }}">
                        {{ $tipo->tipo_setor === 'todos' ? 'Todos' : ($tipo->tipo_setor === 'publico' ? 'Público' : 'Privado') }}
                    </span>
                    @if($tipo->descricao)
                    <span class="text-xs text-gray-500">{{ Str::limit($tipo->descricao, 60) }}</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.configuracoes.tipos-documento-resposta.edit', $tipo) }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Editar</a>
            <form action="{{ route('admin.configuracoes.tipos-documento-resposta.destroy', $tipo) }}" method="POST" onsubmit="return confirm('Excluir este tipo?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium">Excluir</button>
            </form>
        </div>
    </div>
    @empty
    <div class="p-8 text-center">
        <p class="text-sm text-gray-500 mb-3">Nenhum tipo de documento resposta cadastrado.</p>
        <p class="text-xs text-gray-400">Cadastre tipos como "ROI dos equipamentos", "Prancha do estabelecimento", etc.</p>
    </div>
    @endforelse
</div>
