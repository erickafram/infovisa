@extends('layouts.admin')

@section('title', 'Documentos Pendentes de Assinatura')

@section('content')
<div class="max-w-8xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Documentos Pendentes de Assinatura</h1>
            <p class="mt-1 text-sm text-gray-600">
                Documentos aguardando sua assinatura digital
            </p>
        </div>
        <a href="{{ route('admin.assinatura.configurar-senha') }}" 
           class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            🔐 Configurar Senha
        </a>
    </div>

    @if($assinaturasPendentes->isEmpty())
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nenhum documento pendente</h3>
            <p class="text-sm text-gray-600">
                Você não possui documentos aguardando assinatura no momento.
            </p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Documento
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Processo
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ordem
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Criado em
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($assinaturasPendentes as $assinatura)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center bg-blue-100 rounded-lg">
                                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $assinatura->documentoDigital->tipoDocumento->nome ?? 'Documento' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                ID: #{{ $assinatura->documentoDigital->id }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($assinatura->documentoDigital->processo)
                                        <div class="text-sm text-gray-900">
                                            {{ $assinatura->documentoDigital->processo->numero }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $assinatura->documentoDigital->processo->estabelecimento->nome_fantasia ?? 'N/A' }}
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400">Sem processo</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ $assinatura->ordem }}º
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="text-sm text-gray-900">
                                        {{ $assinatura->created_at->format('d/m/Y') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $assinatura->created_at->format('H:i') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <x-button-assinar 
                                        href="{{ route('admin.assinatura.assinar', $assinatura->documentoDigital->id) }}"
                                        variant="primary"
                                        size="md">
                                        Assinar
                                    </x-button-assinar>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h3 class="text-sm font-semibold text-yellow-900">Atenção</h3>
                    <p class="mt-1 text-xs text-yellow-800">
                        Ao assinar um documento, você está confirmando que leu e concorda com seu conteúdo. 
                        A assinatura digital tem validade jurídica e não pode ser desfeita.
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
