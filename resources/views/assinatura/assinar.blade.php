@extends('layouts.admin')

@section('title', 'Assinar Documento')

@section('content')
<div class="max-w-xl mx-auto py-8">
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.assinatura.pendentes') }}" class="text-sm text-gray-500 hover:text-gray-700">
            ← Voltar
        </a>
        <span class="text-xs text-yellow-600 bg-yellow-50 px-2 py-1 rounded">Pendente</span>
    </div>

    {{-- Card Principal --}}
    <div class="bg-white rounded-lg border border-gray-200">
        {{-- Header --}}
        <div class="px-5 py-4 border-b border-gray-100">
            <h1 class="text-base font-semibold text-gray-900">{{ $documento->tipoDocumento->nome }}</h1>
            <p class="text-xs text-gray-500 mt-0.5">Documento #{{ $documento->numero_documento ?? $documento->id }}</p>
        </div>

        {{-- Info --}}
        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/50">
            <div class="grid grid-cols-2 gap-3 text-xs">
                @if($documento->processo)
                <div>
                    <span class="text-gray-400">Processo</span>
                    <a href="{{ route('admin.estabelecimentos.processos.show', [$documento->processo->estabelecimento_id, $documento->processo->id]) }}" 
                       class="block text-gray-700 hover:text-blue-600">
                        {{ $documento->processo->numero_processo ?? 'S/N' }}
                    </a>
                </div>
                <div>
                    <span class="text-gray-400">Estabelecimento</span>
                    <p class="text-gray-700 truncate">{{ Str::limit($documento->processo->estabelecimento->nome_fantasia ?? 'N/A', 20) }}</p>
                </div>
                @endif
                <div>
                    <span class="text-gray-400">Criado em</span>
                    <p class="text-gray-700">{{ $documento->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <span class="text-gray-400">Posição</span>
                    <p class="text-gray-700">{{ $assinatura->ordem }}º assinante</p>
                </div>
            </div>
        </div>

        {{-- Assinantes --}}
        <div class="px-5 py-3 border-b border-gray-100">
            <div class="flex flex-wrap gap-1.5">
                @foreach($documento->assinaturas->sortBy('ordem') as $ass)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs
                    {{ $ass->status === 'assinado' ? 'bg-green-50 text-green-700' : ($ass->usuario_interno_id === auth('interno')->id() ? 'bg-blue-50 text-blue-700' : 'bg-gray-100 text-gray-500') }}">
                    @if($ass->status === 'assinado')
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    @elseif($ass->usuario_interno_id === auth('interno')->id())
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                    @endif
                    {{ $ass->usuarioInterno->nome ?? 'Usuário' }}
                </span>
                @endforeach
            </div>
        </div>

        {{-- Form --}}
        <form action="{{ route('admin.assinatura.processar', $documento->id) }}" method="POST" class="px-5 py-4">
            @csrf
            <input type="hidden" name="acao" value="assinar">

            <label class="block text-xs font-medium text-gray-600 mb-1.5">Senha de Assinatura</label>
            <input type="password" 
                   name="senha_assinatura" 
                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-md focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @error('senha_assinatura') border-red-400 @enderror"
                   placeholder="••••••••"
                   required
                   autofocus>
            @error('senha_assinatura')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror

            {{-- Botões --}}
            <div class="flex items-center gap-2 mt-4">
                <button type="submit" class="flex-1 px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700">
                    Assinar
                </button>
                <button type="button" onclick="abrirModalConteudo()" class="flex-1 px-3 py-2 text-sm font-medium text-gray-600 bg-gray-100 rounded-md hover:bg-gray-200">
                    Visualizar
                </button>
            </div>
            
            @php
                $temAssinaturaFeita = $documento->assinaturas->where('status', 'assinado')->count() > 0;
                $usuarioLogado = auth('interno')->user();
                $isAdmin = $usuarioLogado->isAdmin();
            @endphp
            @if((!$temAssinaturaFeita || $isAdmin) && $documento->status !== 'assinado')
            <button type="button" onclick="abrirModalGerenciarAssinantes()" class="w-full mt-2 text-xs text-gray-400 hover:text-gray-600">
                Gerenciar assinantes
            </button>
            @endif
        </form>

        {{-- Footer --}}
        <div class="px-5 py-2.5 bg-gray-50 border-t border-gray-100">
            <p class="text-[10px] text-gray-400 flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                Assinatura digital protegida por criptografia
            </p>
        </div>
    </div>
</div>

{{-- Modal Conteúdo --}}
<div id="modalConteudo" class="hidden fixed inset-0 bg-black/40 z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg w-full max-w-2xl max-h-[80vh] overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <span class="text-sm font-medium text-gray-700">Conteúdo</span>
                <button onclick="fecharModalConteudo()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-4 overflow-y-auto max-h-[60vh]">
                <div class="prose prose-sm max-w-none">{!! $documento->conteudo !!}</div>
            </div>
            <div class="px-4 py-3 border-t bg-gray-50">
                <button onclick="fecharModalConteudo()" class="w-full px-3 py-1.5 text-sm text-gray-600 bg-gray-200 rounded hover:bg-gray-300">Fechar</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Assinantes --}}
