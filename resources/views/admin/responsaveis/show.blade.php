@extends('layouts.admin')

@section('title', 'Detalhes do Responsável')
@section('page-title', 'Detalhes do Responsável')

@section('content')
<div class="space-y-6">
    {{-- Navegação e Título --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.responsaveis.index') }}" 
               class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-all"
               title="Voltar para lista">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 leading-tight">
                    {{ $responsavel->nome }}
                </h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-xs text-gray-500 font-mono bg-gray-100 px-2 py-0.5 rounded border border-gray-200">
                        {{ $responsavel->cpf_formatado }}
                    </span>
                    @foreach($responsavel->tipos as $tipo)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide {{ $tipo === 'legal' ? 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20' : 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20' }}">
                            {{ $tipo === 'legal' ? 'Resp. Legal' : 'Resp. Técnico' }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Coluna Principal: Informações e Documentos --}}
        <div class="lg:col-span-1 space-y-6">
            
            {{-- Cartão de Contato --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50">
                    <h3 class="font-bold text-gray-800 text-sm flex items-center gap-2 uppercase tracking-wide">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        Contato
                    </h3>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Email</label>
                        <a href="mailto:{{ $responsavel->email }}" class="text-sm font-medium text-blue-600 hover:text-blue-800 break-all transition-colors flex items-center gap-1">
                            {{ $responsavel->email }}
                        </a>
                    </div>
                    <div>
                         <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Telefone</label>
                         <p class="text-sm font-medium text-gray-900">{{ $responsavel->telefone_formatado ?? 'Não informado' }}</p>
                    </div>
                </div>
            </div>

            {{-- Informações Técnicas/Legais --}}
            @php
                $temInfoExtra = false;
                foreach($responsavel->registros as $reg) {
                    if(($reg->tipo === 'tecnico' && $reg->conselho) || ($reg->tipo === 'legal' && $reg->tipo_documento)) {
                        $temInfoExtra = true;
                        break;
                    }
                }
            @endphp
            
            @if($temInfoExtra)
                @foreach($responsavel->registros as $registro)
                    @if($registro->tipo === 'tecnico' && $registro->conselho)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-emerald-50/40">
                            <h3 class="font-bold text-emerald-800 text-sm flex items-center gap-2 uppercase tracking-wide">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Registro Profissional
                            </h3>
                        </div>
                        <div class="p-5 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Conselho</label>
                                    <p class="text-sm font-bold text-gray-900">{{ $registro->conselho }}</p>
                                </div>
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Número</label>
                                    <p class="text-sm font-bold text-gray-900">{{ $registro->numero_registro_conselho }}</p>
                                </div>
                            </div>
                            
                            @if($registro->carteirinha_conselho)
                            <div class="pt-3 mt-1 border-t border-gray-50">
                                <a href="{{ asset('storage/' . $registro->carteirinha_conselho) }}" 
                                   target="_blank"
                                   class="flex items-center justify-center w-full px-4 py-2 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg hover:bg-emerald-100 transition-colors gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Visualizar Carteirinha
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif

                    @if($registro->tipo === 'legal' && $registro->tipo_documento)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 bg-blue-50/40">
                            <h3 class="font-bold text-blue-800 text-sm flex items-center gap-2 uppercase tracking-wide">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                                Dados Legais
                            </h3>
                        </div>
                        <div class="p-5 space-y-4">
                            <div>
                                <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block mb-1">Documento Apresentado</label>
                                <p class="text-sm font-bold text-gray-900">{{ strtoupper($registro->tipo_documento) }}</p>
                            </div>
                            
                            @if($registro->documento_identificacao)
                            <div class="pt-3 mt-1 border-t border-gray-50">
                                <a href="{{ asset('storage/' . $registro->documento_identificacao) }}" 
                                   target="_blank"
                                   class="flex items-center justify-center w-full px-4 py-2 bg-blue-50 text-blue-700 text-xs font-bold rounded-lg hover:bg-blue-100 transition-colors gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Visualizar Documento
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                @endforeach
            @endif
        </div>

        {{-- Coluna Secundária: Estabelecimentos (Mais larga) --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col h-full">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-900">Estabelecimentos Vinculados</h3>
                        <p class="text-xs text-gray-500 mt-1">Empresas onde este profissional atua.</p>
                    </div>
                    <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-bold ring-1 ring-inset ring-gray-500/10">
                        {{ $responsavel->estabelecimentos->count() }}
                    </span>
                </div>
                
                <div class="p-6 bg-gray-50/50 flex-1">
                    @if($responsavel->estabelecimentos->count() > 0)
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($responsavel->estabelecimentos as $est)
                            <div class="group bg-white border border-gray-200 rounded-xl p-4 hover:border-blue-300 hover:shadow-md transition-all duration-200">
                                <div class="flex flex-col sm:flex-row sm:items-start gap-4 justify-between">
                                    {{-- Info Principal --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h4 class="font-bold text-gray-900 text-sm truncate group-hover:text-blue-600 transition-colors">
                                                {{ $est->nome_fantasia ?: $est->razao_social }}
                                            </h4>
                                            @if($est->pivot->ativo)
                                                <span class="w-2 h-2 rounded-full bg-green-500" title="Vínculo Ativo"></span>
                                            @else
                                                <span class="w-2 h-2 rounded-full bg-gray-300" title="Vínculo Inativo"></span>
                                            @endif
                                        </div>
                                        
                                        <p class="text-xs text-gray-500 truncate mb-2">{{ $est->razao_social }}</p>
                                        
                                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                            <div class="flex items-center gap-1 bg-gray-50 px-2 py-1 rounded border border-gray-100">
                                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                                <span class="font-mono">{{ $est->cnpj_formatado }}</span>
                                            </div>
                                            
                                            <div class="flex items-center gap-1">
                                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <span class="truncate max-w-[150px]">{{ $est->cidade ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Badges e Ação --}}
                                    <div class="flex flex-col items-start sm:items-end gap-3 mt-2 sm:mt-0 min-w-[140px]">
                                        <div class="flex gap-2">
                                            @if($est->pivot->tipo_vinculo === 'legal')
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-600/20">
                                                    Legal
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                                    Técnico
                                                </span>
                                            @endif
                                            
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase {{ $est->pivot->ativo ? 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-600/20' : 'bg-gray-100 text-gray-600 ring-1 ring-inset ring-gray-500/20' }}">
                                                {{ $est->pivot->ativo ? 'Ativo' : 'Inativo' }}
                                            </span>
                                        </div>
                                        
                                        <a href="{{ route('admin.estabelecimentos.show', $est->id) }}" 
                                           class="inline-flex items-center justify-center w-full px-3 py-1.5 text-xs font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors shadow-sm">
                                            Ver Estabelecimento
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-64 text-center">
                            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mb-4 shadow-sm border border-gray-100">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <h3 class="text-sm font-semibold text-gray-900">Nenhum vínculo ativo</h3>
                            <p class="mt-1 text-xs text-gray-500 max-w-xs">
                                Este profissional não está vinculado a nenhuma empresa no momento.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
