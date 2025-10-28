{{-- 
    EXEMPLOS DE USO DO COMPONENTE BUTTON-ASSINAR
    
    Este arquivo contém exemplos de como usar o componente de botão
    Copie e cole os exemplos conforme necessário
--}}

{{-- ============================================
     BOTÃO BÁSICO - LINK
     ============================================ --}}

{{-- Botão primário (padrão) --}}
<x-button-assinar href="{{ route('admin.assinatura.assinar', $id) }}">
    Assinar
</x-button-assinar>

{{-- Botão secundário --}}
<x-button-assinar href="#" variant="secondary">
    Cancelar
</x-button-assinar>

{{-- Botão de sucesso --}}
<x-button-assinar href="#" variant="success">
    Aprovar
</x-button-assinar>

{{-- Botão de perigo --}}
<x-button-assinar href="#" variant="danger">
    Rejeitar
</x-button-assinar>


{{-- ============================================
     BOTÃO BÁSICO - BUTTON (FORM)
     ============================================ --}}

{{-- Botão submit --}}
<x-button-assinar type="submit">
    Salvar
</x-button-assinar>

{{-- Botão button --}}
<x-button-assinar type="button">
    Fechar
</x-button-assinar>


{{-- ============================================
     TAMANHOS
     ============================================ --}}

{{-- Pequeno --}}
<x-button-assinar size="sm">
    Assinar
</x-button-assinar>

{{-- Médio (padrão) --}}
<x-button-assinar size="md">
    Assinar
</x-button-assinar>

{{-- Grande --}}
<x-button-assinar size="lg">
    Assinar
</x-button-assinar>


{{-- ============================================
     ESTADOS
     ============================================ --}}

{{-- Botão desabilitado --}}
<x-button-assinar disabled>
    Assinar
</x-button-assinar>

{{-- Botão em loading --}}
<x-button-assinar loading>
    Processando...
</x-button-assinar>

{{-- Botão desabilitado com variante danger --}}
<x-button-assinar variant="danger" disabled>
    Excluir
</x-button-assinar>


{{-- ============================================
     COMBINAÇÕES PRÁTICAS
     ============================================ --}}

{{-- Botão de assinatura com loading dinâmico --}}
<x-button-assinar 
    href="{{ route('admin.assinatura.assinar', $documento->id) }}"
    :loading="$isProcessing ?? false">
    Assinar Documento
</x-button-assinar>

{{-- Botão submit de formulário com loading --}}
<form method="POST" action="{{ route('admin.assinatura.processar') }}">
    @csrf
    <x-button-assinar 
        type="submit" 
        variant="success"
        size="lg"
        x-data="{ loading: false }"
        @click="loading = true"
        :loading="false">
        Confirmar Assinatura
    </x-button-assinar>
</form>

{{-- Botão com atributos adicionais --}}
<x-button-assinar 
    href="#"
    variant="primary"
    class="w-full"
    id="btn-assinar"
    data-documento-id="123">
    Assinar
</x-button-assinar>


{{-- ============================================
     GRID DE EXEMPLOS VISUAIS
     ============================================ --}}

<div class="space-y-8 p-8 bg-gray-50">
    <div>
        <h3 class="text-lg font-semibold mb-4">Variantes de Cor</h3>
        <div class="flex flex-wrap gap-4">
            <x-button-assinar variant="primary">Primary</x-button-assinar>
            <x-button-assinar variant="secondary">Secondary</x-button-assinar>
            <x-button-assinar variant="success">Success</x-button-assinar>
            <x-button-assinar variant="danger">Danger</x-button-assinar>
        </div>
    </div>

    <div>
        <h3 class="text-lg font-semibold mb-4">Tamanhos</h3>
        <div class="flex flex-wrap items-center gap-4">
            <x-button-assinar size="sm">Pequeno</x-button-assinar>
            <x-button-assinar size="md">Médio</x-button-assinar>
            <x-button-assinar size="lg">Grande</x-button-assinar>
        </div>
    </div>

    <div>
        <h3 class="text-lg font-semibold mb-4">Estados</h3>
        <div class="flex flex-wrap gap-4">
            <x-button-assinar>Normal</x-button-assinar>
            <x-button-assinar disabled>Desabilitado</x-button-assinar>
            <x-button-assinar loading>Loading</x-button-assinar>
        </div>
    </div>

    <div>
        <h3 class="text-lg font-semibold mb-4">Estados com Variantes</h3>
        <div class="flex flex-wrap gap-4">
            <x-button-assinar variant="primary" disabled>Primary Disabled</x-button-assinar>
            <x-button-assinar variant="success" loading>Success Loading</x-button-assinar>
            <x-button-assinar variant="danger" disabled>Danger Disabled</x-button-assinar>
        </div>
    </div>

    <div>
        <h3 class="text-lg font-semibold mb-4">Responsivo (Full Width em Mobile)</h3>
        <div class="space-y-2">
            <x-button-assinar class="w-full sm:w-auto">
                Botão Responsivo
            </x-button-assinar>
        </div>
    </div>
</div>
