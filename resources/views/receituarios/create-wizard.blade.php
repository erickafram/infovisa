@extends('layouts.admin')

@section('title', 'Novo Receituário')
@section('page-title', 'Novo Receituário')

@section('content')
<div class="max-w-8xl mx-auto" x-data="wizardReceituario()">
    
    {{-- Cabeçalho com Título --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
            @if($tipo == 'medico')
                📋 Cadastro de Médico, Cirurgião Dentista e Médico Veterinário
            @elseif($tipo == 'instituicao')
                🏥 Cadastro de Instituição (Hospital, Clínica e Similares)
            @elseif($tipo == 'secretaria')
                🏛️ Cadastro de Secretaria de Saúde e Vigilância Sanitária
            @elseif($tipo == 'talidomida')
                💊 Cadastro de Prescritor de Talidomida
            @endif
        </h2>
    </div>

    {{-- Barra de Progresso com Steps --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            @if(in_array($tipo, ['medico', 'talidomida']))
                {{-- Steps para Médico/Talidomida --}}
                <template x-for="(step, index) in steps" :key="index">
                    <div class="flex-1 flex items-center" :class="index < steps.length - 1 ? 'mr-4' : ''">
                        <div class="flex flex-col items-center flex-1">
                            <div class="relative">
                                <div @click="goToStep(index)" 
                                     class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg cursor-pointer transition-all"
                                     :class="currentStep === index ? 'bg-blue-600 text-white ring-4 ring-blue-200' : 
                                             currentStep > index ? 'bg-green-500 text-white' : 
                                             'bg-gray-200 text-gray-600'">
                                    <span x-show="currentStep > index">✓</span>
                                    <span x-show="currentStep <= index" x-text="index + 1"></span>
                                </div>
                            </div>
                            <div class="mt-2 text-center">
                                <p class="text-sm font-semibold" 
                                   :class="currentStep === index ? 'text-blue-600' : 'text-gray-600'"
                                   x-text="step.title"></p>
                            </div>
                        </div>
                        <div x-show="index < steps.length - 1" 
                             class="flex-1 h-1 mx-2 rounded"
                             :class="currentStep > index ? 'bg-green-500' : 'bg-gray-200'"></div>
                    </div>
                </template>
            @else
                {{-- Steps para Instituição/Secretaria --}}
                <template x-for="(step, index) in steps" :key="index">
                    <div class="flex-1 flex items-center" :class="index < steps.length - 1 ? 'mr-4' : ''">
                        <div class="flex flex-col items-center flex-1">
                            <div class="relative">
                                <div @click="goToStep(index)" 
                                     class="w-12 h-12 rounded-full flex items-center justify-center font-bold text-lg cursor-pointer transition-all"
                                     :class="currentStep === index ? 'bg-blue-600 text-white ring-4 ring-blue-200' : 
                                             currentStep > index ? 'bg-green-500 text-white' : 
                                             'bg-gray-200 text-gray-600'">
                                    <span x-show="currentStep > index">✓</span>
                                    <span x-show="currentStep <= index" x-text="index + 1"></span>
                                </div>
                            </div>
                            <div class="mt-2 text-center">
                                <p class="text-sm font-semibold" 
                                   :class="currentStep === index ? 'text-blue-600' : 'text-gray-600'"
                                   x-text="step.title"></p>
                            </div>
                        </div>
                        <div x-show="index < steps.length - 1" 
                             class="flex-1 h-1 mx-2 rounded"
                             :class="currentStep > index ? 'bg-green-500' : 'bg-gray-200'"></div>
                    </div>
                </template>
            @endif
        </div>
    </div>

    {{-- Formulário --}}
    <form method="POST" action="{{ route('admin.receituarios.store') }}" id="receituarioForm">
        @csrf
        <input type="hidden" name="tipo" value="{{ $tipo }}">

        {{-- Conteúdo dos Steps --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 mb-6">
            
            @if(in_array($tipo, ['medico', 'talidomida']))
                @include('receituarios.wizard.medico-steps')
            @elseif($tipo == 'instituicao')
                @include('receituarios.wizard.instituicao-steps')
            @elseif($tipo == 'secretaria')
                @include('receituarios.wizard.secretaria-steps')
            @endif

        </div>

        {{-- Botões de Navegação --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center justify-between">
            <button type="button" 
                    @click="previousStep()" 
                    x-show="currentStep > 0"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-semibold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Voltar
            </button>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.receituarios.index') }}" 
                   class="inline-flex items-center gap-2 px-6 py-3 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancelar
                </a>

                <button type="button" 
                        @click="nextStep()" 
                        x-show="currentStep < steps.length - 1"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                    Próximo
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <button type="submit" 
                        x-show="currentStep === steps.length - 1"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Finalizar Cadastro
                </button>
            </div>
        </div>
    </form>

</div>

<script>
function wizardReceituario() {
    const tipo = '{{ $tipo }}';
    
    let stepsConfig = {};
    
    if (tipo === 'medico') {
        stepsConfig = {
            steps: [
                { title: 'Dados Pessoais', id: 'step-1' },
                { title: 'Endereço', id: 'step-2' },
                { title: 'Locais de Trabalho', id: 'step-3' }
            ]
        };
    } else if (tipo === 'talidomida') {
        stepsConfig = {
            steps: [
                { title: 'Dados Pessoais', id: 'step-1' },
                { title: 'Endereço', id: 'step-2' },
                { title: 'Locais de Trabalho', id: 'step-3' }
            ]
        };
    } else if (tipo === 'instituicao') {
        stepsConfig = {
            steps: [
                { title: 'Dados da Instituição', id: 'step-1' },
                { title: 'Endereço e Contato', id: 'step-2' },
                { title: 'Responsável Técnico', id: 'step-3' }
            ]
        };
    } else if (tipo === 'secretaria') {
        stepsConfig = {
            steps: [
                { title: 'Dados da Secretaria', id: 'step-1' },
                { title: 'Endereço e Contato', id: 'step-2' },
                { title: 'Responsável', id: 'step-3' }
            ]
        };
    }
    
    return {
        currentStep: 0,
        ...stepsConfig,
        
        nextStep() {
            if (this.currentStep < this.steps.length - 1) {
                this.currentStep++;
                this.scrollToTop();
            }
        },
        
        previousStep() {
            if (this.currentStep > 0) {
                this.currentStep--;
                this.scrollToTop();
            }
        },
        
        goToStep(index) {
            this.currentStep = index;
            this.scrollToTop();
        },
        
        scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    };
}
</script>
@endsection