<div id="modalGerenciarAssinantes" class="hidden fixed inset-0 bg-black/40 z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg w-full max-w-md">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <span class="text-sm font-medium text-gray-700">Assinantes</span>
                <button onclick="fecharModalGerenciarAssinantes()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('admin.documentos.gerenciar-assinantes', $documento->id) }}" method="POST">
                @csrf
                <div class="p-4 max-h-64 overflow-y-auto">
                    @php
                        $usuarioLogado = auth('interno')->user();
                        $usuariosInternosQuery = \App\Models\UsuarioInterno::where('ativo', true);
                        if ($usuarioLogado->municipio_id) {
                            $usuariosInternosQuery->where('municipio_id', $usuarioLogado->municipio_id);
                        }
                        $usuariosInternosQuery->whereNotNull('municipio_id');
                        $usuariosInternos = $usuariosInternosQuery->orderBy('nome')->get();
                        $assinantesAtuais = $documento->assinaturas->pluck('usuario_interno_id')->toArray();
                    @endphp
                    @foreach($usuariosInternos as $usuario)
                    <label class="flex items-center gap-2 py-2 cursor-pointer hover:bg-gray-50 -mx-2 px-2 rounded">
                        <input type="checkbox" name="assinantes[]" value="{{ $usuario->id }}" {{ in_array($usuario->id, $assinantesAtuais) ? 'checked' : '' }}
                               class="w-3.5 h-3.5 text-blue-600 rounded border-gray-300">
                        <span class="text-sm text-gray-700">{{ $usuario->nome }}</span>
                    </label>
                    @endforeach
                </div>
                <div class="px-4 py-3 border-t bg-gray-50 flex gap-2">
                    <button type="button" onclick="fecharModalGerenciarAssinantes()" class="flex-1 px-3 py-1.5 text-sm text-gray-600 bg-gray-200 rounded hover:bg-gray-300">Cancelar</button>
                    <button type="submit" class="flex-1 px-3 py-1.5 text-sm text-white bg-blue-600 rounded hover:bg-blue-700">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirModalConteudo() {
    document.getElementById('modalConteudo').classList.remove('hidden');
}
function fecharModalConteudo() {
    document.getElementById('modalConteudo').classList.add('hidden');
}
function abrirModalGerenciarAssinantes() {
    document.getElementById('modalGerenciarAssinantes').classList.remove('hidden');
}
function fecharModalGerenciarAssinantes() {
    document.getElementById('modalGerenciarAssinantes').classList.add('hidden');
}
document.getElementById('modalConteudo')?.addEventListener('click', e => { if (e.target.id === 'modalConteudo') fecharModalConteudo(); });
document.getElementById('modalGerenciarAssinantes')?.addEventListener('click', e => { if (e.target.id === 'modalGerenciarAssinantes') fecharModalGerenciarAssinantes(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') { fecharModalConteudo(); fecharModalGerenciarAssinantes(); }});
</script>
@endsection
