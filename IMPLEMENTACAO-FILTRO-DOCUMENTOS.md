# ✅ Implementação: Filtro de Documentos por Usuário e Status

## 📋 Funcionalidade Implementada

Sistema de filtragem de documentos digitais que mostra apenas os documentos relacionados ao usuário logado, separados por status:

- **Rascunhos**: Documentos em criação pelo usuário
- **Aguardando Minha Assinatura**: Documentos pendentes da assinatura do usuário
- **Assinados por Mim**: Documentos já assinados pelo usuário
- **Todos**: Todos os documentos relacionados ao usuário

---

## 🔧 Implementação no Controller

### **Arquivo:** `app/Http/Controllers/DocumentoDigitalController.php`

#### **Método `index()` Atualizado:**

```php
public function index(Request $request)
{
    $usuarioLogado = auth('interno')->user();
    $filtroStatus = $request->get('status', 'todos');
    
    // Query base: documentos relacionados ao usuário
    $query = DocumentoDigital::with(['tipoDocumento', 'usuarioCriador', 'processo', 'assinaturas.usuarioInterno'])
        ->where(function($q) use ($usuarioLogado) {
            // Documentos criados pelo usuário
            $q->where('usuario_criador_id', $usuarioLogado->id)
              // OU documentos onde o usuário é assinante
              ->orWhereHas('assinaturas', function($query) use ($usuarioLogado) {
                  $query->where('usuario_interno_id', $usuarioLogado->id);
              });
        });
    
    // Aplicar filtro de status
    if ($filtroStatus !== 'todos') {
        switch ($filtroStatus) {
            case 'rascunho':
                $query->where('status', 'rascunho')
                      ->where('usuario_criador_id', $usuarioLogado->id);
                break;
                
            case 'aguardando_minha_assinatura':
                $query->where('status', 'aguardando_assinatura')
                      ->whereHas('assinaturas', function($q) use ($usuarioLogado) {
                          $q->where('usuario_interno_id', $usuarioLogado->id)
                            ->where('status', 'pendente');
                      });
                break;
                
            case 'assinados_por_mim':
                $query->whereHas('assinaturas', function($q) use ($usuarioLogado) {
                    $q->where('usuario_interno_id', $usuarioLogado->id)
                      ->where('status', 'assinado');
                });
                break;
        }
    }
    
    $documentos = $query->orderBy('created_at', 'desc')->paginate(20);
    
    // Estatísticas para badges
    $stats = [
        'rascunhos' => DocumentoDigital::where('usuario_criador_id', $usuarioLogado->id)
            ->where('status', 'rascunho')
            ->count(),
        'aguardando_minha_assinatura' => DocumentoDigital::where('status', 'aguardando_assinatura')
            ->whereHas('assinaturas', function($q) use ($usuarioLogado) {
                $q->where('usuario_interno_id', $usuarioLogado->id)
                  ->where('status', 'pendente');
            })
            ->count(),
        'assinados_por_mim' => DocumentoDigital::whereHas('assinaturas', function($q) use ($usuarioLogado) {
            $q->where('usuario_interno_id', $usuarioLogado->id)
              ->where('status', 'assinado');
        })
        ->count(),
    ];

    return view('documentos.index', compact('documentos', 'filtroStatus', 'stats'));
}
```

---

## 🎨 Implementação na View

### **Arquivo:** `resources/views/documentos/index.blade.php`

#### **1. Filtros com Badges (Linha 13-71)**

