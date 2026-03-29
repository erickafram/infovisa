@extends('layouts.admin')

@section('title', 'Tipos de Documento')
@section('page-title', 'Tipos de Documento')

@section('content')
<div class="max-w-8xl mx-auto" x-data="tiposDocumento()">
    {{-- Breadcrumb --}}
    <div class="mb-4">
        <nav class="flex items-center gap-2 text-xs text-gray-500">
            <a href="{{ route('admin.configuracoes.index') }}" class="hover:text-blue-600">Configurações</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="text-gray-900 font-medium">Tipos de Documento</span>
        </nav>
    </div>

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <p class="text-xs text-gray-400">Arraste para reordenar ou ordene de A-Z</p>
        </div>
        <div class="flex items-center gap-2">
            <button @click="ordenarAZ()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                Ordenar A-Z
            </button>
            <a href="{{ route('admin.configuracoes.tipos-documento.create') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Novo Tipo
            </a>
        </div>
    </div>

    {{-- Mensagens --}}
    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-lg px-4 py-2.5 flex items-center gap-2">
        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <p class="text-xs font-medium text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Status de salvamento --}}
    <div x-show="salvando" x-cloak class="mb-3 bg-blue-50 border border-blue-200 rounded-lg px-4 py-2 flex items-center gap-2">
        <svg class="w-4 h-4 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
        <p class="text-xs text-blue-700">Salvando ordem...</p>
    </div>
    <div x-show="salvo" x-cloak x-transition class="mb-3 bg-green-50 border border-green-200 rounded-lg px-4 py-2 flex items-center gap-2">
        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <p class="text-xs text-green-700">Ordem salva!</p>
    </div>

    @if($tipos->count() > 0)
    {{-- Lista com drag-and-drop --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-200 grid grid-cols-12 gap-2 text-[10px] font-semibold text-gray-500 uppercase tracking-wider">
            <div class="col-span-1">#</div>
            <div class="col-span-4">Nome</div>
            <div class="col-span-2">Código</div>
            <div class="col-span-1">Status</div>
            <div class="col-span-1">Nível</div>
            <div class="col-span-1">Prazo</div>
            <div class="col-span-2 text-right">Ações</div>
        </div>
        <div id="listaTipos" class="divide-y divide-gray-100">
            @foreach($tipos as $tipo)
            <div class="grid grid-cols-12 gap-2 items-center px-4 py-2.5 hover:bg-gray-50 transition cursor-grab active:cursor-grabbing active:bg-blue-50 group"
                 data-id="{{ $tipo->id }}" data-nome="{{ $tipo->nome }}">
                {{-- Drag handle + Ordem --}}
                <div class="col-span-1 flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-gray-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm6 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4z"/></svg>
                    <span class="text-[11px] text-gray-400 font-mono ordem-label">{{ $tipo->ordem }}</span>
                </div>
                {{-- Nome --}}
                <div class="col-span-4 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $tipo->nome }}</p>
                    @if($tipo->descricao)
                    <p class="text-[11px] text-gray-400 truncate">{{ Str::limit($tipo->descricao, 50) }}</p>
                    @endif
                </div>
                {{-- Código --}}
                <div class="col-span-2">
                    <code class="text-[11px] px-1.5 py-0.5 bg-gray-100 rounded text-gray-600">{{ $tipo->codigo }}</code>
                </div>
                {{-- Status --}}
                <div class="col-span-1">
                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full {{ $tipo->ativo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $tipo->ativo ? 'Ativo' : 'Inativo' }}
                    </span>
                </div>
                {{-- Nível/Visibilidade --}}
                <div class="col-span-1">
                    @if($tipo->visibilidade === 'estadual')
                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-purple-100 text-purple-700">Estadual</span>
                    @elseif($tipo->visibilidade === 'municipal')
                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-blue-100 text-blue-700">Municipal</span>
                    @else
                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-gray-100 text-gray-500">Todos</span>
                    @endif
                </div>
                {{-- Prazo --}}
                <div class="col-span-1">
                    @if($tipo->tem_prazo)
                    <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $tipo->prazo_padrao_dias }}d</span>
                    @else
                    <span class="text-[10px] text-gray-300">—</span>
                    @endif
                </div>
                <div class="col-span-2 flex items-center justify-end gap-1">
                    <a href="{{ route('admin.configuracoes.tipos-documento.edit', $tipo->id) }}"
                       class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </a>
                    <form action="{{ route('admin.configuracoes.tipos-documento.destroy', $tipo->id) }}" method="POST"
                          onsubmit="return confirm('Remover este tipo de documento?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Remover">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <p class="text-[11px] text-gray-400 mt-2">{{ $tipos->count() }} tipos cadastrados</p>
    @else
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <p class="text-sm text-gray-500 mb-4">Nenhum tipo cadastrado</p>
        <a href="{{ route('admin.configuracoes.tipos-documento.create') }}" class="text-sm text-blue-600 font-medium hover:underline">Criar primeiro tipo →</a>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
function tiposDocumento() {
    return {
        salvando: false,
        salvo: false,
        sortable: null,

        init() {
            const el = document.getElementById('listaTipos');
            if (!el) return;
            this.sortable = Sortable.create(el, {
                animation: 200,
                ghostClass: 'bg-blue-50',
                chosenClass: 'shadow-lg',
                dragClass: 'opacity-50',
                handle: '[data-id]',
                onEnd: () => this.salvarOrdem()
            });
        },

        async salvarOrdem() {
            const items = document.querySelectorAll('#listaTipos [data-id]');
            const ordem = Array.from(items).map(el => parseInt(el.dataset.id));

            // Atualiza labels visuais
            items.forEach((el, i) => {
                const label = el.querySelector('.ordem-label');
                if (label) label.textContent = i + 1;
            });

            this.salvando = true;
            this.salvo = false;
            try {
                const r = await fetch('{{ route("admin.configuracoes.tipos-documento.reordenar") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                    body: JSON.stringify({ ordem })
                });
                if (r.ok) {
                    this.salvo = true;
                    setTimeout(() => this.salvo = false, 2000);
                }
            } catch(e) { console.error(e); }
            this.salvando = false;
        },

        async ordenarAZ() {
            const el = document.getElementById('listaTipos');
            const items = Array.from(el.children);
            items.sort((a, b) => a.dataset.nome.localeCompare(b.dataset.nome, 'pt-BR'));
            items.forEach(item => el.appendChild(item));
            await this.salvarOrdem();
        }
    }
}
</script>
@endpush
@endsection
