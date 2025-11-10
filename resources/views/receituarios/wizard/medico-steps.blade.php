{{-- STEP 1: DADOS PESSOAIS --}}
<div x-show="currentStep === 0" x-transition>
    <div class="mb-6">
        <h3 class="text-2xl font-bold text-blue-900 mb-2 flex items-center gap-2">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Passo 1: Dados Pessoais
        </h3>
        <p class="text-gray-600">Preencha as informações pessoais do profissional</p>
    </div>

    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Nome Completo <span class="text-red-500">*</span>
                </label>
                <input type="text" name="nome" value="{{ old('nome') }}" required
                       style="text-transform: uppercase;"
                       placeholder="Digite o nome completo"
                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('nome')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    CPF <span class="text-red-500">*</span>
                </label>
                <input type="text" name="cpf" value="{{ old('cpf') }}" required 
                       x-mask="999.999.999-99"
                       placeholder="000.000.000-00"
                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('cpf')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Telefone <span class="text-red-500">*</span>
                </label>
                <input type="text" name="telefone" value="{{ old('telefone') }}" required
                       x-mask="(99) 99999-9999"
                       placeholder="(00) 00000-0000"
                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('telefone')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
            </div>

            @if($tipo == 'talidomida')
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Telefone 2 (Opcional)
                </label>
                <input type="text" name="telefone2" value="{{ old('telefone2') }}"
                       x-mask="(99) 99999-9999"
                       placeholder="(00) 00000-0000"
                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    E-mail
                </label>
                <input type="email" name="email" value="{{ old('email') }}"
                       placeholder="email@exemplo.com"
                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            @endif

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Especialidade
                </label>
                <select name="especialidade" 
                        class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Selecione...</option>
                    <option value="ACUPUNTURA">ACUPUNTURA</option>
                    <option value="ALERGIA E IMUNOLOGIA">ALERGIA E IMUNOLOGIA</option>
                    <option value="ANESTESIOLOGIA">ANESTESIOLOGIA</option>
                    <option value="ANGIOLOGIA">ANGIOLOGIA</option>
                    <option value="CARDIOLOGIA">CARDIOLOGIA</option>
                    <option value="CIRURGIA CARDIOVASCULAR">CIRURGIA CARDIOVASCULAR</option>
                    <option value="CIRURGIA DA MÃO">CIRURGIA DA MÃO</option>
                    <option value="CIRURGIA DE CABEÇA E PESCOÇO">CIRURGIA DE CABEÇA E PESCOÇO</option>
                    <option value="CIRURGIA DO APARELHO DIGESTIVO">CIRURGIA DO APARELHO DIGESTIVO</option>
                    <option value="CIRURGIA GERAL">CIRURGIA GERAL</option>
                    <option value="CIRURGIA ONCOLÓGICA">CIRURGIA ONCOLÓGICA</option>
                    <option value="CIRURGIA PEDIÁTRICA">CIRURGIA PEDIÁTRICA</option>
                    <option value="CIRURGIA PLÁSTICA">CIRURGIA PLÁSTICA</option>
                    <option value="CIRURGIA TORÁCICA">CIRURGIA TORÁCICA</option>
                    <option value="CIRURGIA VASCULAR">CIRURGIA VASCULAR</option>
                    <option value="CLÍNICA MÉDICA">CLÍNICA MÉDICA</option>
                    <option value="COLOPROCTOLOGIA">COLOPROCTOLOGIA</option>
                    <option value="DERMATOLOGIA">DERMATOLOGIA</option>
                    <option value="ENDOCRINOLOGIA E METABOLOGIA">ENDOCRINOLOGIA E METABOLOGIA</option>
                    <option value="ENDOSCOPIA">ENDOSCOPIA</option>
                    <option value="GASTROENTEROLOGIA">GASTROENTEROLOGIA</option>
                    <option value="GENÉTICA MÉDICA">GENÉTICA MÉDICA</option>
                    <option value="GERIATRIA">GERIATRIA</option>
                    <option value="GINECOLOGIA E OBSTETRÍCIA">GINECOLOGIA E OBSTETRÍCIA</option>
                    <option value="HEMATOLOGIA E HEMOTERAPIA">HEMATOLOGIA E HEMOTERAPIA</option>
                    <option value="HOMEOPATIA">HOMEOPATIA</option>
                    <option value="INFECTOLOGIA">INFECTOLOGIA</option>
                    <option value="MASTOLOGIA">MASTOLOGIA</option>
                    <option value="MEDICINA DE EMERGÊNCIA">MEDICINA DE EMERGÊNCIA</option>
                    <option value="MEDICINA DE FAMÍLIA E COMUNIDADE">MEDICINA DE FAMÍLIA E COMUNIDADE</option>
                    <option value="MEDICINA DO TRABALHO">MEDICINA DO TRABALHO</option>
                    <option value="MEDICINA DE TRÁFEGO">MEDICINA DE TRÁFEGO</option>
                    <option value="MEDICINA ESPORTIVA">MEDICINA ESPORTIVA</option>
                    <option value="MEDICINA FÍSICA E REABILITAÇÃO">MEDICINA FÍSICA E REABILITAÇÃO</option>
                    <option value="MEDICINA INTENSIVA">MEDICINA INTENSIVA</option>
                    <option value="MEDICINA LEGAL E PERÍCIA MÉDICA">MEDICINA LEGAL E PERÍCIA MÉDICA</option>
                    <option value="MEDICINA NUCLEAR">MEDICINA NUCLEAR</option>
                    <option value="MEDICINA PREVENTIVA E SOCIAL">MEDICINA PREVENTIVA E SOCIAL</option>
                    <option value="NEFROLOGIA">NEFROLOGIA</option>
                    <option value="NEUROCIRURGIA">NEUROCIRURGIA</option>
                    <option value="NEUROLOGIA">NEUROLOGIA</option>
                    <option value="NUTROLOGIA">NUTROLOGIA</option>
                    <option value="OFTALMOLOGIA">OFTALMOLOGIA</option>
                    <option value="ONCOLOGIA CLÍNICA">ONCOLOGIA CLÍNICA</option>
                    <option value="ORTOPEDIA E TRAUMATOLOGIA">ORTOPEDIA E TRAUMATOLOGIA</option>
                    <option value="OTORRINOLARINGOLOGIA">OTORRINOLARINGOLOGIA</option>
                    <option value="PATOLOGIA">PATOLOGIA</option>
                    <option value="PATOLOGIA CLÍNICA/MEDICINA LABORATORIAL">PATOLOGIA CLÍNICA/MEDICINA LABORATORIAL</option>
                    <option value="PEDIATRIA">PEDIATRIA</option>
                    <option value="PNEUMOLOGIA">PNEUMOLOGIA</option>
                    <option value="PSIQUIATRIA">PSIQUIATRIA</option>
                    <option value="RADIOLOGIA E DIAGNÓSTICO POR IMAGEM">RADIOLOGIA E DIAGNÓSTICO POR IMAGEM</option>
                    <option value="RADIOTERAPIA">RADIOTERAPIA</option>
                    <option value="REUMATOLOGIA">REUMATOLOGIA</option>
                    <option value="UROLOGIA">UROLOGIA</option>
                    <option value="ODONTOLOGIA">ODONTOLOGIA</option>
                    <option value="MEDICINA VETERINÁRIA">MEDICINA VETERINÁRIA</option>
                    <option value="OUTRAS">OUTRAS</option>
                </select>
                @error('especialidade')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    @if($tipo == 'talidomida')
                        Nº CRM
                    @else
                        Nº Conselho de Classe
                    @endif
                </label>
                <input type="text" name="{{ $tipo == 'talidomida' ? 'numero_crm' : 'numero_conselho_classe' }}" 
                       value="{{ old($tipo == 'talidomida' ? 'numero_crm' : 'numero_conselho_classe') }}"
                       style="text-transform: uppercase;"
                       placeholder="Ex: CRM-TO 1234"
                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>
    </div>
