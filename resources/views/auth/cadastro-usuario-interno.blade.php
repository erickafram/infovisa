@extends('layouts.auth')

@section('title', 'Cadastro de Usuário Interno')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 py-10 px-4">
    <div class="max-w-8xl mx-auto">
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-8 md:px-10 border-b border-gray-100 bg-gradient-to-r from-blue-600 to-sky-600 text-white">
                <p class="text-sm font-medium uppercase tracking-[0.2em] text-blue-100">Cadastro interno</p>
                <h1 class="text-2xl md:text-3xl font-bold mt-2">Solicitação de acesso para equipe interna</h1>
                <p class="text-sm text-blue-100 mt-2">Preencha seus dados. O acesso só será liberado após aprovação do administrador.</p>
            </div>

            <div class="px-6 py-6 md:px-10">
                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 rounded-2xl p-4">
                        <p class="text-sm font-semibold text-red-800">Não foi possível enviar o cadastro.</p>
                        <ul class="mt-2 text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                    <div class="lg:col-span-1 bg-slate-50 rounded-2xl p-4 border border-slate-200">
                        <h2 class="text-sm font-semibold text-slate-900 mb-3">Dados do convite</h2>
                        <div class="space-y-2 text-sm text-slate-700">
                            <p><span class="font-medium">Título:</span> {{ $convite->titulo }}</p>
                            <p><span class="font-medium">Perfil:</span> {{ $nivelAcesso->label() }}</p>
                            <p><span class="font-medium">Município:</span> {{ $municipio?->nome ?? 'Escolha no formulário abaixo' }}</p>
                            <p><span class="font-medium">Validade:</span> {{ $convite->expira_em ? $convite->expira_em->format('d/m/Y H:i') : 'Sem expiração' }}</p>
                        </div>
                    </div>

                    <div class="lg:col-span-2 bg-amber-50 rounded-2xl p-4 border border-amber-200">
                        <h2 class="text-sm font-semibold text-amber-900 mb-2">Como funciona</h2>
                        <div class="space-y-2 text-sm text-amber-800">
                            <p>1. Você preenche este formulário uma única vez.</p>
                            <p>2. O sistema cria seu cadastro como pendente.</p>
                            <p>3. Um administrador revisa e aprova o acesso.</p>
                            <p>4. Depois da aprovação, seu login será feito com CPF e senha.</p>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('cadastro-interno.store', $convite->token) }}" class="space-y-8">
                    @csrf

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Dados pessoais</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome completo</label>
                                <input type="text" id="nome" name="nome" value="{{ old('nome') }}" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase">
                            </div>

                            <div>
                                <label for="cpf" class="block text-sm font-medium text-gray-700 mb-1">CPF</label>
                                <input type="text" id="cpf" name="cpf" value="{{ old('cpf') }}" maxlength="14" required
                                       placeholder="000.000.000-00"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="telefone" class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                                <input type="text" id="telefone" name="telefone" value="{{ old('telefone') }}" maxlength="15"
                                       placeholder="(00) 00000-0000"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label for="data_nascimento" class="block text-sm font-medium text-gray-700 mb-1">Data de nascimento</label>
                                <input type="date" id="data_nascimento" name="data_nascimento" value="{{ old('data_nascimento') }}" max="{{ date('Y-m-d') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Dados profissionais</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="matricula" class="block text-sm font-medium text-gray-700 mb-1">Matrícula</label>
                                <input type="text" id="matricula" name="matricula" value="{{ old('matricula') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase">
                            </div>

                            <div>
                                <label for="cargo" class="block text-sm font-medium text-gray-700 mb-1">Cargo</label>
                                <input type="text" id="cargo" name="cargo" value="{{ old('cargo') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase">
                            </div>

                            <div class="md:col-span-2">
                                <label for="setor" class="block text-sm font-medium text-gray-700 mb-1">Setor</label>
                                <input type="text" id="setor" name="setor" value="{{ old('setor') }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 uppercase">
                            </div>

                            <div class="md:col-span-2">
                                <label for="municipio_search" class="block text-sm font-medium text-gray-700 mb-1">Município</label>
                                <div class="relative" id="municipio-dropdown">
                                    <input
                                        type="text"
                                        id="municipio_search"
                                        autocomplete="off"
                                        value="{{ old('municipio_nome', old('municipio_id') ? optional($municipios->firstWhere('id', (int) old('municipio_id')))->nome : ($municipio?->nome ?? '')) }}"
                                        placeholder="Digite para pesquisar o município..."
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    >
                                    <div id="municipio-results"
                                         class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-lg max-h-60 overflow-y-auto hidden">
                                    </div>
                                    <input type="hidden" id="municipio_id" name="municipio_id" value="{{ old('municipio_id', $municipio?->id) }}">
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Digite o nome do município para pesquisar e selecione na lista.</p>
                                @error('municipio_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Senha de acesso</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                                <input type="password" id="password" name="password" required minlength="8"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <p class="mt-1 text-xs text-gray-500">Use pelo menos 8 caracteres.</p>
                            </div>

                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar senha</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between pt-2">
                        <a href="{{ route('login') }}" class="text-sm text-gray-500 hover:text-gray-700">Voltar para o login</a>
                        <button type="submit"
                                class="px-6 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition font-semibold">
                            Enviar cadastro para aprovação
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const cpfInput = document.getElementById('cpf');
    const telefoneInput = document.getElementById('telefone');
    const municipioSearchInput = document.getElementById('municipio_search');
    const municipioIdInput = document.getElementById('municipio_id');
    const municipioResults = document.getElementById('municipio-results');
    const municipios = @json($municipios->map(fn ($item) => ['id' => $item->id, 'nome' => $item->nome])->values());

    cpfInput?.addEventListener('input', function () {
        let value = this.value.replace(/\D/g, '').slice(0, 11);
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        this.value = value;
    });

    telefoneInput?.addEventListener('input', function () {
        let value = this.value.replace(/\D/g, '').slice(0, 11);
        if (value.length > 10) {
            value = value.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3');
        } else if (value.length > 6) {
            value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
        } else if (value.length > 2) {
            value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
        } else if (value.length > 0) {
            value = value.replace(/^(\d*)/, '($1');
        }
        this.value = value;
    });

    // Dropdown de município com pesquisa
    function renderMunicipioResults(filtrados) {
        municipioResults.innerHTML = '';
        if (filtrados.length === 0) {
            municipioResults.innerHTML = '<div class="px-4 py-3 text-sm text-gray-500">Nenhum município encontrado.</div>';
            municipioResults.classList.remove('hidden');
            return;
        }
        filtrados.slice(0, 30).forEach(function (item) {
            const div = document.createElement('div');
            div.textContent = item.nome;
            div.className = 'px-4 py-2.5 text-sm text-gray-700 cursor-pointer hover:bg-blue-50 hover:text-blue-700 transition';
            div.addEventListener('mousedown', function (e) {
                e.preventDefault();
                municipioSearchInput.value = item.nome;
                municipioIdInput.value = item.id;
                municipioResults.classList.add('hidden');
                municipioSearchInput.classList.remove('border-gray-300');
                municipioSearchInput.classList.add('border-green-400');
            });
            municipioResults.appendChild(div);
        });
        municipioResults.classList.remove('hidden');
    }

    municipioSearchInput?.addEventListener('input', function () {
        const termo = this.value.trim().toLowerCase();
        municipioIdInput.value = '';
        municipioSearchInput.classList.remove('border-green-400');
        municipioSearchInput.classList.add('border-gray-300');

        if (termo.length < 2) {
            municipioResults.classList.add('hidden');
            return;
        }

        const filtrados = municipios.filter(function (m) {
            return m.nome.toLowerCase().indexOf(termo) !== -1;
        });
        renderMunicipioResults(filtrados);
    });

    municipioSearchInput?.addEventListener('focus', function () {
        const termo = this.value.trim().toLowerCase();
        if (termo.length >= 2) {
            const filtrados = municipios.filter(function (m) {
                return m.nome.toLowerCase().indexOf(termo) !== -1;
            });
            renderMunicipioResults(filtrados);
        }
    });

    municipioSearchInput?.addEventListener('blur', function () {
        setTimeout(function () {
            municipioResults.classList.add('hidden');
        }, 200);
    });

    // Se já tem valor selecionado, marca como verde
    if (municipioIdInput?.value) {
        municipioSearchInput?.classList.remove('border-gray-300');
        municipioSearchInput?.classList.add('border-green-400');
    }
</script>
@endsection
