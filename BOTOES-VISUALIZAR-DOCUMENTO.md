# 📄 Botões na Página "Visualizar Documento"

## 🎯 Implementação Completa

Dois botões foram adicionados na página de visualização de documento (`/admin/documentos/{id}`):

1. **Botão "Voltar"** - Redireciona para a listagem de documentos
2. **Botão "Ver Processo"** - Redireciona para o processo vinculado (se existir)

---

## 💻 Código dos Botões

### **Arquivo:** `resources/views/documentos/show.blade.php`

```blade
{{-- Botões de Ação --}}
<div class="mt-6 flex flex-wrap gap-3">
    {{-- Botão Voltar --}}
    <a href="{{ route('admin.documentos.index') }}" 
       class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
        Voltar
    </a>

    {{-- Botão Ver Processo (se houver processo vinculado) --}}
    @if($documento->processo)
        <a href="{{ route('admin.estabelecimentos.processos.show', [$documento->processo->estabelecimento_id, $documento->processo->id]) }}" 
           class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
            Ver Processo
        </a>
    @endif

    {{-- Botão Editar Rascunho (se for rascunho) --}}
    @if($documento->status === 'rascunho' && $documento->usuario_criador_id === auth('interno')->id())
        <a href="{{ route('admin.documentos.edit', $documento->id) }}" 
           class="px-5 py-2.5 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
            Editar Rascunho
        </a>
    @endif
</div>
```

---

## 🔍 Detalhamento dos Botões

### **1. Botão "Voltar"**

**Sempre visível** - Redireciona para a listagem de documentos.

```blade
<a href="{{ route('admin.documentos.index') }}" 
   class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
    Voltar
</a>
```

**Características:**
- ✅ Rota nomeada: `admin.documentos.index`
- ✅ Estilo: Botão secundário (branco com borda cinza)
- ✅ Hover: Fundo cinza claro e borda mais escura
- ✅ Focus ring: Anel cinza para acessibilidade
- ✅ Transição suave: 200ms

**Classes TailwindCSS:**
- `px-5 py-2.5` - Padding horizontal e vertical
- `text-sm font-semibold` - Texto pequeno e negrito
- `text-gray-700` - Cor do texto cinza escuro
- `bg-white` - Fundo branco
- `border border-gray-300` - Borda cinza clara
- `rounded-lg` - Cantos arredondados
- `hover:bg-gray-50` - Fundo cinza claro no hover
- `hover:border-gray-400` - Borda mais escura no hover
- `focus:outline-none` - Remove outline padrão
- `focus:ring-2 focus:ring-gray-500 focus:ring-offset-2` - Anel de foco
- `transition-all duration-200` - Transição suave

---

### **2. Botão "Ver Processo"**

**Condicional** - Aparece apenas se o documento tiver processo vinculado.

```blade
@if($documento->processo)
    <a href="{{ route('admin.estabelecimentos.processos.show', [$documento->processo->estabelecimento_id, $documento->processo->id]) }}" 
       class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
        Ver Processo
    </a>
@endif
```

**Características:**
- ✅ Rota nomeada: `admin.estabelecimentos.processos.show`
- ✅ Parâmetros: `estabelecimento_id` e `processo_id`
- ✅ Estilo: Botão primário (azul)
- ✅ Hover: Azul mais escuro e sombra maior
- ✅ Focus ring: Anel azul para acessibilidade
- ✅ Sombra: Pequena sombra que aumenta no hover
- ✅ Condicional: Só aparece se `$documento->processo` existir

**Classes TailwindCSS:**
- `px-5 py-2.5` - Padding horizontal e vertical
- `text-sm font-semibold` - Texto pequeno e negrito
- `text-white` - Texto branco
- `bg-blue-600` - Fundo azul
- `rounded-lg` - Cantos arredondados
- `hover:bg-blue-700` - Azul mais escuro no hover
- `focus:outline-none` - Remove outline padrão
- `focus:ring-2 focus:ring-blue-500 focus:ring-offset-2` - Anel de foco azul
- `transition-all duration-200` - Transição suave
- `shadow-sm` - Sombra pequena
- `hover:shadow-md` - Sombra média no hover

---

## 🎨 Estilos TailwindCSS Explicados

### **Padding e Tamanho**
```css
px-5      /* padding-left: 1.25rem; padding-right: 1.25rem; */
py-2.5    /* padding-top: 0.625rem; padding-bottom: 0.625rem; */
text-sm   /* font-size: 0.875rem; line-height: 1.25rem; */
```

### **Cores - Botão Voltar (Secundário)**
```css
text-gray-700       /* Texto cinza escuro */
bg-white            /* Fundo branco */
border-gray-300     /* Borda cinza clara */
hover:bg-gray-50    /* Fundo cinza claro no hover */
hover:border-gray-400 /* Borda cinza média no hover */
```

### **Cores - Botão Ver Processo (Primário)**
```css
text-white          /* Texto branco */
bg-blue-600         /* Fundo azul */
hover:bg-blue-700   /* Azul mais escuro no hover */
```

### **Bordas e Cantos**
```css
border              /* border-width: 1px; */
rounded-lg          /* border-radius: 0.5rem; */
```

