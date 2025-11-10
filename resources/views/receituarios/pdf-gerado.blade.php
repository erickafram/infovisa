@extends('layouts.admin')

@section('title', 'Documento Gerado - Aguardando Assinatura')
@section('page-title', 'Documento Gerado - Aguardando Assinatura')

@section('content')
<div class="max-w-7xl mx-auto">
    
    {{-- Alerta de Ação Necessária --}}
    <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg shadow-lg">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <svg class="h-8 w-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="ml-4 flex-1">
                <h3 class="text-xl font-bold text-yellow-800 mb-2">
                    ⚠️ ATENÇÃO - DOCUMENTO AGUARDANDO ASSINATURA
                </h3>
                <div class="text-yellow-700 space-y-2">
                    <p class="font-semibold">Para concluir o cadastro, você deve:</p>
                    <ol class="list-decimal list-inside space-y-1 ml-4">
                        <li><strong>Imprimir</strong> o documento abaixo (clique no botão de impressão)</li>
                        <li><strong>Assinar</strong> nas 3 vias indicadas no documento</li>
                        <li><strong>Digitalizar</strong> o documento assinado</li>
                        <li><strong>Anexar</strong> o arquivo digitalizado no processo de receituário</li>
                    </ol>
                    <p class="mt-3 text-sm italic">
                        💡 <strong>Importante:</strong> As assinaturas devem ser semelhantes ao documento de identificação ou reconhecidas em cartório.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Ações Rápidas --}}
    <div class="mb-6 flex items-center justify-between bg-white p-4 rounded-lg shadow">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.receituarios.index') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Voltar para Lista
            </a>

            <a href="{{ route('admin.receituarios.show', $receituario->id) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Ver Detalhes do Cadastro
            </a>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.receituarios.gerar-pdf', $receituario->id) }}" 
               target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Baixar PDF
            </a>

            <button onclick="printPDF()" 
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                🖨️ Imprimir Documento
            </button>
        </div>
    </div>

    {{-- Card de Informações do Receituário --}}
    <div class="mb-6 bg-white p-4 rounded-lg shadow">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <span class="text-sm text-gray-500">Tipo:</span>
                <p class="font-semibold text-gray-900">{{ $receituario->tipo_nome }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Nome/Razão Social:</span>
                <p class="font-semibold text-gray-900">{{ $receituario->nome ?? $receituario->razao_social }}</p>
            </div>
            <div>
                <span class="text-sm text-gray-500">Status:</span>
                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                    AGUARDANDO ASSINATURA
                </span>
            </div>
        </div>
    </div>

    {{-- Visualizador de PDF Incorporado --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-gray-800 text-white px-6 py-3 flex items-center justify-between">
            <h3 class="text-lg font-semibold">📄 Documento para Assinatura</h3>
            <span class="text-sm text-gray-300">Visualização do PDF</span>
        </div>
        
        <div class="relative" style="height: 800px;">
            <iframe 
                id="pdfViewer"
                src="{{ route('admin.receituarios.gerar-pdf', $receituario->id) }}" 
                class="w-full h-full border-0"
                title="Documento PDF para Assinatura">
            </iframe>
        </div>
    </div>

    {{-- Instruções Detalhadas --}}
    <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-6 rounded-lg">
        <h4 class="text-lg font-bold text-blue-900 mb-3">📋 Próximos Passos</h4>
        <div class="space-y-3 text-blue-800">
            <div class="flex items-start gap-3">
                <span class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">1</span>
                <div>
                    <p class="font-semibold">Imprima o documento</p>
                    <p class="text-sm">Clique no botão "🖨️ Imprimir Documento" acima ou use Ctrl+P</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">2</span>
                <div>
                    <p class="font-semibold">Assine nas 3 vias indicadas</p>
                    <p class="text-sm">Preencha os campos de assinatura conforme indicado no documento</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">3</span>
                <div>
                    <p class="font-semibold">Digitalize o documento assinado</p>
                    <p class="text-sm">Escaneie ou fotografe o documento com boa qualidade</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="flex-shrink-0 w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">4</span>
                <div>
                    <p class="font-semibold">Anexe no processo de receituário</p>
                    <p class="text-sm">Acesse a lista de receituários e anexe o documento digitalizado</p>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function printPDF() {
    const iframe = document.getElementById('pdfViewer');
    iframe.contentWindow.print();
}

// Alerta ao sair da página sem anexar
window.addEventListener('beforeunload', function (e) {
    const confirmationMessage = 'Você ainda não anexou o documento assinado. Tem certeza que deseja sair?';
    e.returnValue = confirmationMessage;
    return confirmationMessage;
});
</script>
@endsection
