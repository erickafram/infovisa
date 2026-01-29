{{-- Modal Upload com Abas --}}
<div x-show="modalUpload" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
    <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm" @click="modalUpload = false"></div>
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden" @click.stop
             x-data="{ 
                 abaAtiva: 'obrigatorios',
                 arquivosObrigatorios: {},
                 documentosEnviados: {},
                 enviando: {},
                 enviandoTodos: false,
                 dragoverDiverso: false,
                 fileDiverso: null,
                 fileDiversoSize: '',
                 tipoDocDiverso: '',
                 enviandoDiverso: false,
                 handleFileObrigatorio(e, docId, docNome) {
                     const file = e.target.files[0];
                     if (file) {
                         this.arquivosObrigatorios[docId] = {
                             file: file,
                             nome: docNome,
                             nomeOriginal: file.name,
                             size: (file.size / 1024 / 1024).toFixed(2) + ' MB'
                         };
                     }
                 },
                 removeFileObrigatorio(docId) {
                     delete this.arquivosObrigatorios[docId];
                     this.arquivosObrigatorios = {...this.arquivosObrigatorios};
                     const input = document.getElementById('file_doc_' + docId);
                     if (input) input.value = '';
                 },
                 async enviarObrigatorioAjax(docId) {
                     if (!this.arquivosObrigatorios[docId] || this.enviando[docId]) return;
                     
                     this.enviando[docId] = true;
                     const formData = new FormData();
                     formData.append('arquivo', this.arquivosObrigatorios[docId].file);
                     formData.append('tipo_documento_obrigatorio_id', docId);
                     formData.append('observacoes', this.arquivosObrigatorios[docId].nome);
                     formData.append('_token', '{{ csrf_token() }}');
                     
                     try {
                         const response = await fetch('{{ route('company.processos.upload', $processo->id) }}', {
                             method: 'POST',
                             body: formData,
                             headers: {
                                 'X-Requested-With': 'XMLHttpRequest',
                                 'Accept': 'application/json'
                             }
                         });
                         
                         if (response.ok) {
                             this.documentosEnviados[docId] = true;
                             delete this.arquivosObrigatorios[docId];
                             this.arquivosObrigatorios = {...this.arquivosObrigatorios};
                             const input = document.getElementById('file_doc_' + docId);
                             if (input) input.value = '';
                         } else {
                             const data = await response.json();
                             alert(data.message || 'Erro ao enviar documento');
                         }
                     } catch (error) {
                         console.error('Erro:', error);
                         alert('Erro ao enviar documento. Tente novamente.');
                     } finally {
                         this.enviando[docId] = false;
                     }
                 },
                 async enviarTodosObrigatorios() {
                     const docIds = Object.keys(this.arquivosObrigatorios);
                     if (docIds.length === 0) {
                         alert('Selecione pelo menos um arquivo para enviar.');
                         return;
                     }
                     
                     this.enviandoTodos = true;
                     
                     for (const docId of docIds) {
                         await this.enviarObrigatorioAjax(docId);
                     }
                     
                     this.enviandoTodos = false;
                 },
                 get totalArquivosSelecionados() {
                     return Object.keys(this.arquivosObrigatorios).length;
                 },
                 handleFileDiverso(e) {
                     const file = e.target.files[0] || (e.dataTransfer && e.dataTransfer.files[0]);
                     if (file) {
                         this.fileDiverso = file;
                         this.fileDiversoSize = (file.size / 1024 / 1024).toFixed(2) + ' MB';
                         if (e.dataTransfer) {
                             this.$refs.fileDiversoInput.files = e.dataTransfer.files;
                         }
                     }
                     this.dragoverDiverso = false;
                 },
                 resetFileDiverso() {
                     this.fileDiverso = null;
                     this.fileDiversoSize = '';
                     this.$refs.fileDiversoInput.value = '';
                 },
                 async enviarDiversoAjax() {
                     if (!this.fileDiverso || !this.tipoDocDiverso || this.enviandoDiverso) return;
                     
                     this.enviandoDiverso = true;
                     const formData = new FormData();
                     formData.append('arquivo', this.fileDiverso);
                     formData.append('observacoes', this.tipoDocDiverso);
                     formData.append('_token', '{{ csrf_token() }}');
                     
                     try {
                         const response = await fetch('{{ route('company.processos.upload', $processo->id) }}', {
                             method: 'POST',
                             body: formData,
                             headers: {
                                 'X-Requested-With': 'XMLHttpRequest',
                                 'Accept': 'application/json'
                             }
                         });
                         
                         if (response.ok) {
                             this.resetFileDiverso();
                             this.tipoDocDiverso = '';
                             alert('Documento enviado com sucesso!');
                         } else {
                             const data = await response.json();
                             alert(data.message || 'Erro ao enviar documento');
                         }
                     } catch (error) {
                         console.error('Erro:', error);
                         alert('Erro ao enviar documento. Tente novamente.');
                     } finally {
                         this.enviandoDiverso = false;
                     }
                 }
             }">
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Enviar Documentos</h3>
                            <p class="text-xs text-gray-500">Anexe os documentos necessários ao processo</p>
                        </div>
                    </div>
                    <button type="button" @click="modalUpload = false" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            {{-- Abas --}}
            <div class="border-b border-gray-200 bg-gray-50">
                <nav class="flex px-6" aria-label="Tabs">
                    <button type="button" @click="abaAtiva = 'obrigatorios'"
                            :class="abaAtiva === 'obrigatorios' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="px-4 py-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Documentos Obrigatórios
                        @if(isset($documentosObrigatorios) && $documentosObrigatorios->count() > 0)
                        <span class="px-1.5 py-0.5 text-[10px] font-bold bg-blue-100 text-blue-700 rounded-full">{{ $documentosObrigatorios->count() }}</span>
                        @endif
                    </button>
                    <button type="button" @click="abaAtiva = 'diversos'"
                            :class="abaAtiva === 'diversos' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="px-4 py-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                        </svg>
                        Documentos Diversos
                    </button>
                </nav>
            </div>
            
            {{-- Conteúdo das Abas --}}
            <div class="overflow-y-auto" style="max-height: calc(90vh - 180px);">
                
                {{-- Aba: Documentos Obrigatórios --}}
                <div x-show="abaAtiva === 'obrigatorios'" class="p-6">
                    {{-- Aviso --}}
                    <div class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-200 rounded-xl mb-4">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="text-sm text-amber-800">
                            Selecione os arquivos e envie individualmente ou todos de uma vez. O nome do arquivo será definido automaticamente.
                        </p>
                    </div>

                    {{-- Botão Enviar Todos --}}
                    <template x-if="totalArquivosSelecionados > 0">
                        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-xl flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm font-medium text-blue-800">
                                    <span x-text="totalArquivosSelecionados"></span> arquivo(s) selecionado(s)
                                </span>
                            </div>
                            <button type="button" @click="enviarTodosObrigatorios()"
                                    :disabled="enviandoTodos"
                                    class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <template x-if="!enviandoTodos">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                </template>
                                <template x-if="enviandoTodos">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                                <span x-text="enviandoTodos ? 'Enviando...' : 'Enviar Todos'"></span>
                            </button>
                        </div>
                    </template>

                    @if(isset($documentosObrigatorios) && $documentosObrigatorios->count() > 0)
                    <div class="space-y-3">
                        @foreach($documentosObrigatorios as $doc)
                        @php
                            $statusEnvio = $doc['status_envio'] ?? null;
                            $jaEnviado = $doc['ja_enviado'] ?? false;
                            $isPendente = $statusEnvio === 'pendente';
                            $isAprovado = $statusEnvio === 'aprovado';
                            $isRejeitado = $statusEnvio === 'rejeitado';
                        @endphp
                        <div class="border border-gray-200 rounded-xl p-4 hover:border-blue-300 transition-colors"
                             :class="{
                                 'bg-green-50 border-green-200': {{ $isAprovado ? 'true' : 'false' }} || documentosEnviados[{{ $doc['id'] }}],
                                 'bg-amber-50 border-amber-200': {{ $isPendente ? 'true' : 'false' }} && !documentosEnviados[{{ $doc['id'] }}],
                                 'bg-red-50 border-red-200': {{ $isRejeitado ? 'true' : 'false' }} && !documentosEnviados[{{ $doc['id'] }}],
                                 'bg-white': !{{ $jaEnviado ? 'true' : 'false' }} && !{{ $isRejeitado ? 'true' : 'false' }} && !documentosEnviados[{{ $doc['id'] }}]
                             }">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0"
                                         :class="{
                                             'bg-green-100': {{ $isAprovado ? 'true' : 'false' }} || documentosEnviados[{{ $doc['id'] }}],
                                             'bg-amber-100': {{ $isPendente ? 'true' : 'false' }} && !documentosEnviados[{{ $doc['id'] }}],
                                             'bg-red-100': {{ $isRejeitado ? 'true' : 'false' }} && !documentosEnviados[{{ $doc['id'] }}],
                                             'bg-red-100': !{{ $jaEnviado ? 'true' : 'false' }} && !{{ $isRejeitado ? 'true' : 'false' }} && !documentosEnviados[{{ $doc['id'] }}] && {{ $doc['obrigatorio'] ? 'true' : 'false' }},
                                             'bg-gray-100': !{{ $jaEnviado ? 'true' : 'false' }} && !{{ $isRejeitado ? 'true' : 'false' }} && !documentosEnviados[{{ $doc['id'] }}] && !{{ $doc['obrigatorio'] ? 'true' : 'false' }}
                                         }">
                                        {{-- Ícone de aprovado --}}
                                        <template x-if="{{ $isAprovado ? 'true' : 'false' }} || documentosEnviados[{{ $doc['id'] }}]">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </template>
                                        {{-- Ícone de pendente --}}
                                        @if($isPendente)
                                        <template x-if="!documentosEnviados[{{ $doc['id'] }}]">
                                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </template>
                                        @elseif($isRejeitado)
                                        {{-- Ícone de rejeitado --}}
                                        <template x-if="!documentosEnviados[{{ $doc['id'] }}]">
                                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </template>
                                        @elseif(!$jaEnviado)
                                        {{-- Ícone padrão (não enviado) --}}
                                        <template x-if="!documentosEnviados[{{ $doc['id'] }}]">
                                            <svg class="w-5 h-5 {{ $doc['obrigatorio'] ? 'text-red-600' : 'text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                        </template>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="text-sm font-semibold text-gray-900">{{ $doc['nome'] }}</span>
                                            @if($doc['obrigatorio'])
                                            <span class="px-1.5 py-0.5 text-[10px] font-medium bg-red-100 text-red-700 rounded">Obrigatório</span>
                                            @else
                                            <span class="px-1.5 py-0.5 text-[10px] font-medium bg-gray-100 text-gray-600 rounded">Opcional</span>
                                            @endif
                                            {{-- Status badges --}}
                                            @if($isPendente)
                                            <template x-if="!documentosEnviados[{{ $doc['id'] }}]">
                                                <span class="px-1.5 py-0.5 text-[10px] font-medium bg-amber-100 text-amber-700 rounded">Aguardando Aprovação</span>
                                            </template>
                                            @elseif($isAprovado)
                                            <span class="px-1.5 py-0.5 text-[10px] font-medium bg-green-100 text-green-700 rounded">Aprovado</span>
                                            @elseif($isRejeitado)
                                            <template x-if="!documentosEnviados[{{ $doc['id'] }}]">
                                                <span class="px-1.5 py-0.5 text-[10px] font-medium bg-red-100 text-red-700 rounded">Rejeitado - Reenvie</span>
                                            </template>
                                            @endif
                                            <template x-if="documentosEnviados[{{ $doc['id'] }}]">
                                                <span class="px-1.5 py-0.5 text-[10px] font-medium bg-green-100 text-green-700 rounded">Enviado</span>
                                            </template>
                                        </div>
                                        @if($doc['descricao'])
                                        <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $doc['descricao'] }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    {{-- Aprovado ou enviado agora --}}
                                    <template x-if="{{ $isAprovado ? 'true' : 'false' }} || documentosEnviados[{{ $doc['id'] }}]">
                                        <span class="text-xs text-green-600 font-medium whitespace-nowrap">✓ OK</span>
                                    </template>
                                    {{-- Pendente de aprovação --}}
                                    @if($isPendente)
                                    <template x-if="!documentosEnviados[{{ $doc['id'] }}]">
                                        <span class="text-xs text-amber-600 font-medium whitespace-nowrap flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            Pendente
                                        </span>
                                    </template>
                                    @elseif($isRejeitado || !$jaEnviado)
                                    {{-- Rejeitado ou não enviado - pode enviar --}}
                                    <template x-if="!documentosEnviados[{{ $doc['id'] }}]">
                                        <div class="flex items-center gap-2">
                                            <input type="file" id="file_doc_{{ $doc['id'] }}"
                                                   class="hidden" accept=".pdf"
                                                   @change="handleFileObrigatorio($event, {{ $doc['id'] }}, '{{ $doc['nome'] }}')">
                                            
                                            {{-- Botão selecionar arquivo --}}
                                            <template x-if="!arquivosObrigatorios[{{ $doc['id'] }}]">
                                                <label for="file_doc_{{ $doc['id'] }}" 
                                                       class="px-4 py-2 {{ $isRejeitado ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700' }} text-white text-sm font-medium rounded-lg cursor-pointer transition-colors flex items-center gap-2 whitespace-nowrap">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                    </svg>
                                                    {{ $isRejeitado ? 'Reenviar' : 'Selecionar' }}
                                                </label>
                                            </template>
                                        </div>
                                    </template>
                                    @endif
                                </div>
                            </div>
                            
                            {{-- Arquivo selecionado --}}
                            <template x-if="arquivosObrigatorios[{{ $doc['id'] }}] && !documentosEnviados[{{ $doc['id'] }}]">
                                <div class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-sm font-medium text-gray-900">
                                                    Será salvo como: <span class="text-blue-600" x-text="arquivosObrigatorios[{{ $doc['id'] }}]?.nome + '.pdf'"></span>
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    Arquivo: <span x-text="arquivosObrigatorios[{{ $doc['id'] }}]?.nomeOriginal"></span> 
                                                    (<span x-text="arquivosObrigatorios[{{ $doc['id'] }}]?.size"></span>)
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <button type="button" @click="removeFileObrigatorio({{ $doc['id'] }})" 
                                                    :disabled="enviando[{{ $doc['id'] }}]"
                                                    class="p-1.5 text-red-500 hover:bg-red-100 rounded-lg transition-colors disabled:opacity-50" title="Remover">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                            <button type="button" @click="enviarObrigatorioAjax({{ $doc['id'] }})"
                                                    :disabled="enviando[{{ $doc['id'] }}]"
                                                    class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                                <template x-if="!enviando[{{ $doc['id'] }}]">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                                    </svg>
                                                </template>
                                                <template x-if="enviando[{{ $doc['id'] }}]">
                                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                </template>
                                                <span x-text="enviando[{{ $doc['id'] }}] ? 'Enviando...' : 'Enviar'"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-gray-500 text-sm">Nenhum documento obrigatório configurado para este tipo de processo.</p>
                        <p class="text-gray-400 text-xs mt-1">Use a aba "Documentos Diversos" para enviar outros arquivos.</p>
                    </div>
                    @endif
                </div>
                
                {{-- Aba: Documentos Diversos --}}
                <div x-show="abaAtiva === 'diversos'" class="p-6"
                     x-data="{
                         arquivosPessoaFisica: [],
                         maxArquivosPF: 6,
                         handleFilesPessoaFisica(e) {
                             const files = e.target.files || (e.dataTransfer && e.dataTransfer.files);
                             if (files) {
                                 for (let i = 0; i < files.length && this.arquivosPessoaFisica.length < this.maxArquivosPF; i++) {
                                     const file = files[i];
                                     // Verifica se já não foi adicionado
                                     const jaExiste = this.arquivosPessoaFisica.some(f => f.name === file.name && f.size === file.size);
                                     if (!jaExiste) {
                                         this.arquivosPessoaFisica.push({
                                             file: file,
                                             name: file.name,
                                             size: (file.size / 1024 / 1024).toFixed(2) + ' MB'
                                         });
                                     }
                                 }
                             }
                             // Limpa o input para permitir selecionar o mesmo arquivo novamente
                             if (e.target && e.target.value) e.target.value = '';
                             this.dragoverDiverso = false;
                         },
                         removeFilePF(index) {
                             this.arquivosPessoaFisica.splice(index, 1);
                         },
                         async enviarTodosPF() {
                             if (this.arquivosPessoaFisica.length === 0 || this.enviandoDiverso) return;
                             
                             this.enviandoDiverso = true;
                             let sucessos = 0;
                             let erros = 0;
                             
                             for (let i = 0; i < this.arquivosPessoaFisica.length; i++) {
                                 const arquivo = this.arquivosPessoaFisica[i];
                                 const formData = new FormData();
                                 formData.append('arquivo', arquivo.file);
                                 formData.append('observacoes', 'Outros Documentos');
                                 formData.append('_token', '{{ csrf_token() }}');
                                 
                                 try {
                                     const response = await fetch('{{ route('company.processos.upload', $processo->id) }}', {
                                         method: 'POST',
                                         body: formData,
                                         headers: {
                                             'X-Requested-With': 'XMLHttpRequest',
                                             'Accept': 'application/json'
                                         }
                                     });
                                     
                                     if (response.ok) {
                                         sucessos++;
                                     } else {
                                         erros++;
                                     }
                                 } catch (error) {
                                     console.error('Erro:', error);
                                     erros++;
                                 }
                             }
                             
                             this.enviandoDiverso = false;
                             this.arquivosPessoaFisica = [];
                             
                             if (erros === 0) {
                                 alert(`${sucessos} documento(s) enviado(s) com sucesso!`);
                             } else {
                                 alert(`${sucessos} documento(s) enviado(s) com sucesso. ${erros} erro(s).`);
                             }
                         }
                     }">
                        
                        {{-- Aviso --}}
                        <div class="flex items-start gap-3 p-3 bg-blue-50 border border-blue-200 rounded-xl mb-4">
                            <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-sm text-blue-800">
                                Use esta aba para enviar memorandos, ofícios, solicitações e outros documentos diversos.
                            </p>
                        </div>
                        
                        {{-- Tipo de Documento --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Documento *</label>
                            <select required x-model="tipoDocDiverso"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione o tipo...</option>
                                <option value="Memorando">Memorando</option>
                                <option value="Ofício">Ofício</option>
                                <option value="Outros Documentos">Outros Documentos</option>
                            </select>
                        </div>
                        
                        {{-- Aviso especial para Outros Documentos --}}
                        <template x-if="tipoDocDiverso === 'Outros Documentos'">
                            <div class="flex items-start gap-3 p-3 bg-green-50 border border-green-200 rounded-xl mb-4">
                                <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-green-800">Upload múltiplo habilitado!</p>
                                    <p class="text-xs text-green-700 mt-1">Você pode selecionar até 6 arquivos de uma vez para enviar.</p>
                                </div>
                            </div>
                        </template>
                        
                        {{-- Área de Upload Normal (1 arquivo) --}}
                        <template x-if="tipoDocDiverso !== 'Outros Documentos'">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Arquivo *</label>
                                <div class="relative"
                                     @dragover.prevent="dragoverDiverso = true"
                                     @dragleave.prevent="dragoverDiverso = false"
                                     @drop.prevent="handleFileDiverso($event)">
                                    <input type="file" x-ref="fileDiversoInput"
                                           @change="handleFileDiverso($event)"
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                           accept=".pdf">
                                    <div class="border-2 border-dashed rounded-xl p-6 text-center transition-all"
                                         :class="dragoverDiverso ? 'border-blue-500 bg-blue-50' : (fileDiverso ? 'border-green-400 bg-green-50' : 'border-gray-300 hover:border-blue-400 hover:bg-gray-50')">
                                        <template x-if="!fileDiverso">
                                            <div>
                                                <svg class="w-10 h-10 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                </svg>
                                                <p class="text-sm text-gray-600 mb-1">
                                                    <span class="text-blue-600 font-medium">Clique para selecionar</span> ou arraste o arquivo
                                                </p>
                                                <p class="text-xs text-gray-500">Apenas PDF (máx. 30MB)</p>
                                            </div>
                                        </template>
                                        <template x-if="fileDiverso">
                                            <div class="flex items-center justify-center gap-3">
                                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </div>
                                                <div class="text-left">
                                                    <p class="text-sm font-medium text-gray-900" x-text="fileDiverso.name"></p>
                                                    <p class="text-xs text-gray-500" x-text="fileDiversoSize"></p>
                                                </div>
                                                <button type="button" @click.stop="resetFileDiverso()" class="p-1 text-red-500 hover:bg-red-50 rounded">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>
                        
                        {{-- Área de Upload Múltiplo (Outros Documentos - até 6 arquivos) --}}
                        <template x-if="tipoDocDiverso === 'Outros Documentos'">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Arquivos * 
                                    <span class="text-gray-500 font-normal">(<span x-text="arquivosPessoaFisica.length"></span>/6 selecionados)</span>
                                </label>
                                
                                {{-- Área de drop --}}
                                <div class="relative mb-3"
                                     @dragover.prevent="dragoverDiverso = true"
                                     @dragleave.prevent="dragoverDiverso = false"
                                     @drop.prevent="handleFilesPessoaFisica($event)">
                                    <input type="file" 
                                           @change="handleFilesPessoaFisica($event)"
                                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                           accept=".pdf"
                                           multiple
                                           :disabled="arquivosPessoaFisica.length >= maxArquivosPF">
                                    <div class="border-2 border-dashed rounded-xl p-6 text-center transition-all"
                                         :class="dragoverDiverso ? 'border-green-500 bg-green-50' : (arquivosPessoaFisica.length >= maxArquivosPF ? 'border-gray-200 bg-gray-50' : 'border-gray-300 hover:border-green-400 hover:bg-gray-50')">
                                        <template x-if="arquivosPessoaFisica.length < maxArquivosPF">
                                            <div>
                                                <svg class="w-10 h-10 mx-auto text-green-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                                </svg>
                                                <p class="text-sm text-gray-600 mb-1">
                                                    <span class="text-green-600 font-medium">Clique para selecionar</span> ou arraste os arquivos
                                                </p>
                                                <p class="text-xs text-gray-500">Apenas PDF (máx. 30MB cada) - Até 6 arquivos</p>
                                            </div>
                                        </template>
                                        <template x-if="arquivosPessoaFisica.length >= maxArquivosPF">
                                            <div>
                                                <svg class="w-10 h-10 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <p class="text-sm text-gray-500">Limite de 6 arquivos atingido</p>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                
                                {{-- Lista de arquivos selecionados --}}
                                <template x-if="arquivosPessoaFisica.length > 0">
                                    <div class="space-y-2">
                                        <template x-for="(arquivo, index) in arquivosPessoaFisica" :key="index">
                                            <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                    </div>
                                                    <div class="min-w-0">
                                                        <p class="text-sm font-medium text-gray-900 truncate" x-text="arquivo.name"></p>
                                                        <p class="text-xs text-gray-500" x-text="arquivo.size"></p>
                                                    </div>
                                                </div>
                                                <button type="button" @click="removeFilePF(index)" 
                                                        class="p-1.5 text-red-500 hover:bg-red-100 rounded-lg transition-colors flex-shrink-0" title="Remover">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                        
                        {{-- Botão Enviar --}}
                        <div class="flex justify-end">
                            {{-- Botão para upload normal --}}
                            <template x-if="tipoDocDiverso !== 'Outros Documentos'">
                                <button type="button" @click="enviarDiversoAjax()"
                                        :disabled="!tipoDocDiverso || !fileDiverso || enviandoDiverso"
                                        class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <template x-if="!enviandoDiverso">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                    </template>
                                    <template x-if="enviandoDiverso">
                                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </template>
                                    <span x-text="enviandoDiverso ? 'Enviando...' : 'Enviar Documento'"></span>
                                </button>
                            </template>
                            
                            {{-- Botão para upload múltiplo (Outros Documentos) --}}
                            <template x-if="tipoDocDiverso === 'Outros Documentos'">
                                <button type="button" @click="enviarTodosPF()"
                                        :disabled="arquivosPessoaFisica.length === 0 || enviandoDiverso"
                                        class="px-5 py-2.5 text-sm font-medium text-white bg-green-600 rounded-xl hover:bg-green-700 transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                    <template x-if="!enviandoDiverso">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                    </template>
                                    <template x-if="enviandoDiverso">
                                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </template>
                                    <span x-text="enviandoDiverso ? 'Enviando...' : 'Enviar ' + arquivosPessoaFisica.length + ' Documento(s)'"></span>
                                </button>
                            </template>
                        </div>
                </div>
            </div>
            
            {{-- Footer --}}
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-2xl flex justify-end">
                <button type="button" @click="modalUpload = false" 
                        class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>
