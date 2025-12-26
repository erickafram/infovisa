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

    {{-- Vincular Novo Usuário --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Vincular Novo Usuário
        </h3>

        <form action="{{ route('company.estabelecimentos.usuarios.store', $estabelecimento->id) }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                        <option value="proprietario">Proprietário</option>
                        <option value="responsavel_legal">Responsável Legal</option>
                        <option value="responsavel_tecnico">Responsável Técnico</option>
                        <option value="contador">Contador</option>
                        <option value="procurador">Procurador</option>
                        <option value="colaborador">Colaborador</option>
                        <option value="outro">Outro</option>
                    </select>
                    @error('tipo_vinculo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="observacao" class="block text-sm font-medium text-gray-700 mb-2">Observação</label>
                    <input type="text" id="observacao" name="observacao" maxlength="500"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Informações adicionais (opcional)">
                    @error('observacao')
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

    {{-- Lista de Usuários Vinculados --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                Usuários Vinculados ({{ $estabelecimento->usuariosVinculados->count() }})
            </h3>
        </div>

        @if($estabelecimento->usuariosVinculados->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($estabelecimento->usuariosVinculados as $usuario)
                <div class="p-6 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between gap-4">
                        {{-- Informações do Usuário --}}
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <span class="text-blue-600 font-semibold text-sm">
                                        {{ strtoupper(substr($usuario->nome, 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <h4 class="text-base font-semibold text-gray-900">{{ $usuario->nome }}</h4>
                                    <p class="text-sm text-gray-600">{{ $usuario->email }}</p>
                                </div>
                            </div>

                            <div class="ml-13 grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500">Telefone:</span>
                                    <p class="font-medium text-gray-900">{{ $usuario->telefone_formatado ?? $usuario->telefone ?? '-' }}</p>
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

                        {{-- Ações --}}
                        <div class="flex gap-2">
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
                        </div>
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
                
                fetch(`/company/usuarios-externos/buscar?q=${encodeURIComponent(this.buscaUsuario)}&estabelecimento_id=${this.estabelecimentoId}`)
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