</div>

{{-- STEP 2: ENDEREÇO --}}
<div x-show="currentStep === 1" x-transition x-data="cepLookup()">
    <div class="mb-6">
        <h3 class="text-2xl font-bold text-green-900 mb-2 flex items-center gap-2">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Passo 2: Endereço {{ $tipo == 'talidomida' ? 'Residencial' : '' }}
        </h3>
        <p class="text-gray-600">Informe o endereço {{ $tipo == 'talidomida' ? 'residencial' : '' }} do profissional</p>
    </div>

    <div class="space-y-6">
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
            <p class="text-sm text-yellow-800">
                💡 <strong>Dica:</strong> Digite o CEP e clique em "Buscar" para preencher automaticamente o endereço
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    CEP
                </label>
                <div class="flex gap-2">
                    <input type="text" name="cep" x-model="cep" 
                           x-mask="99999-999"
                           placeholder="00000-000"
                           class="flex-1 px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="button" @click="buscarCep()" 
                            class="px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                        Buscar
                    </button>
                </div>
                @error('cep')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Endereço {{ $tipo == 'talidomida' ? 'Residencial' : '' }}
                </label>
                <input type="text" name="{{ $tipo == 'talidomida' ? 'endereco_residencial' : 'endereco' }}" 
                       x-model="endereco"
                       style="text-transform: uppercase;"
                       placeholder="Rua, Avenida, Quadra, Lote, etc."
                       class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Município
                </label>
                <select name="municipio_id" x-model="municipioId"
                        class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Selecione...</option>
                    @foreach($municipios as $municipio)
                        <option value="{{ $municipio->id }}">{{ $municipio->nome }}</option>
                    @endforeach
                </select>
                @error('municipio_id')<span class="text-red-500 text-sm">{{ $message }}</span>@enderror
            </div>
        </div>
    </div>