```blade
<!-- Filtros com Badges -->
<div class="mb-6 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
    <div class="flex flex-wrap gap-3">
        <!-- Todos -->
        <a href="{{ route('admin.documentos.index', ['status' => 'todos']) }}" 
           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                  {{ $filtroStatus === 'todos' ? 'bg-blue-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Todos
        </a>

        <!-- Rascunhos -->
        <a href="{{ route('admin.documentos.index', ['status' => 'rascunho']) }}" 
           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                  {{ $filtroStatus === 'rascunho' ? 'bg-gray-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Rascunhos
            @if($stats['rascunhos'] > 0)
                <span class="ml-2 px-2 py-0.5 text-xs font-bold rounded-full {{ $filtroStatus === 'rascunho' ? 'bg-white text-gray-600' : 'bg-gray-600 text-white' }}">
                    {{ $stats['rascunhos'] }}
                </span>
            @endif
        </a>

        <!-- Aguardando Minha Assinatura -->
        <a href="{{ route('admin.documentos.index', ['status' => 'aguardando_minha_assinatura']) }}" 
           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                  {{ $filtroStatus === 'aguardando_minha_assinatura' ? 'bg-yellow-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Aguardando Minha Assinatura
            @if($stats['aguardando_minha_assinatura'] > 0)
                <span class="ml-2 px-2 py-0.5 text-xs font-bold rounded-full {{ $filtroStatus === 'aguardando_minha_assinatura' ? 'bg-white text-yellow-600' : 'bg-yellow-600 text-white' }}">
                    {{ $stats['aguardando_minha_assinatura'] }}
                </span>
            @endif
        </a>

        <!-- Assinados por Mim -->
        <a href="{{ route('admin.documentos.index', ['status' => 'assinados_por_mim']) }}" 
           class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200
                  {{ $filtroStatus === 'assinados_por_mim' ? 'bg-green-600 text-white shadow-md' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Assinados por Mim
            @if($stats['assinados_por_mim'] > 0)
                <span class="ml-2 px-2 py-0.5 text-xs font-bold rounded-full {{ $filtroStatus === 'assinados_por_mim' ? 'bg-white text-green-600' : 'bg-green-600 text-white' }}">
                    {{ $stats['assinados_por_mim'] }}
                </span>
            @endif
        </a>
    </div>
</div>
```

#### **2. Indicador de Status do Usuário (Linha 126-150)**

```blade
<td class="px-6 py-4 text-sm text-gray-500">
    <div>{{ $documento->usuarioCriador->nome }}</div>
    @php
        $minhaAssinatura = $documento->assinaturas->where('usuario_interno_id', auth('interno')->id())->first();
    @endphp
    @if($minhaAssinatura)
        <div class="mt-1 text-xs">
            @if($minhaAssinatura->status === 'assinado')
                <span class="inline-flex items-center text-green-600">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Você assinou
                </span>
            @elseif($minhaAssinatura->status === 'pendente')
                <span class="inline-flex items-center text-yellow-600">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    Aguardando sua assinatura
                </span>
            @endif
        </div>
    @endif
</td>
```

#### **3. Botões de Ação Contextuais (Linha 154-189)**

```blade
<td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
    <div class="flex items-center justify-end gap-2">
        @php
            $minhaAssinatura = $documento->assinaturas->where('usuario_interno_id', auth('interno')->id())->first();
        @endphp
        
        <!-- Botão Assinar (se pendente) -->
        @if($minhaAssinatura && $minhaAssinatura->status === 'pendente' && $documento->status === 'aguardando_assinatura')
            <a href="{{ route('admin.assinatura.assinar', $documento->id) }}" 
               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-yellow-600 rounded-md hover:bg-yellow-700 transition">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                Assinar
            </a>
        @endif
        
        <!-- Botão Editar (se rascunho e criado pelo usuário) -->
        @if($documento->status === 'rascunho' && $documento->usuario_criador_id === auth('interno')->id())
            <a href="{{ route('admin.documentos.edit', $documento->id) }}" 
               class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700 transition">
                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Editar
            </a>
        @endif
        
        <!-- Botão Ver (sempre visível) -->
        <a href="{{ route('admin.documentos.show', $documento->id) }}" 
           class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Ver
        </a>
    </div>
</td>
```

---

## 🔍 Queries Eloquent Explicadas

### **1. Documentos Relacionados ao Usuário**

```php
DocumentoDigital::where(function($q) use ($usuarioLogado) {
    // Documentos criados pelo usuário
    $q->where('usuario_criador_id', $usuarioLogado->id)
      // OU documentos onde o usuário é assinante
      ->orWhereHas('assinaturas', function($query) use ($usuarioLogado) {
          $query->where('usuario_interno_id', $usuarioLogado->id);
      });
})
```

**Retorna:** Todos os documentos que o usuário criou OU onde ele é assinante.

### **2. Filtro: Rascunhos**

```php
$query->where('status', 'rascunho')
      ->where('usuario_criador_id', $usuarioLogado->id);
```

**Retorna:** Apenas documentos em rascunho criados pelo usuário.

### **3. Filtro: Aguardando Minha Assinatura**

```php
$query->where('status', 'aguardando_assinatura')
      ->whereHas('assinaturas', function($q) use ($usuarioLogado) {
          $q->where('usuario_interno_id', $usuarioLogado->id)
            ->where('status', 'pendente');
      });
```

**Retorna:** Documentos aguardando assinatura onde o usuário tem assinatura pendente.