### **Foco (Acessibilidade)**
```css
focus:outline-none          /* Remove outline padrão */
focus:ring-2                /* Anel de 2px */
focus:ring-gray-500         /* Cor do anel (cinza para Voltar) */
focus:ring-blue-500         /* Cor do anel (azul para Ver Processo) */
focus:ring-offset-2         /* Espaço entre elemento e anel */
```

### **Sombras**
```css
shadow-sm           /* box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); */
hover:shadow-md     /* box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); */
```

### **Transições**
```css
transition-all      /* Transição em todas as propriedades */
duration-200        /* Duração de 200ms */
```

---

## 🔗 Rotas Utilizadas

### **1. Rota para Listagem de Documentos**
```php
// routes/web.php
Route::get('/documentos', [DocumentoDigitalController::class, 'index'])
    ->name('admin.documentos.index');
```

**Blade:**
```blade
{{ route('admin.documentos.index') }}
```

**URL Gerada:**
```
http://localhost:8001/admin/documentos
```

---

### **2. Rota para Visualizar Processo**
```php
// routes/web.php
Route::get('/estabelecimentos/{id}/processos/{processo}', [ProcessoController::class, 'show'])
    ->name('admin.estabelecimentos.processos.show');
```

**Blade:**
```blade
{{ route('admin.estabelecimentos.processos.show', [$documento->processo->estabelecimento_id, $documento->processo->id]) }}
```

**URL Gerada (exemplo):**
```
http://localhost:8001/admin/estabelecimentos/5/processos/12
```

---

## 📊 Estrutura do Container

```blade
<div class="mt-6 flex flex-wrap gap-3">
    <!-- Botões aqui -->
</div>
```

**Classes do Container:**
- `mt-6` - Margem superior de 1.5rem
- `flex` - Display flex
- `flex-wrap` - Permite quebra de linha em telas pequenas
- `gap-3` - Espaçamento de 0.75rem entre botões

---

## 🎯 Lógica Condicional

### **Botão "Ver Processo"**
```blade
@if($documento->processo)
    <!-- Botão Ver Processo -->
@endif
```

**Condição:** Só exibe se o documento tiver um processo vinculado.

### **Botão "Editar Rascunho"**
```blade
@if($documento->status === 'rascunho' && $documento->usuario_criador_id === auth('interno')->id())
    <!-- Botão Editar Rascunho -->
@endif
```

**Condições:**
1. Documento deve estar em status `rascunho`
2. Usuário logado deve ser o criador do documento

---

## 📱 Responsividade

Os botões são responsivos graças ao `flex-wrap`:

### **Desktop (>768px)**
```
[Voltar] [Ver Processo] [Editar Rascunho]
```

### **Mobile (<768px)**
```
[Voltar]
[Ver Processo]
[Editar Rascunho]
```

---

## 🎨 Variações de Cor

Se você quiser criar botões com outras cores, use estas classes:

### **Botão Verde (Sucesso)**
```blade
<a href="#" 
   class="px-5 py-2.5 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
    Aprovar
</a>
```

### **Botão Vermelho (Perigo)**
```blade
<a href="#" 
   class="px-5 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
    Excluir
</a>
```

### **Botão Amarelo (Aviso)**
```blade
<a href="#" 
   class="px-5 py-2.5 text-sm font-semibold text-white bg-yellow-600 rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
    Atenção
</a>
```

### **Botão Cinza (Neutro)**
```blade
<a href="#" 
   class="px-5 py-2.5 text-sm font-semibold text-white bg-gray-600 rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 shadow-sm hover:shadow-md">
    Cancelar
</a>
```

---

## ✅ Checklist de Implementação

- [x] Botão "Voltar" sempre visível
- [x] Botão "Ver Processo" condicional ao processo vinculado
- [x] Rotas nomeadas utilizadas
- [x] TailwindCSS para estilização
- [x] Sem ícones (apenas texto)
- [x] Design simples e limpo
- [x] Hover suave com transição
- [x] Focus ring para acessibilidade
- [x] Responsivo (flex-wrap)
- [x] Cores contextuais (cinza/branco para Voltar, azul para Ver Processo)

---

## 🚀 Como Testar

1. **Acesse um documento:**
   ```
   http://localhost:8001/admin/documentos/28
   ```

2. **Verifique os botões:**
   - ✅ Botão "Voltar" deve estar sempre visível
   - ✅ Botão "Ver Processo" deve aparecer se houver processo vinculado
   - ✅ Clique em "Voltar" deve redirecionar para `/admin/documentos`
   - ✅ Clique em "Ver Processo" deve redirecionar para o processo

3. **Teste responsividade:**
   - Redimensione a janela do navegador
   - Botões devem empilhar em telas pequenas

4. **Teste acessibilidade:**
   - Use Tab para navegar entre botões
   - Focus ring deve aparecer ao focar com teclado

---

## 📚 Referências

- [TailwindCSS - Buttons](https://tailwindcss.com/docs/button)
- [Laravel - Named Routes](https://laravel.com/docs/11.x/routing#named-routes)
- [Blade - Conditional Statements](https://laravel.com/docs/11.x/blade#if-statements)
