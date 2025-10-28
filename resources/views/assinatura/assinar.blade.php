@extends('layouts.admin')

@section('title', 'Assinar Documento')

@section('content')
<div class="max-w-8xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.assinatura.pendentes') }}" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Voltar para documentos pendentes
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        {{-- Informações do Documento --}}
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">📄 Informações do Documento</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase">Tipo de Documento</label>
                    <p class="mt-1 text-sm font-medium text-gray-900">{{ $documento->tipoDocumento->nome }}</p>
                </div>
                @if($documento->processo)
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Processo</label>
                        <p class="mt-1 text-sm font-medium text-gray-900">{{ $documento->processo->numero_processo ?? 'S/N' }}</p>
                    </div>
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">Estabelecimento</label>
                        <p class="mt-1 text-sm font-medium text-gray-900">
                            {{ $documento->processo->estabelecimento->nome_fantasia ?? 'N/A' }}
                        </p>
                    </div>
                @else
                    <div>
                        <label class="text-xs font-medium text-gray-500 uppercase">ID do Documento</label>
                        <p class="mt-1 text-sm font-medium text-gray-900">#{{ $documento->id }}</p>
                    </div>
                @endif
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase">Criado em</label>
                    <p class="mt-1 text-sm font-medium text-gray-900">
                        {{ $documento->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase">Sua Ordem</label>
                    <p class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            {{ $assinatura->ordem }}º assinante
                        </span>
                    </p>
                </div>
                <div>
                    <label class="text-xs font-medium text-gray-500 uppercase">Conteúdo</label>
                    <p class="mt-1">
                        <button type="button" onclick="abrirModalConteudo()" 
                                class="inline-flex items-center gap-1 px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Ver Conteúdo
                        </button>
                    </p>
                </div>
            </div>
        </div>

        {{-- Formulário de Assinatura --}}
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">🔐 Confirmar Assinatura</h2>
            
            <form action="{{ route('admin.assinatura.processar', $documento->id) }}" method="POST" id="formAssinatura">
                @csrf
                <input type="hidden" name="acao" id="acao" value="assinar">

                <div class="mb-6">
                    <label for="senha_assinatura" class="block text-sm font-medium text-gray-700 mb-2">
                        Senha de Assinatura Digital *
                    </label>
                    <input type="password" 
                           name="senha_assinatura" 
                           id="senha_assinatura"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('senha_assinatura') border-red-500 @enderror"
                           placeholder="Digite sua senha de assinatura digital"
                           required
                           autofocus>
                    <p class="mt-1 text-xs text-gray-500">
                        Digite a senha que você configurou para assinatura digital
                    </p>
                    @error('senha_assinatura')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Botões --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    @php
                        $temAssinaturaFeita = $documento->assinaturas->where('status', 'assinado')->count() > 0;
                        $usuarioLogado = auth('interno')->user();
                        $isAdmin = $usuarioLogado->isAdmin();
                    @endphp
                    @if((!$temAssinaturaFeita || $isAdmin) && $documento->status !== 'assinado')
                        <button type="button" onclick="abrirModalGerenciarAssinantes()" 
                                class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Gerenciar Assinantes
                        </button>
                    @endif
                    <a href="{{ route('admin.assinatura.pendentes') }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </a>
                    <button type="submit" 
                            id="btnAssinar"
                            class="px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-300">
                        ✅ Assinar Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal de Conteúdo --}}
<div id="modalConteudo" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4 pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">📝 Conteúdo do Documento</h3>
            <button onclick="fecharModalConteudo()" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="max-h-96 overflow-y-auto p-4 bg-gray-50 rounded">
            <div class="prose prose-sm max-w-none">
                {!! $documento->conteudo !!}
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <button onclick="fecharModalConteudo()" 
                    class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
                Fechar
            </button>
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

// Fechar modal ao clicar fora
document.getElementById('modalConteudo')?.addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModalConteudo();
    }
});
</script>

{{-- Modal para Gerenciar Assinantes --}}
<div id="modalGerenciarAssinantes" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
        <div class="flex items-center justify-between mb-4 pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">👥 Gerenciar Assinantes</h3>
            <button onclick="fecharModalGerenciarAssinantes()" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form action="{{ route('admin.documentos.gerenciar-assinantes', $documento->id) }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Selecione os usuários que devem assinar este documento
                </label>
                <div class="max-h-64 overflow-y-auto border border-gray-300 rounded-lg p-3 space-y-2">
                    @php
                        $usuarioLogado = auth('interno')->user();
                        $usuariosInternosQuery = \App\Models\UsuarioInterno::where('ativo', true);
                        
                        // Filtra por município do usuário logado
                        if ($usuarioLogado->municipio_id) {
                            $usuariosInternosQuery->where('municipio_id', $usuarioLogado->municipio_id);
                        }
                        
                        // Exclui administradores do sistema (sem município)
                        $usuariosInternosQuery->whereNotNull('municipio_id');
                        
                        $usuariosInternos = $usuariosInternosQuery->orderBy('nome')->get();
                        $assinantesAtuais = $documento->assinaturas->pluck('usuario_interno_id')->toArray();
                    @endphp
                    @foreach($usuariosInternos as $usuario)
                        <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                            <input type="checkbox" 
                                   name="assinantes[]" 
                                   value="{{ $usuario->id }}"
                                   {{ in_array($usuario->id, $assinantesAtuais) ? 'checked' : '' }}
                                   class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">{{ $usuario->nome }}</p>
                                <p class="text-xs text-gray-500">{{ $usuario->cargo ?? 'Cargo não informado' }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-gray-500">
                    Selecione os usuários que devem assinar o documento. A ordem será definida automaticamente.
                </p>
            </div>
            
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="fecharModalGerenciarAssinantes()" 
                        class="px-4 py-2 bg-gray-200 text-gray-800 text-sm font-medium rounded-lg hover:bg-gray-300">
                    Cancelar
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalGerenciarAssinantes() {
    document.getElementById('modalGerenciarAssinantes').classList.remove('hidden');
}

function fecharModalGerenciarAssinantes() {
    document.getElementById('modalGerenciarAssinantes').classList.add('hidden');
}

// Fechar modal ao clicar fora
document.getElementById('modalGerenciarAssinantes')?.addEventListener('click', function(e) {
    if (e.target === this) {
        fecharModalGerenciarAssinantes();
    }
});
</script>
@endsection
