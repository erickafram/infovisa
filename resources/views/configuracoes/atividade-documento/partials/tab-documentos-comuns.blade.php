{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Documentos Comuns a Todos os Serviços</h3>
        <p class="text-sm text-gray-500">Estes documentos são aplicados automaticamente a todos os estabelecimentos, baseado no escopo e tipo de setor</p>
    </div>
    <a href="{{ route('admin.configuracoes.tipos-documento-obrigatorio.create', ['documento_comum' => 1]) }}" 
       class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Novo Documento Comum
    </a>
</div>

{{-- Legenda --}}
<div class="bg-gray-50 rounded-lg p-4 mb-6">
    <h4 class="text-sm font-medium text-gray-700 mb-3">Legenda dos Filtros</h4>
    <div class="flex flex-wrap gap-4">
        <div class="flex items-center gap-2">
            <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Estadual</span>
            <span class="text-xs text-gray-500">Apenas competência estadual</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Municipal</span>
            <span class="text-xs text-gray-500">Apenas competência municipal</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">Todos</span>
            <span class="text-xs text-gray-500">Ambas competências</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded-full">Público</span>
            <span class="text-xs text-gray-500">Apenas setor público</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded-full">Privado</span>
            <span class="text-xs text-gray-500">Apenas setor privado</span>
        </div>
    </div>
</div>

{{-- Tabela --}}
@if($documentosComuns->isEmpty())
<div class="text-center py-8">
    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    <p class="text-gray-500">Nenhum documento comum cadastrado</p>
    <a href="{{ route('admin.configuracoes.tipos-documento-obrigatorio.create', ['documento_comum' => 1]) }}" class="mt-2 inline-block text-sm text-purple-600 hover:underline">
        Cadastrar primeiro documento comum
    </a>
</div>
@else
<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-gray-50 border-y border-gray-200">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Documento</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nomenclatura</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Escopo</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Setor</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Validade</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($documentosComuns as $doc)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <div>
                        <span class="text-sm font-medium text-gray-900">{{ $doc->nome }}</span>
                        @if($doc->descricao)
                        <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($doc->descricao, 60) }}</p>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-3">
                    <span class="text-sm font-mono text-gray-600">{{ $doc->nomenclatura }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        @if($doc->escopo_competencia === 'estadual') bg-blue-100 text-blue-800
                        @elseif($doc->escopo_competencia === 'municipal') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-600 @endif">
                        {{ $doc->escopo_competencia_label }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                        @if($doc->tipo_setor === 'publico') bg-indigo-100 text-indigo-800
                        @elseif($doc->tipo_setor === 'privado') bg-orange-100 text-orange-800
                        @else bg-gray-100 text-gray-600 @endif">
                        {{ $doc->tipo_setor_label }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    @if($doc->prazo_validade_dias)
                    <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">
                        {{ $doc->prazo_validade_dias }} dias
                    </span>
                    @else
                    <span class="text-xs text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.configuracoes.tipos-documento-obrigatorio.edit', $doc) }}" 
                           class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- Exemplo de Aplicação --}}
<div class="mt-8 bg-amber-50 border border-amber-200 rounded-lg p-4">
    <h4 class="text-sm font-semibold text-amber-800 mb-2">Como funciona?</h4>
    <p class="text-sm text-amber-700">
        Quando um estabelecimento faz cadastro, os documentos comuns são automaticamente incluídos na lista de documentos exigidos, 
        respeitando os filtros de escopo (estadual/municipal) e tipo de setor (público/privado).
    </p>
    <div class="mt-3 text-sm text-amber-700">
        <strong>Exemplo:</strong> Um hospital privado de competência estadual receberá:
        <ul class="list-disc list-inside mt-1 ml-2">
            <li>Documentos com escopo "Todos" ou "Estadual"</li>
            <li>Documentos com setor "Todos" ou "Privado"</li>
        </ul>
    </div>
</div>
