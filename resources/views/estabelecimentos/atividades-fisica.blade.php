@extends('layouts.admin')

@section('title', 'Gerenciar Atividades Econômicas')
@section('page-title', 'Gerenciar Atividades Econômicas')

@section('content')
<div class="">
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Atividades Econômicas (CNAE)</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $estabelecimento->nome_fantasia }}</p>
            </div>
        </div>
    </div>

    {{-- Mensagens de sucesso/erro --}}
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
            <div class="flex">
                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="ml-3 text-sm text-red-700">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.estabelecimentos.atividades.update', $estabelecimento->id) }}" class="space-y-6">
        @csrf

        {{-- Buscar CNAE pela API do IBGE --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Buscar Nova Atividade (CNAE)</h3>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Código CNAE (7 dígitos)</label>
                <div class="flex gap-2">
                    <input type="text" id="cnae_busca" placeholder="Ex: 5611201" maxlength="7" class="flex-1 px-3 py-2 border rounded-md">
                    <button type="button" id="btn_buscar_cnae" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Buscar
                    </button>
                </div>
                <p id="cnae_erro" class="text-red-500 text-xs mt-1 hidden"></p>
                <p id="cnae_sucesso" class="text-green-600 text-xs mt-1 hidden"></p>
            </div>
        </div>

        {{-- Lista de Atividades Cadastradas --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Atividades Cadastradas</h3>
            <div id="cnaes_lista" class="space-y-2">
                @if($estabelecimento->atividades_exercidas && count($estabelecimento->atividades_exercidas) > 0)
                    @foreach($estabelecimento->atividades_exercidas as $index => $atividade)
                        <div class="flex items-start justify-between p-4 bg-gray-50 rounded-lg border">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-mono text-sm font-semibold text-blue-600">{{ $atividade['codigo'] ?? '' }}</span>
                                    @if(isset($atividade['principal']) && $atividade['principal'])
                                        <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs font-medium rounded">Principal</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-900 font-medium">{{ strtoupper($atividade['descricao'] ?? '') }}</p>
                                @if(isset($atividade['grupo']))
                                    <p class="text-xs text-gray-500 mt-1">Grupo: {{ $atividade['grupo'] }}</p>
                                @endif
                            </div>
                            <button type="button" onclick="removerCnae({{ $index }})" class="ml-4 text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                @else
                    <p class="text-sm text-gray-500">Nenhuma atividade cadastrada.</p>
                @endif
            </div>
            <input type="hidden" name="atividades_exercidas" id="cnaes_input" value="{{ json_encode($estabelecimento->atividades_exercidas ?? []) }}">
        </div>

        <div class="flex justify-between pt-6 pb-8">
            <a href="{{ route('admin.estabelecimentos.show', $estabelecimento->id) }}" class="px-6 py-2.5 border text-gray-700 rounded-md hover:bg-gray-50">Cancelar</a>
            <button type="submit" class="px-6 py-2.5 bg-green-600 text-white rounded-md hover:bg-green-700">Salvar Atividades</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Carrega atividades existentes
    let cnaes = @json($estabelecimento->atividades_exercidas ?? []);
    
    // Busca CNAE ao clicar no botão
    document.getElementById('btn_buscar_cnae').addEventListener('click', buscarCnae);
    
    // Busca CNAE ao pressionar Enter
    document.getElementById('cnae_busca').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            buscarCnae();
        }
    });
    
    function buscarCnae() {
        const codigo = document.getElementById('cnae_busca').value.trim();
        const erroEl = document.getElementById('cnae_erro');
        const sucessoEl = document.getElementById('cnae_sucesso');
        
        erroEl.classList.add('hidden');
        sucessoEl.classList.add('hidden');
        
        if (codigo.length !== 7 || !/^\d+$/.test(codigo)) {
            erroEl.textContent = 'Digite um código CNAE válido com 7 dígitos';
            erroEl.classList.remove('hidden');
            return;
        }
        
        // Verifica se já existe (compara como string)
        if (cnaes.some(c => String(c.codigo) === String(codigo))) {
            erroEl.textContent = 'Este CNAE já foi adicionado';
            erroEl.classList.remove('hidden');
            return;
        }
        
        // Busca na API do IBGE
        fetch(`https://servicodados.ibge.gov.br/api/v2/cnae/subclasses/${codigo}`)
            .then(response => {
                if (!response.ok) throw new Error('CNAE não encontrado');
                return response.json();
            })
            .then(data => {
                // A API retorna um objeto único, não um array
                if (data && data.id) {
                    const novoCnae = {
                        codigo: String(data.id), // Garante que é string
                        descricao: data.descricao,
                        grupo: data.classe?.grupo?.descricao || '',
                        principal: cnaes.length === 0 // Primeiro é principal
                    };
                    
                    cnaes.push(novoCnae);
                    atualizarLista();
                    
                    document.getElementById('cnae_busca').value = '';
                    sucessoEl.textContent = '✓ CNAE adicionado com sucesso!';
                    sucessoEl.classList.remove('hidden');
                } else {
                    throw new Error('CNAE não encontrado');
                }
            })
            .catch(error => {
                erroEl.textContent = 'CNAE não encontrado. Verifique o código e tente novamente.';
                erroEl.classList.remove('hidden');
            });
    }
    
    function atualizarLista() {
        const lista = document.getElementById('cnaes_lista');
        if (cnaes.length === 0) {
            lista.innerHTML = '<p class="text-sm text-gray-500">Nenhuma atividade cadastrada.</p>';
        } else {
            lista.innerHTML = cnaes.map((cnae, index) => `
                <div class="flex items-start justify-between p-4 bg-gray-50 rounded-lg border">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-mono text-sm font-semibold text-blue-600">${cnae.codigo}</span>
                            ${cnae.principal ? '<span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs font-medium rounded">Principal</span>' : ''}
                        </div>
                        <p class="text-sm text-gray-900 font-medium">${cnae.descricao.toUpperCase()}</p>
                        ${cnae.grupo ? `<p class="text-xs text-gray-500 mt-1">Grupo: ${cnae.grupo}</p>` : ''}
                    </div>
                    <button type="button" onclick="removerCnae(${index})" class="ml-4 text-red-600 hover:text-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            `).join('');
        }
        document.getElementById('cnaes_input').value = JSON.stringify(cnaes);
    }
    
    window.removerCnae = function(index) {
        if (confirm('Deseja remover esta atividade?')) {
            const eraPrincipal = cnaes[index].principal;
            cnaes.splice(index, 1);
            
            // Se removeu a principal e ainda tem outras, marca a primeira como principal
            if (eraPrincipal && cnaes.length > 0) {
                cnaes[0].principal = true;
            }
            
            atualizarLista();
        }
    };
});
</script>
@endsection
