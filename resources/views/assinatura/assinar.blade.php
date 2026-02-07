@extends('layouts.admin')

@section('title', 'Assinar Documento')

@section('content')
<div class="max-w-4xl mx-auto py-6 space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('admin.assinatura.pendentes') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Voltar
            </a>
            <h1 class="text-xl font-bold text-gray-900">Assinatura Digital</h1>
            <p class="text-sm text-gray-500 mt-1">Revise e assine o documento digitalmente</p>
        </div>
        <span class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-yellow-700 bg-yellow-50 rounded-lg border border-yellow-200">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
            Pendente de Assinatura
        </span>
    </div>

    {{-- Card Principal --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        {{-- Informações do Documento --}}
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $documento->tipoDocumento->nome }}</h2>
                    <p class="text-sm text-gray-500 mt-1">Documento #{{ $documento->numero_documento ?? $documento->id }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400">Criado em</p>
                    <p class="text-sm font-medium text-gray-700">{{ $documento->created_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            @if($documento->processo)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 p-4 bg-gray-50 rounded-lg">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Processo</p>
                    <a href="{{ route('admin.estabelecimentos.processos.show', [$documento->processo->estabelecimento_id, $documento->processo->id]) }}" 
                       class="text-sm font-medium text-blue-600 hover:text-blue-700 flex items-center gap-1">
                        {{ $documento->processo->numero_processo ?? 'S/N' }}
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
                <div>
                    <p class="text-xs text-gray-500 mb-1">Estabelecimento</p>
                    <p class="text-sm font-medium text-gray-700 truncate">{{ $documento->processo->estabelecimento->nome_fantasia ?? 'N/A' }}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Assinantes --}}
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Assinantes ({{ $assinatura->ordem }}º de {{ $documento->assinaturas->count() }})</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($documento->assinaturas->sortBy('ordem') as $ass)
                <div class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border
                    {{ $ass->status === 'assinado' ? 'bg-green-50 border-green-200 text-green-700' : ($ass->usuario_interno_id === auth('interno')->id() ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-gray-50 border-gray-200 text-gray-500') }}">
                    @if($ass->status === 'assinado')
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    @elseif($ass->usuario_interno_id === auth('interno')->id())
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                    @else
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/></svg>
                    @endif
                    <span class="text-sm font-medium">{{ $ass->usuarioInterno->nome ?? 'Usuário' }}</span>
                    @if($ass->status === 'assinado')
                        <span class="text-xs opacity-75">• Assinado</span>
                    @elseif($ass->usuario_interno_id === auth('interno')->id())
                        <span class="text-xs opacity-75">• Você</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Formulário de Assinatura --}}
        <form action="{{ route('admin.assinatura.processar', $documento->id) }}" method="POST" class="p-6">
            @csrf
            <input type="hidden" name="acao" value="assinar">

            <div class="mb-6">
                <label class="block text-sm font-semibold text-gray-900 mb-2">Senha de Assinatura Digital</label>
                <input type="password" 
                       name="senha_assinatura" 
                       class="w-full px-4 py-3 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('senha_assinatura') border-red-400 ring-2 ring-red-200 @enderror"
                       placeholder="Digite sua senha de assinatura"
                       required
                       autofocus>
                @error('senha_assinatura')
                    <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Botões de Ação --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <button type="button" onclick="abrirModalPdf()" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Visualizar Documento
                </button>
                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 transition shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Assinar Documento
                </button>
            </div>
            
            @php
                $temAssinaturaFeita = $documento->assinaturas->where('status', 'assinado')->count() > 0;
                $usuarioLogado = auth('interno')->user();
                $isAdmin = $usuarioLogado->isAdmin();
            @endphp
            @if((!$temAssinaturaFeita || $isAdmin) && $documento->status !== 'assinado')
            <button type="button" onclick="abrirModalGerenciarAssinantes()" class="w-full mt-4 text-xs text-gray-400 hover:text-gray-600 transition">
                Gerenciar assinantes
            </button>
            @endif
        </form>

        {{-- Rodapé com Segurança --}}
        <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-t border-gray-100">
            <div class="flex items-center justify-center gap-2 text-xs text-gray-600">
                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                <span class="font-medium">Assinatura digital protegida por criptografia</span>
            </div>
        </div>
    </div>
</div>

{{-- Modal PDF --}}
<div id="modalPdf" class="hidden fixed inset-0 bg-gray-900/95 z-50">
    <div class="flex items-center justify-center min-h-screen p-6">
        <div class="bg-white rounded-xl w-full max-w-7xl h-[95vh] overflow-hidden flex flex-col shadow-2xl">
            {{-- Header Minimalista --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span class="text-sm font-medium text-gray-700">{{ $documento->numero_documento ?? 'Documento #' . $documento->id }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="abrirPdfNovaAba()" class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Nova aba
                    </button>
                    <button onclick="fecharModalPdf()" class="p-2 text-gray-400 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            
            {{-- Conteúdo do PDF --}}
            <div class="flex-1 overflow-hidden bg-gray-50">
                <iframe id="pdfFrame" src="" class="w-full h-full border-0"></iframe>
            </div>
        </div>
    </div>
</div>

{{-- Modal Gerenciar Assinantes --}}
<div id="modalGerenciarAssinantes" class="hidden fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl w-full max-w-md shadow-xl overflow-hidden">
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="text-base font-semibold text-gray-900">Gerenciar Assinantes</h3>
                <button onclick="fecharModalGerenciarAssinantes()" class="p-1.5 text-gray-400 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            {{-- Form --}}
            <form action="{{ route('admin.documentos.gerenciar-assinantes', $documento->id) }}" method="POST">
                @csrf
                <div class="p-5">
                    <p class="text-sm text-gray-500 mb-4">Selecione os usuários que devem assinar:</p>
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
                    <div class="space-y-1 max-h-80 overflow-y-auto pr-2">
                        @foreach($usuariosInternos as $usuario)
                        <label class="flex items-center gap-3 p-2.5 cursor-pointer hover:bg-gray-50 rounded-lg transition group">
                            <input type="checkbox" name="assinantes[]" value="{{ $usuario->id }}" {{ in_array($usuario->id, $assinantesAtuais) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-2 focus:ring-blue-500 focus:ring-offset-0">
                            <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ $usuario->nome }}</span>
                            @if(in_array($usuario->id, $assinantesAtuais))
                            <svg class="w-4 h-4 text-green-500 ml-auto" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            @endif
                        </label>
                        @endforeach
                    </div>
                </div>
                
                {{-- Footer --}}
                <div class="px-5 py-4 border-t border-gray-100 flex gap-3">
                    <button type="button" onclick="fecharModalGerenciarAssinantes()" class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-lg transition">
                        Cancelar
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const pdfUrl = "{{ route('admin.assinatura.visualizar-pdf', $documento->id) }}";

function abrirModalPdf() {
    const modal = document.getElementById('modalPdf');
    const iframe = document.getElementById('pdfFrame');
    iframe.src = pdfUrl;
    modal.classList.remove('hidden');
}

function fecharModalPdf() {
    const modal = document.getElementById('modalPdf');
    const iframe = document.getElementById('pdfFrame');
    modal.classList.add('hidden');
    iframe.src = ''; // Limpa o iframe ao fechar
}

function abrirPdfNovaAba() {
    window.open(pdfUrl, '_blank');
}

function abrirModalGerenciarAssinantes() {
    document.getElementById('modalGerenciarAssinantes').classList.remove('hidden');
}

function fecharModalGerenciarAssinantes() {
    document.getElementById('modalGerenciarAssinantes').classList.add('hidden');
}

// Fecha modal ao clicar fora
document.getElementById('modalPdf')?.addEventListener('click', e => { 
    if (e.target.id === 'modalPdf') fecharModalPdf(); 
});

document.getElementById('modalGerenciarAssinantes')?.addEventListener('click', e => { 
    if (e.target.id === 'modalGerenciarAssinantes') fecharModalGerenciarAssinantes(); 
});

// Fecha modais com ESC
document.addEventListener('keydown', e => { 
    if (e.key === 'Escape') { 
        fecharModalPdf(); 
        fecharModalGerenciarAssinantes(); 
    }
});
</script>
@endsection