### **4. Filtro: Assinados por Mim**

```php
$query->whereHas('assinaturas', function($q) use ($usuarioLogado) {
    $q->where('usuario_interno_id', $usuarioLogado->id)
      ->where('status', 'assinado');
});
```

**Retorna:** Documentos onde o usuário já assinou.

---

## 📊 Estatísticas (Badges)

```php
$stats = [
    'rascunhos' => DocumentoDigital::where('usuario_criador_id', $usuarioLogado->id)
        ->where('status', 'rascunho')
        ->count(),
    'aguardando_minha_assinatura' => DocumentoDigital::where('status', 'aguardando_assinatura')
        ->whereHas('assinaturas', function($q) use ($usuarioLogado) {
            $q->where('usuario_interno_id', $usuarioLogado->id)
              ->where('status', 'pendente');
        })
        ->count(),
    'assinados_por_mim' => DocumentoDigital::whereHas('assinaturas', function($q) use ($usuarioLogado) {
        $q->where('usuario_interno_id', $usuarioLogado->id)
          ->where('status', 'assinado');
    })
    ->count(),
];
```

---

## 🎯 Recursos Implementados

### ✅ **Filtros**
- Todos os documentos
- Rascunhos
- Aguardando minha assinatura
- Assinados por mim

### ✅ **Badges de Contagem**
- Exibe número de documentos em cada categoria
- Atualiza dinamicamente
- Design responsivo

### ✅ **Indicadores Visuais**
- Ícone verde: "Você assinou"
- Ícone amarelo: "Aguardando sua assinatura"
- Status coloridos (rascunho, aguardando, assinado)

### ✅ **Botões Contextuais**
- **Assinar**: Aparece se documento aguarda assinatura do usuário
- **Editar**: Aparece se documento é rascunho criado pelo usuário
- **Ver**: Sempre visível

### ✅ **Responsividade**
- Filtros adaptam para mobile
- Tabela com scroll horizontal
- Badges responsivos

---

## 🚀 Como Usar

### **1. Acessar a Listagem**
```
http://localhost:8001/admin/documentos
```

### **2. Filtrar por Status**
```
http://localhost:8001/admin/documentos?status=rascunho
http://localhost:8001/admin/documentos?status=aguardando_minha_assinatura
http://localhost:8001/admin/documentos?status=assinados_por_mim
```

### **3. Ver Todos**
```
http://localhost:8001/admin/documentos?status=todos
```

---

## 📝 Estrutura do Banco de Dados

### **Tabela: documentos_digitais**
- `id`
- `usuario_criador_id` (FK para usuarios_internos)
- `status` ('rascunho', 'aguardando_assinatura', 'assinado')
- `tipo_documento_id`
- `processo_id`
- `numero_documento`
- `nome`
- `conteudo`
- `sigiloso`
- `created_at`
- `updated_at`

### **Tabela: documento_assinaturas**
- `id`
- `documento_digital_id` (FK para documentos_digitais)
- `usuario_interno_id` (FK para usuarios_internos)
- `ordem`
- `status` ('pendente', 'assinado')
- `obrigatoria`
- `assinado_em`
- `created_at`
- `updated_at`

---

## 🎨 Paleta de Cores

| Status | Cor | Classe TailwindCSS |
|--------|-----|-------------------|
| Todos | Azul | `bg-blue-600` |
| Rascunho | Cinza | `bg-gray-600` |
| Aguardando | Amarelo | `bg-yellow-600` |
| Assinado | Verde | `bg-green-600` |

---

## ✅ Status da Implementação

- ✅ Controller atualizado com filtros
- ✅ Queries Eloquent otimizadas
- ✅ View com filtros e badges
- ✅ Indicadores visuais de status
- ✅ Botões contextuais
- ✅ Estatísticas em tempo real
- ✅ Design responsivo com TailwindCSS
- ✅ Paginação mantida

---

## 🔗 Rotas Relacionadas

```php
// routes/web.php
Route::get('/documentos', [DocumentoDigitalController::class, 'index'])
    ->name('documentos.index');
Route::get('/documentos/{id}', [DocumentoDigitalController::class, 'show'])
    ->name('documentos.show');
Route::get('/documentos/{id}/edit', [DocumentoDigitalController::class, 'edit'])
    ->name('documentos.edit');
Route::get('/assinatura/assinar/{documentoId}', [AssinaturaDigitalController::class, 'assinar'])
    ->name('assinatura.assinar');
```
