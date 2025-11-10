@extends('layouts.admin')

@section('title', 'Visualizar Receituário')
@section('page-title', 'Visualizar Receituário')

@section('content')
<div class="max-w-8xl mx-auto">
    
    {{-- Cabeçalho com ações --}}
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.receituarios.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Voltar
        </a>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.receituarios.gerar-pdf', $receituario->id) }}" 
               target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                📄 Gerar PDF para Assinatura
            </a>

            <a href="{{ route('admin.receituarios.edit', $receituario->id) }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Editar
            </a>

            @if(!$receituario->processo_id)
            <form action="{{ route('admin.receituarios.criar-processo', $receituario->id) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Criar Processo
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Informações do Receituário --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $receituario->identificador }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $receituario->tipo_nome }}</p>
            </div>
            <div>
                <span class="px-4 py-2 text-sm font-semibold rounded-full
                    {{ $receituario->status == 'ativo' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $receituario->status == 'pendente' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $receituario->status == 'inativo' ? 'bg-gray-100 text-gray-800' : '' }}">
                    {{ strtoupper($receituario->status) }}
                </span>
            </div>
        </div>

        @if(in_array($receituario->tipo, ['medico', 'talidomida']))
            {{-- DADOS PESSOAIS --}}
            <div class="mb-6 bg-blue-50 rounded-lg p-6 border-l-4 border-blue-500">
                <h3 class="text-lg font-bold text-blue-900 mb-4">DADOS PESSOAIS</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-lg">
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Nome Completo</label>
                        <p class="text-base text-gray-900 font-semibold">{{ $receituario->nome }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">CPF</label>
                        <p class="text-base text-gray-900">{{ $receituario->cpf_formatado }}</p>
                    </div>

                    @if($receituario->especialidade)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Especialidade</label>
                        <p class="text-base text-gray-900">{{ $receituario->especialidade }}</p>
                    </div>
                    @endif

                    @if($receituario->numero_conselho_classe || $receituario->numero_crm)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">
                            {{ $receituario->tipo == 'talidomida' ? 'Nº CRM' : 'Nº Conselho de Classe' }}
                        </label>
                        <p class="text-base text-gray-900">{{ $receituario->numero_crm ?? $receituario->numero_conselho_classe }}</p>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Telefone</label>
                        <p class="text-base text-gray-900">{{ $receituario->telefone }}</p>
                    </div>

                    @if($receituario->telefone2)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Telefone 2</label>
                        <p class="text-base text-gray-900">{{ $receituario->telefone2 }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ENDEREÇO --}}
            @if($receituario->endereco || $receituario->endereco_residencial || $receituario->municipio)
            <div class="mb-6 bg-green-50 rounded-lg p-6 border-l-4 border-green-500">
                <h3 class="text-lg font-bold text-green-900 mb-4">DADOS DE ENDEREÇO</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-lg">
                    @if($receituario->cep)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">CEP</label>
                        <p class="text-base text-gray-900">{{ $receituario->cep }}</p>
                    </div>
                    @endif

                    @if($receituario->municipio)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Município</label>
                        <p class="text-base text-gray-900">{{ $receituario->municipio->nome ?? $receituario->municipio }}</p>
                    </div>
                    @endif

                    @if($receituario->endereco || $receituario->endereco_residencial)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Endereço</label>
                        <p class="text-base text-gray-900">{{ $receituario->endereco ?? $receituario->endereco_residencial }}</p>
                    </div>
                    @endif

                    @if($receituario->email)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">E-mail</label>
                        <p class="text-base text-gray-900">{{ $receituario->email }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- LOCAIS DE TRABALHO --}}
            @if($receituario->locais_trabalho && count($receituario->locais_trabalho) > 0)
            <div class="mb-6 bg-purple-50 rounded-lg p-6 border-l-4 border-purple-500">
                <h3 class="text-lg font-bold text-purple-900 mb-4">LOCAIS DE TRABALHO</h3>
                
                @foreach($receituario->locais_trabalho as $local)
                    @if(!empty($local['nome']))
                    <div class="mb-3 p-4 bg-white rounded-lg border border-purple-200">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @if(!empty($local['cep']))
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">CEP</label>
                                <p class="text-base text-gray-900">{{ $local['cep'] }}</p>
                            </div>
                            @endif

                            @if(!empty($local['municipio']))
                            <div>
                                <label class="block text-sm font-medium text-gray-500 mb-1">Município</label>
                                <p class="text-base text-gray-900">{{ $local['municipio'] }}</p>
                            </div>
                            @endif

                            <div class="md:col-span-{{ empty($local['cep']) && empty($local['municipio']) ? '3' : '3' }}">
                                <label class="block text-sm font-medium text-gray-500 mb-1">Nome do Local</label>
                                <p class="text-base text-gray-900 font-semibold">{{ $local['nome'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
            @endif
        @endif

        @if(in_array($receituario->tipo, ['instituicao', 'secretaria']))
            {{-- DADOS DA INSTITUIÇÃO --}}
            <div class="mb-6 bg-blue-50 rounded-lg p-6 border-l-4 border-blue-500">
                <h3 class="text-lg font-bold text-blue-900 mb-4">DADOS DA INSTITUIÇÃO</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-lg">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Razão Social</label>
                        <p class="text-base text-gray-900 font-semibold">{{ $receituario->razao_social }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">CNPJ</label>
                        <p class="text-base text-gray-900">{{ $receituario->cnpj_formatado }}</p>
                    </div>
                </div>
            </div>

            {{-- ENDEREÇO E CONTATO --}}
            <div class="mb-6 bg-green-50 rounded-lg p-6 border-l-4 border-green-500">
                <h3 class="text-lg font-bold text-green-900 mb-4">DADOS DE ENDEREÇO E CONTATO</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-lg">
                    @if($receituario->cep)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">CEP</label>
                        <p class="text-base text-gray-900">{{ $receituario->cep }}</p>
                    </div>
                    @endif

                    @if($receituario->municipio)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Município</label>
                        <p class="text-base text-gray-900">{{ $receituario->municipio->nome ?? $receituario->municipio }}</p>
                    </div>
                    @endif

                    @if($receituario->endereco)
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Endereço</label>
                        <p class="text-base text-gray-900">{{ $receituario->endereco }}</p>
                    </div>
                    @endif

                    @if($receituario->telefone)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Telefone</label>
                        <p class="text-base text-gray-900">{{ $receituario->telefone }}</p>
                    </div>
                    @endif

                    @if($receituario->email)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">E-mail</label>
                        <p class="text-base text-gray-900">{{ $receituario->email }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- RESPONSÁVEL TÉCNICO --}}
            @if($receituario->tipo == 'instituicao' && $receituario->responsavel_nome)
            <div class="mb-6 bg-orange-50 rounded-lg p-6 border-l-4 border-orange-500">
                <h3 class="text-lg font-bold text-orange-900 mb-4">DADOS DO RESPONSÁVEL TÉCNICO</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-white p-4 rounded-lg">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500 mb-1">Nome</label>
                        <p class="text-base text-gray-900 font-semibold">{{ $receituario->responsavel_nome }}</p>
                    </div>

                    @if($receituario->responsavel_cpf)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">CPF</label>
                        <p class="text-base text-gray-900">{{ $receituario->responsavel_cpf }}</p>
                    </div>
                    @endif

                    @if($receituario->responsavel_crm)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Nº CRM-TO</label>
                        <p class="text-base text-gray-900">{{ $receituario->responsavel_crm }}</p>
                    </div>
                    @endif

                    @if($receituario->responsavel_especialidade)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Especialidade</label>
                        <p class="text-base text-gray-900">{{ $receituario->responsavel_especialidade }}</p>
                    </div>
                    @endif

                    @if($receituario->responsavel_telefone)
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Telefone</label>
                        <p class="text-base text-gray-900">{{ $receituario->responsavel_telefone }}</p>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        @endif

        {{-- OBSERVAÇÕES --}}
        @if($receituario->observacoes)
        <div class="mb-6 bg-gray-50 rounded-lg p-6 border-l-4 border-gray-500">
            <h3 class="text-lg font-bold text-gray-900 mb-4">OBSERVAÇÕES</h3>
            <p class="text-base text-gray-700 whitespace-pre-wrap">{{ $receituario->observacoes }}</p>
        </div>
        @endif

        {{-- INFORMAÇÕES DO SISTEMA --}}
        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm text-gray-600">
                <div>
                    <span class="font-medium">Cadastrado em:</span>
                    {{ $receituario->created_at->format('d/m/Y H:i') }}
                </div>
                <div>
                    <span class="font-medium">Última atualização:</span>
                    {{ $receituario->updated_at->format('d/m/Y H:i') }}
                </div>
                @if($receituario->usuarioCriacao)
                <div>
                    <span class="font-medium">Cadastrado por:</span>
                    {{ $receituario->usuarioCriacao->nome }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
