@extends('layouts.company')

@section('title', 'Usuários Vinculados')
@section('page-title', 'Usuários Vinculados ao Estabelecimento')

@section('content')
<div class="max-w-8xl mx-auto space-y-6" x-data="usuariosVinculo()">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('company.estabelecimentos.show', $estabelecimento->id) }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 hover:text-gray-900 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-900">Usuários Vinculados</h2>
                <p class="text-sm text-gray-600 mt-1">{{ $estabelecimento->nome_fantasia }} - {{ $estabelecimento->documento_formatado }}</p>
            </div>
        </div>
    </div>

    {{-- Mensagens --}}
    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    {{-- Vincular Novo Usuário - Apenas para gestores --}}
    @if(!$ehVisualizador)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Vincular Novo Usuário
        </h3>

        <form action="{{ route('company.estabelecimentos.usuarios.store', $estabelecimento->id) }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-1">
                    <label for="busca_usuario" class="block text-sm font-medium text-gray-700 mb-2">Usuário *</label>
                    <div class="relative">
                        <input type="text" 
                               id="busca_usuario"
                               x-model="buscaUsuario"
                               @input="buscarUsuarios()"
                               @focus="mostrarResultados = true"
                               placeholder="Digite nome, CPF ou e-mail..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               autocomplete="off">
                        
                        <input type="hidden" id="usuario_externo_id" name="usuario_externo_id" x-model="usuarioSelecionadoId">
                        <input type="hidden" name="email" x-model="usuarioSelecionadoEmail">
                        
                        {{-- Loading --}}
                        <div x-show="carregando" class="absolute right-3 top-3">
                            <svg class="animate-spin h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                        
                        {{-- Dropdown de Resultados --}}
                        <div x-show="mostrarResultados && resultados.length > 0"
                             @click.away="mostrarResultados = false"
                             class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                             style="display: none;">
                            <template x-for="usuario in resultados" :key="usuario.id">
                                <div @click="selecionarUsuario(usuario)"
                                     class="px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-0">
                                    <div class="font-medium text-gray-900" x-text="usuario.nome"></div>
                                    <div class="text-sm text-gray-600">
                                        <span x-text="usuario.cpf"></span> • <span x-text="usuario.email"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        {{-- Mensagem de nenhum resultado --}}
                        <div x-show="mostrarResultados && !carregando && resultados.length === 0 && buscaUsuario.length >= 3"
                             class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg p-4 text-center text-gray-500 text-sm"
                             style="display: none;">
                            Nenhum usuário encontrado
                        </div>
                        
                        {{-- Usuário Selecionado --}}
                        <div x-show="usuarioSelecionado"
                             class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg flex items-center justify-between"
                             style="display: none;">
                            <div>
                                <div class="font-medium text-blue-900" x-text="usuarioSelecionado?.nome"></div>
                                <div class="text-sm text-blue-700" x-text="usuarioSelecionado?.email"></div>
                            </div>
                            <button type="button" @click="limparSelecao()" class="text-blue-600 hover:text-blue-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @error('usuario_externo_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tipo_vinculo" class="block text-sm font-medium text-gray-700 mb-2">Tipo de Vínculo *</label>
                    <select id="tipo_vinculo" name="tipo_vinculo" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione</option>
                        <option value="funcionario">Funcionário</option>
                        <option value="contador">Contador</option>
                    </select>
                    @error('tipo_vinculo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="nivel_acesso" class="block text-sm font-medium text-gray-700 mb-2">Nível de Acesso *</label>
                    <select id="nivel_acesso" name="nivel_acesso" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="gestor">Gestor (Acesso Total)</option>
                        <option value="visualizador">Visualizador (Somente Leitura)</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        <strong>Gestor:</strong> pode criar processos, editar cadastro, anexar documentos.<br>
                        <strong>Visualizador:</strong> apenas visualiza informações.
                    </p>
                    @error('nivel_acesso')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Vincular Usuário
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- Lista de Usuários Vinculados --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                Usuários Vinculados ({{ $estabelecimento->usuariosVinculados->count() + ($criador && !$criadorVinculado ? 1 : 0) }})
            </h3>
        </div>

        @if($estabelecimento->usuariosVinculados->count() > 0 || ($criador && !$criadorVinculado))
            <div class="divide-y divide-gray-200">
                {{-- Mostra o criador primeiro (se não estiver na lista de vinculados) --}}
                @if($criador && !$criadorVinculado)
                <div class="p-6 hover:bg-gray-50 transition-colors bg-amber-50">
                    <div class="flex items-start justify-between gap-4">
                        {{-- Informações do Usuário Criador --}}
                        @php
                            $ehProprioUsuarioCriador = $criador->id == $usuarioAtualId;
                            $podeVerDadosCriador = !$ehVisualizador || $ehProprioUsuarioCriador;
                        @endphp
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center">
                                    <span class="text-amber-600 font-semibold text-sm">
                                        {{ strtoupper(substr($criador->nome, 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="text-base font-semibold text-gray-900">{{ $criador->nome }}</h4>
                                    @if($podeVerDadosCriador)
                                        <p class="text-sm text-gray-600">{{ $criador->email }}</p>
                                        <p class="text-sm text-gray-500">CPF: {{ $criador->cpf_formatado ?? $criador->cpf ?? '-' }}</p>
                                    @else
                                        <p class="text-sm text-gray-400 italic">Dados protegidos</p>
                                    @endif
                                </div>
                            </div>

                            <div class="ml-13 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Telefone:</span>
                                    @if($podeVerDadosCriador)
                                        <p class="font-medium text-gray-900">{{ $criador->telefone_formatado ?? $criador->telefone ?? '-' }}</p>
                                    @else
                                        <p class="font-medium text-gray-400 italic">***</p>
                                    @endif
                                </div>
                                <div>
                                    <span class="text-gray-500">Tipo de Vínculo:</span>
                                    <p class="font-medium text-gray-900">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800">
                                            Criador do Cadastro
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <span class="text-gray-500">Nível de Acesso:</span>
                                    <p class="font-medium">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                            Acesso Total (Criador)
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <span class="text-gray-500">Cadastrado em:</span>
                                    <p class="font-medium text-gray-900">{{ $estabelecimento->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Sem ações para o criador --}}
                        <div class="flex gap-2">
                            <span class="inline-flex items-center gap-1 px-3 py-2 bg-gray-200 text-gray-500 text-sm font-medium rounded cursor-not-allowed" title="O criador do cadastro não pode ser desvinculado">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Protegido
                            </span>
                        </div>
                    </div>
                </div>
                @endif

                @foreach($estabelecimento->usuariosVinculados as $usuario)
                @php
                    $isCriador = $usuario->id == $estabelecimento->usuario_externo_id;
                    $ehProprioUsuario = $usuario->id == $usuarioAtualId;
                    $podeVerDadosUsuario = !$ehVisualizador || $ehProprioUsuario;
                @endphp
                <div class="p-6 hover:bg-gray-50 transition-colors {{ $isCriador ? 'bg-amber-50' : '' }}">
                    <div class="flex items-start justify-between gap-4">
                        {{-- Informações do Usuário --}}
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="h-10 w-10 rounded-full {{ $isCriador ? 'bg-amber-100' : 'bg-blue-100' }} flex items-center justify-center">
                                    <span class="{{ $isCriador ? 'text-amber-600' : 'text-blue-600' }} font-semibold text-sm">
                                        {{ strtoupper(substr($usuario->nome, 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="text-base font-semibold text-gray-900">
                                        {{ $usuario->nome }}
                                        @if($isCriador)
                                            <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-amber-100 text-amber-800">
                                                Criador
                                            </span>
                                        @endif
                                        @if($ehProprioUsuario)
                                            <span class="ml-2 px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                                Você
                                            </span>
                                        @endif
                                    </h4>
                                    @if($podeVerDadosUsuario)
                                        <p class="text-sm text-gray-600">{{ $usuario->email }}</p>
                                        <p class="text-sm text-gray-500">CPF: {{ $usuario->cpf_formatado ?? $usuario->cpf ?? '-' }}</p>
                                    @else
                                        <p class="text-sm text-gray-400 italic">Dados protegidos</p>
                                    @endif
                                </div>
                            </div>

                            <div class="ml-13 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Telefone:</span>
                                    @if($podeVerDadosUsuario)
                                        <p class="font-medium text-gray-900">{{ $usuario->telefone_formatado ?? $usuario->telefone ?? '-' }}</p>
                                    @else
                                        <p class="font-medium text-gray-400 italic">***</p>
                                    @endif
                                </div>
                                <div>
                                    <span class="text-gray-500">Tipo de Vínculo:</span>
                                    <p class="font-medium text-gray-900">
                                        @php
                                            $tipos = [
                                                'proprietario' => 'Proprietário',
                                                'responsavel_legal' => 'Responsável Legal',
                                                'responsavel_tecnico' => 'Responsável Técnico',
                                                'contador' => 'Contador',
                                                'procurador' => 'Procurador',
                                                'colaborador' => 'Colaborador',
                                                'funcionario' => 'Funcionário',
                                                'socio' => 'Sócio',
                                                'representante' => 'Representante',
                                                'outro' => 'Outro'
                                            ];
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                            {{ $tipos[$usuario->pivot->tipo_vinculo] ?? ucfirst($usuario->pivot->tipo_vinculo) }}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <span class="text-gray-500">Nível de Acesso:</span>
                                    @if($isCriador)
                                        <p class="font-medium">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                                Acesso Total (Criador)
                                            </span>
                                        </p>
                                    @else
                                        @php
                                            $nivelAcesso = $usuario->pivot->nivel_acesso ?? 'gestor';
                                        @endphp
                                        <p class="font-medium">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $nivelAcesso === 'gestor' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $nivelAcesso === 'gestor' ? 'Gestor (Acesso Total)' : 'Visualizador (Somente Leitura)' }}
                                            </span>
                                        </p>
                                    @endif
                                </div>
                                <div>
                                    <span class="text-gray-500">Vinculado em:</span>
                                    <p class="font-medium text-gray-900">{{ $usuario->pivot->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>

                            @if($usuario->pivot->observacao)
                            <div class="ml-13 mt-2 text-sm">
                                <span class="text-gray-500">Observação:</span>
                                <p class="text-gray-700">{{ $usuario->pivot->observacao }}</p>
                            </div>
                            @endif
                        </div>

                        {{-- Ações - Apenas para gestores --}}
                        @if(!$ehVisualizador)
                        <div class="flex gap-2">
                            @if($isCriador)
                                <span class="inline-flex items-center gap-1 px-3 py-2 bg-gray-200 text-gray-500 text-sm font-medium rounded cursor-not-allowed" title="O criador do cadastro não pode ser desvinculado">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Protegido
                                </span>
                            @else
                                {{-- Botão Editar Nível de Acesso --}}
                                <button type="button"
                                        @click="modalUsuario = { id: {{ $usuario->id }}, nome: '{{ $usuario->nome }}', nivelAcesso: '{{ $usuario->pivot->nivel_acesso ?? 'gestor' }}' }; showModalNivelAcesso = true"
                                        class="inline-flex items-center gap-1 px-3 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Editar
                                </button>
                                
                                <form action="{{ route('company.estabelecimentos.usuarios.destroy', [$estabelecimento->id, $usuario->id]) }}" 
                                      method="POST"
                                      onsubmit="return confirm('Tem certeza que deseja desvincular este usuário?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-2 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Desvincular
                                    </button>
                                </form>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum usuário vinculado</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Vincule usuários externos para dar acesso a este estabelecimento.
                </p>
            </div>
        @endif
    </div>

    {{-- Modal de Edição de Nível de Acesso --}}
    <div x-show="showModalNivelAcesso" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black/50" @click="showModalNivelAcesso = false"></div>
        
        {{-- Modal --}}
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md transform transition-all"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.away="showModalNivelAcesso = false">
                
                {{-- Header --}}
                <div class="flex items-center justify-between p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Editar Nível de Acesso</h3>
                    <button type="button" @click="showModalNivelAcesso = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                {{-- Body --}}
                <form :action="`{{ url('company/estabelecimentos/' . $estabelecimento->id . '/usuarios') }}/${modalUsuario?.id}`" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="p-4 space-y-4">
                        <div class="text-center mb-4">
                            <p class="text-sm text-gray-600">Usuário:</p>
                            <p class="font-semibold text-gray-900" x-text="modalUsuario?.nome"></p>
                        </div>
                        
                        <div class="space-y-3">
                            <label class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition-all"
                                   :class="modalUsuario?.nivelAcesso === 'gestor' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" 
                                       name="nivel_acesso" 
                                       value="gestor"
                                       x-model="modalUsuario.nivelAcesso"
                                       class="mt-0.5 h-4 w-4 text-green-600 border-gray-300 focus:ring-green-500">
                                <div>
                                    <span class="font-semibold text-gray-900">Gestor (Acesso Total)</span>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Pode criar processos, editar cadastro, anexar documentos e realizar todas as ações no estabelecimento.
                                    </p>
                                </div>
                            </label>
                            
                            <label class="flex items-start gap-3 p-4 border-2 rounded-lg cursor-pointer transition-all"
                                   :class="modalUsuario?.nivelAcesso === 'visualizador' ? 'border-yellow-500 bg-yellow-50' : 'border-gray-200 hover:border-gray-300'">
                                <input type="radio" 
                                       name="nivel_acesso" 
                                       value="visualizador"
                                       x-model="modalUsuario.nivelAcesso"
                                       class="mt-0.5 h-4 w-4 text-yellow-600 border-gray-300 focus:ring-yellow-500">
                                <div>
                                    <span class="font-semibold text-gray-900">Visualizador (Somente Leitura)</span>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Pode apenas visualizar informações. Não pode editar, anexar documentos ou abrir processos.
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>
                    
                    {{-- Footer --}}
                    <div class="flex justify-end gap-3 p-4 border-t border-gray-200 bg-gray-50 rounded-b-xl">
                        <button type="button" 
                                @click="showModalNivelAcesso = false"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function usuariosVinculo() {
    return {
        buscaUsuario: '',
        resultados: [],
        mostrarResultados: false,
        carregando: false,
        usuarioSelecionado: null,
        usuarioSelecionadoId: '',
        usuarioSelecionadoEmail: '',
        timeoutBusca: null,
        estabelecimentoId: {{ $estabelecimento->id }},
        
        // Modal de nível de acesso
        showModalNivelAcesso: false,
        modalUsuario: null,

        buscarUsuarios() {
            // Limpa timeout anterior
            clearTimeout(this.timeoutBusca);
            
            // Se busca vazia, limpa resultados
            if (this.buscaUsuario.length < 3) {
                this.resultados = [];
                return;
            }
            
            // Aguarda 300ms após parar de digitar
            this.timeoutBusca = setTimeout(() => {
                this.carregando = true;
                
                fetch(`${window.APP_URL}/company/usuarios-externos/buscar?q=${encodeURIComponent(this.buscaUsuario)}&estabelecimento_id=${this.estabelecimentoId}`)
                    .then(response => response.json())
                    .then(data => {
                        this.resultados = data;
                        this.mostrarResultados = true;
                        this.carregando = false;
                    })
                    .catch(error => {
                        console.error('Erro ao buscar usuários:', error);
                        this.carregando = false;
                    });
            }, 300);
        },

        selecionarUsuario(usuario) {
            this.usuarioSelecionado = usuario;
            this.usuarioSelecionadoId = usuario.id;
            this.usuarioSelecionadoEmail = usuario.email;
            this.buscaUsuario = usuario.nome;
            this.mostrarResultados = false;
            this.resultados = [];
        },

        limparSelecao() {
            this.usuarioSelecionado = null;
            this.usuarioSelecionadoId = '';
            this.usuarioSelecionadoEmail = '';
            this.buscaUsuario = '';
            this.resultados = [];
        }
    }
}
</script>
@endsection
