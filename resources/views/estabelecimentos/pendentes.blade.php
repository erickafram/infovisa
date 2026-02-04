@extends('layouts.admin')

@section('title', 'Estabelecimentos Pendentes')
@section('page-title', 'Estabelecimentos Pendentes de Aprovação')

@section('content')
<div class="space-y-3">
    {{-- Header compacto --}}
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-bold text-gray-900">Pendentes de Aprovação</h2>
        <a href="{{ route('admin.estabelecimentos.index') }}" class="text-sm text-gray-600 hover:text-gray-900">← Voltar</a>
    </div>

    {{-- Tabs compactas --}}
    <div class="flex gap-4 border-b border-gray-200 text-sm">
        <a href="{{ route('admin.estabelecimentos.pendentes') }}" class="border-b-2 border-yellow-500 text-yellow-600 pb-2 font-medium">
            Pendentes @if(isset($totalPendentes) && $totalPendentes > 0)<span class="ml-1 bg-yellow-100 text-yellow-800 text-xs px-1.5 py-0.5 rounded-full">{{ $totalPendentes }}</span>@endif
        </a>
        <a href="{{ route('admin.estabelecimentos.rejeitados') }}" class="text-gray-500 hover:text-gray-700 pb-2">
            Rejeitados @if(isset($totalRejeitados) && $totalRejeitados > 0)<span class="ml-1 bg-red-100 text-red-800 text-xs px-1.5 py-0.5 rounded-full">{{ $totalRejeitados }}</span>@endif
        </a>
        <a href="{{ route('admin.estabelecimentos.desativados') }}" class="text-gray-500 hover:text-gray-700 pb-2">
            Desativados @if(isset($totalDesativados) && $totalDesativados > 0)<span class="ml-1 bg-gray-100 text-gray-800 text-xs px-1.5 py-0.5 rounded-full">{{ $totalDesativados }}</span>@endif
        </a>
    </div>

    {{-- Busca compacta --}}
    @if($estabelecimentos->total() > 5)
    <form method="GET" action="{{ route('admin.estabelecimentos.pendentes') }}" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por CNPJ, CPF, Nome..."
               class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500">
        <button type="submit" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg text-sm hover:bg-blue-700">Buscar</button>
        @if(request('search'))<a href="{{ route('admin.estabelecimentos.pendentes') }}" class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg text-sm">Limpar</a>@endif
    </form>
    @endif

    {{-- Lista compacta --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        @if($estabelecimentos->count() > 0)
            <div class="px-4 py-2 border-b bg-yellow-50 text-sm text-yellow-800">
                <strong>{{ $estabelecimentos->total() }}</strong> estabelecimento{{ $estabelecimentos->total() !== 1 ? 's' : '' }} pendente{{ $estabelecimentos->total() !== 1 ? 's' : '' }}
            </div>

            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-2">Estabelecimento</th>
                        <th class="px-4 py-2">Documento</th>
                        <th class="px-4 py-2">Município</th>
                        <th class="px-4 py-2">Cadastro</th>
                        <th class="px-4 py-2 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($estabelecimentos as $estabelecimento)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">
                            <div class="font-medium text-gray-900">{{ Str::limit($estabelecimento->nome_razao_social, 35) }}</div>
                            @if($estabelecimento->nome_fantasia && $estabelecimento->tipo_pessoa === 'juridica')
                            <div class="text-xs text-gray-500">{{ Str::limit($estabelecimento->nome_fantasia, 30) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-2">
                            <span class="text-xs {{ $estabelecimento->tipo_pessoa === 'juridica' ? 'text-blue-600' : 'text-green-600' }}">
                                {{ $estabelecimento->tipo_pessoa === 'juridica' ? 'PJ' : 'PF' }}
                            </span>
                            {{ $estabelecimento->documento_formatado }}
                        </td>
                        <td class="px-4 py-2 text-gray-600">{{ $estabelecimento->cidade }}/{{ $estabelecimento->estado }}</td>
                        <td class="px-4 py-2 text-gray-500 text-xs">{{ $estabelecimento->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}" 
                                   class="bg-blue-600 text-white px-2 py-1 rounded text-xs hover:bg-blue-700" title="Analisar">
                                    Analisar
                                </a>
                                <form action="{{ route('admin.estabelecimentos.aprovar', $estabelecimento->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Aprovar este estabelecimento?')"
                                            class="bg-green-600 text-white px-2 py-1 rounded text-xs hover:bg-green-700" title="Aprovar">
                                        ✓ Aprovar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($estabelecimentos->hasPages())
            <div class="px-4 py-2 border-t border-gray-200">{{ $estabelecimentos->links() }}</div>
            @endif
        @else
            <div class="px-4 py-8 text-center text-gray-500">
                <p class="font-medium">✓ Nenhum estabelecimento pendente</p>
                <a href="{{ route('admin.estabelecimentos.index') }}" class="text-blue-600 text-sm hover:underline">Ver todos os estabelecimentos</a>
            </div>
        @endif
    </div>
</div>
@endsection
