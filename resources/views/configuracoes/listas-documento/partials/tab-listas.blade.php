{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h3 class="text-lg font-semibold text-gray-900">Listas de Documentos</h3>
        <p class="text-sm text-gray-500">Configure quais documentos são exigidos para cada tipo de processo e atividade</p>
    </div>
    <a href="{{ route('admin.configuracoes.listas-documento.create') }}" 
       class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nova Lista
    </a>
</div>

{{-- Filtros --}}
<div class="bg-gray-50 rounded-lg p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <input type="hidden" name="tab" value="listas">
        <div class="flex-1 min-w-[180px]">
            <label class="text-[10px] font-medium text-gray-500 uppercase mb-1 block">Buscar</label>
            <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Nome da lista..."
                   class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div class="w-44">
            <label class="text-[10px] font-medium text-gray-500 uppercase mb-1 block">Tipo de Processo</label>
            <select name="tipo_processo_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Todos</option>
                @foreach($tiposProcesso as $tp)
                <option value="{{ $tp->id }}" {{ request('tipo_processo_id') == $tp->id ? 'selected' : '' }}>{{ $tp->nome }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-36">
            <label class="text-[10px] font-medium text-gray-500 uppercase mb-1 block">Escopo</label>
            <select name="escopo" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Todos</option>
                <option value="estadual" {{ request('escopo') === 'estadual' ? 'selected' : '' }}>🏛️ Estadual</option>
                <option value="municipal" {{ request('escopo') === 'municipal' ? 'selected' : '' }}>🏘️ Municipal</option>
            </select>
        </div>
        @if(request('escopo') === 'municipal' || !request('escopo'))
        <div class="w-44">
            <label class="text-[10px] font-medium text-gray-500 uppercase mb-1 block">Município</label>
            <select name="municipio_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Todos</option>
                @foreach(\App\Models\Municipio::orderBy('nome')->get() as $mun)
                <option value="{{ $mun->id }}" {{ request('municipio_id') == $mun->id ? 'selected' : '' }}>{{ $mun->nome }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <button type="submit" class="px-4 py-2 bg-gray-700 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition">Filtrar</button>
        @if(request('busca') || request('tipo_processo_id') || request('escopo') || request('municipio_id'))
        <a href="{{ route('admin.configuracoes.listas-documento.index', ['tab' => 'listas']) }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700">Limpar</a>
        @endif
    </form>
</div>

@if($listas->isEmpty())
<div class="text-center py-12">
    <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
    </svg>
    <p class="text-gray-500">Nenhuma lista encontrada</p>
    <a href="{{ route('admin.configuracoes.listas-documento.create') }}" class="mt-2 inline-block text-sm text-blue-600 hover:underline">Criar primeira lista</a>
</div>
@else

@php
    $listasEstaduais = $listas->where('escopo', 'estadual');
    $listasMunicipais = $listas->where('escopo', 'municipal');
    $municipiosComListas = $listasMunicipais->groupBy('municipio_id');
    $filtroEscopo = request('escopo');
@endphp

{{-- SEÇÃO ESTADUAL --}}
@if(!$filtroEscopo || $filtroEscopo === 'estadual')
@if($listasEstaduais->count() > 0)
<div class="mb-8">
    <div class="flex items-center gap-2 mb-3">
        <span class="text-lg">🏛️</span>
        <h4 class="text-sm font-bold text-blue-800 uppercase tracking-wide">Estadual</h4>
        <span class="text-[10px] px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full font-bold">{{ $listasEstaduais->count() }}</span>
        <div class="flex-1 h-px bg-blue-100"></div>
    </div>
    <div class="bg-white rounded-xl border border-blue-200 overflow-hidden">
        @include('configuracoes.listas-documento.partials.tabela-listas', ['listasTabela' => $listasEstaduais])
    </div>
</div>
@endif
@endif

{{-- SEÇÃO MUNICIPAL --}}
@if(!$filtroEscopo || $filtroEscopo === 'municipal')
@if($listasMunicipais->count() > 0)
<div class="mb-4">
    <div class="flex items-center gap-2 mb-3">
        <span class="text-lg">🏘️</span>
        <h4 class="text-sm font-bold text-green-800 uppercase tracking-wide">Municipal</h4>
        <span class="text-[10px] px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-bold">{{ $listasMunicipais->count() }}</span>
        <div class="flex-1 h-px bg-green-100"></div>
    </div>

    @foreach($municipiosComListas as $munId => $listasDoMunicipio)
    @php $municipioNome = $listasDoMunicipio->first()->municipio->nome ?? 'Município #' . $munId; @endphp
    <div class="mb-4">
        <div class="flex items-center gap-2 mb-2 ml-2">
            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <span class="text-xs font-semibold text-green-700">{{ $municipioNome }}</span>
            <span class="text-[10px] px-1.5 py-0.5 bg-green-50 text-green-600 rounded-full font-medium">{{ $listasDoMunicipio->count() }} lista(s)</span>
        </div>
        <div class="bg-white rounded-xl border border-green-200 overflow-hidden">
            @include('configuracoes.listas-documento.partials.tabela-listas', ['listasTabela' => $listasDoMunicipio])
        </div>
    </div>
    @endforeach
</div>
@elseif($filtroEscopo === 'municipal')
<div class="text-center py-8 bg-green-50 rounded-xl border border-green-200">
    <p class="text-sm text-green-700">Nenhuma lista municipal cadastrada</p>
    <p class="text-xs text-green-500 mt-1">Crie listas específicas para cada município com seus documentos obrigatórios</p>
    <a href="{{ route('admin.configuracoes.listas-documento.create') }}" class="mt-3 inline-flex items-center gap-1 text-sm text-green-700 font-medium hover:underline">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Criar lista municipal
    </a>
</div>
@endif
@endif

@if($listas->hasPages())
<div class="mt-4">
    {{ $listas->appends(['tab' => 'listas'])->links() }}
</div>
@endif
@endif
