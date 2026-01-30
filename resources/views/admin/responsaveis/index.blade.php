@extends('layouts.admin')

@section('title', 'Responsáveis')
@section('page-title', 'Responsáveis')

@section('content')
<div class="space-y-6">
    
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800 tracking-tight">Responsáveis</h2>
        </div>
        
        {{-- Stats Compactos (Pílulas) --}}
        <div class="flex gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded bg-white border border-gray-200 shadow-sm text-sm font-medium text-gray-600">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                Total: <span class="text-gray-900 font-bold ml-1">{{ $responsaveis->total() }}</span>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded bg-white border border-gray-200 shadow-sm text-sm font-medium text-gray-600 hidden sm:inline-flex">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                Legais: <span class="text-gray-900 font-bold ml-1">{{ \App\Models\Responsavel::where('tipo', 'legal')->count() }}</span>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded bg-white border border-gray-200 shadow-sm text-sm font-medium text-gray-600 hidden sm:inline-flex">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Técnicos: <span class="text-gray-900 font-bold ml-1">{{ \App\Models\Responsavel::where('tipo', 'tecnico')->count() }}</span>
            </span>
        </div>
    </div>

    {{-- Alert Competência (Se aplicável) --}}
    @if(!auth('interno')->user()->isAdmin())
        <div class="flex items-center gap-2 px-3 py-2 rounded border text-sm font-medium {{ auth('interno')->user()->isEstadual() ? 'bg-purple-50 border-purple-100 text-purple-700' : 'bg-blue-50 border-blue-100 text-blue-700' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p>
                Visualizando responsáveis de competência
                <span class="font-bold uppercase">{{ auth('interno')->user()->isEstadual() ? 'Estadual' : (auth('interno')->user()->municipio ?? 'Municipal') }}</span>.
            </p>
        </div>
    @endif

    {{-- Filtros e Busca --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3">
        <form method="GET" action="{{ route('admin.responsaveis.index') }}" class="flex flex-col md:flex-row gap-3">
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Buscar por Nome ou CPF..." 
                       class="pl-9 block w-full text-sm rounded border-gray-200 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 border py-2">
            </div>
            
            <div class="w-full md:w-40">
                <select name="tipo" class="block w-full text-sm rounded border-gray-200 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 border py-2">
                    <option value="">Todos os Tipos</option>
                    <option value="legal" {{ request('tipo') === 'legal' ? 'selected' : '' }}>Responsável Legal</option>
                    <option value="tecnico" {{ request('tipo') === 'tecnico' ? 'selected' : '' }}>Responsável Técnico</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded transition-colors shadow-sm">
                    Filtrar
                </button>
                @if(request()->anyFilled(['busca', 'tipo']))
                    <a href="{{ route('admin.responsaveis.index') }}" class="px-3 py-2 bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 text-sm font-semibold rounded transition-colors">
                        Limpar
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Tabela Compacta --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50/50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Responsável</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Função</th>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Contato</th>
                        <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Vínculos</th>
                        <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($responsaveis as $responsavel)
                    <tr class="hover:bg-blue-50/50 transition-colors group">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="h-6 w-6 rounded bg-gray-100 flex items-center justify-center text-gray-500 font-bold text-xs border border-gray-200">
                                    {{ substr($responsavel->nome, 0, 1) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">{{ $responsavel->nome }}</span>
                                    <span class="text-xs text-gray-400 font-mono">{{ $responsavel->cpf_formatado }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                @foreach($responsavel->tipos as $tipo)
                                    @if($tipo == 'legal')
                                        <span class="inline-flex px-2 py-1 rounded text-xs font-bold uppercase bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20">Legal</span>
                                    @else
                                        <span class="inline-flex px-2 py-1 rounded text-xs font-bold uppercase bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">Técnico</span>
                                    @endif
                                @endforeach
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-600">{{ $responsavel->email }}</span>
                                <span class="text-xs text-gray-400">{{ $responsavel->telefone_formatado ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-center">
                            <span class="inline-flex items-center justify-center px-2 py-1 rounded text-xs font-bold bg-gray-100 text-gray-600 min-w-[24px]">
                                {{ $responsavel->total_estabelecimentos }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <a href="{{ route('admin.responsaveis.show', $responsavel->id) }}" class="text-gray-400 hover:text-blue-600 p-2 hover:bg-blue-50 rounded transition-colors inline-block" title="Ver Detalhes">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <svg class="w-8 h-8 opacity-20 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <span class="text-sm font-medium">Nenhum responsável encontrado.</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($responsaveis->hasPages())
        <div class="px-4 py-4 border-t border-gray-200 bg-gray-50 flex items-center justify-between">
             <div class="hidden sm:block">
                <p class="text-sm text-gray-600">
                    Mostrando <span class="font-medium">{{ $responsaveis->firstItem() }}</span> a <span class="font-medium">{{ $responsaveis->lastItem() }}</span> de <span class="font-medium">{{ $responsaveis->total() }}</span> resultados
                </p>
            </div>
            <div class="flex-1 flex justify-between sm:justify-end">
                {{ $responsaveis->links('pagination.tailwind-clean') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