</div>

{{-- STEP 3: LOCAIS DE TRABALHO --}}
<div x-show="currentStep === 2" x-transition x-data="locaisTrabalho()">
    <div class="mb-6">
        <h3 class="text-2xl font-bold text-purple-900 mb-2 flex items-center gap-2">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Passo 3: Locais de Trabalho
        </h3>
        <p class="text-gray-600">Adicione os locais onde o profissional atua (opcional)</p>
    </div>

    <div class="space-y-6">
        <template x-for="(local, index) in locais" :key="index">
            <div class="bg-gray-50 p-6 rounded-lg border-2 border-gray-200">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-bold text-lg text-gray-900">Local <span x-text="index + 1"></span></h4>
                    <button type="button" @click="removerLocal(index)" x-show="locais.length > 1"
                            class="text-red-600 hover:text-red-800 font-semibold">
                        ✕ Remover
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">CEP</label>
                        <input type="text" :name="'locais_trabalho[' + index + '][cep]'" 
                               x-model="local.cep"
                               x-mask="99999-999"
                               placeholder="00000-000"
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nome do Local</label>
                        <input type="text" :name="'locais_trabalho[' + index + '][nome]'" 
                               x-model="local.nome"
                               style="text-transform: uppercase;"
                               placeholder="Ex: HOSPITAL MUNICIPAL"
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Município</label>
                        <input type="text" :name="'locais_trabalho[' + index + '][municipio]'" 
                               x-model="local.municipio"
                               style="text-transform: uppercase;"
                               placeholder="Digite o município"
                               class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
            </div>
        </template>

        <button type="button" @click="adicionarLocal()" 
                class="w-full py-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-600 font-semibold transition-colors">
            + Adicionar Outro Local
        </button>
    </div>
</div>

<script>
function cepLookup() {
    return {
        cep: '{{ old("cep") }}',
        endereco: '{{ old("endereco") ?? old("endereco_residencial") }}',
        municipioId: '{{ old("municipio_id") }}',
        
        async buscarCep() {
            const cepLimpo = this.cep.replace(/\D/g, '');
            if (cepLimpo.length !== 8) {
                alert('CEP inválido');
                return;
            }
            
            try {
                const response = await fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`);
                const data = await response.json();
                
                if (data.erro) {
                    alert('CEP não encontrado');
                    return;
                }
                
                this.endereco = `${data.logradouro}, ${data.bairro}`.toUpperCase();
                
                // Buscar município
                const municipios = @json($municipios);
                const municipio = municipios.find(m => 
                    m.nome.toLowerCase().includes(data.localidade.toLowerCase())
                );
                if (municipio) {
                    this.municipioId = municipio.id;
                }
            } catch (error) {
                alert('Erro ao buscar CEP');
            }
        }
    };
}

function locaisTrabalho() {
    return {
        locais: [
            { cep: '', nome: '', municipio: '' }
        ],
        
        adicionarLocal() {
            this.locais.push({ cep: '', nome: '', municipio: '' });
        },
        
        removerLocal(index) {
            this.locais.splice(index, 1);
        }
    };
}
</script>

