# Sistema de Histórico de Eventos do Processo

## Visão Geral

Sistema de histórico imutável que registra todas as ações realizadas em um processo, independente da existência dos documentos. Os eventos são armazenados na tabela `processo_eventos` e nunca são deletados, garantindo rastreabilidade completa.

---

## 1. Migration

Execute a migration para criar a tabela:

```bash
php artisan migrate
```

A tabela `processo_eventos` contém:
- `processo_id` - ID do processo
- `usuario_interno_id` - ID do usuário que executou a ação
- `tipo_evento` - Tipo do evento (enum)
- `titulo` - Título do evento
- `descricao` - Descrição detalhada
- `dados_adicionais` - JSON com dados extras (ID do documento, nome, etc)
- `ip_address` - IP do usuário
- `user_agent` - User agent do navegador
- `created_at` / `updated_at` - Timestamps

---

## 2. Tipos de Eventos Disponíveis

```php
'processo_criado'              // Processo foi criado
'documento_anexado'            // Arquivo foi anexado ao processo
'documento_digital_criado'     // Documento digital foi criado
'documento_excluido'           // Arquivo foi excluído
'documento_digital_excluido'   // Documento digital foi excluído
'status_alterado'              // Status do processo mudou
'movimentacao'                 // Processo foi movimentado
'observacao_adicionada'        // Observação foi adicionada
```

---

## 3. Como Registrar Eventos

### 3.1. Criação do Processo

```php
use App\Models\ProcessoEvento;

// No controller após criar o processo
$processo = Processo::create([...]);

ProcessoEvento::registrarCriacaoProcesso($processo);
```

### 3.2. Upload de Documento

```php
// No controller após fazer upload
$documento = ProcessoDocumento::create([...]);

ProcessoEvento::registrarDocumentoAnexado($processo, $documento);
```

### 3.3. Criação de Documento Digital

```php
// No controller após criar documento digital
$documentoDigital = DocumentoDigital::create([...]);

ProcessoEvento::registrarDocumentoDigitalCriado($processo, $documentoDigital);
```

### 3.4. Exclusão de Documento

```php
// No controller ANTES de excluir o documento
$documento = ProcessoDocumento::find($id);

// Registrar evento
ProcessoEvento::registrarDocumentoExcluido($processo, $documento);

// Agora pode excluir
$documento->delete();
```

### 3.5. Exclusão de Documento Digital

```php
// No controller ANTES de excluir o documento digital
$documentoDigital = DocumentoDigital::find($id);

// Registrar evento
ProcessoEvento::registrarDocumentoDigitalExcluido($processo, $documentoDigital);

// Agora pode excluir
$documentoDigital->delete();
```

### 3.6. Alteração de Status

```php
$statusAntigo = $processo->status;
$processo->status = 'em_analise';
$processo->save();

ProcessoEvento::registrarAlteracaoStatus($processo, $statusAntigo, 'em_analise');
```

---

## 4. Exemplo Completo: Controller de Upload

```php
<?php

namespace App\Http\Controllers;

use App\Models\Processo;
use App\Models\ProcessoDocumento;
use App\Models\ProcessoEvento;
use Illuminate\Http\Request;

class ProcessoDocumentoController extends Controller
{
    /**
     * Upload de documento
     */
    public function store(Request $request, $processoId)
    {
        $request->validate([
            'arquivo' => 'required|file|max:10240',
        ]);

        $processo = Processo::findOrFail($processoId);
        
        // Fazer upload do arquivo
        $arquivo = $request->file('arquivo');
        $path = $arquivo->store('processos/' . $processo->id, 'public');
        
        // Criar registro do documento
        $documento = ProcessoDocumento::create([
            'processo_id' => $processo->id,
            'nome_arquivo' => $arquivo->getClientOriginalName(),
            'caminho_arquivo' => $path,
            'tipo_documento' => 'arquivo',
            'tamanho' => $arquivo->getSize(),
        ]);
        
        // ✅ REGISTRAR EVENTO NO HISTÓRICO
        ProcessoEvento::registrarDocumentoAnexado($processo, $documento);
        
        return redirect()->back()->with('success', 'Documento anexado com sucesso!');
    }
    
    /**
     * Exclusão de documento
     */
    public function destroy($processoId, $documentoId)
    {
        $processo = Processo::findOrFail($processoId);
        $documento = ProcessoDocumento::findOrFail($documentoId);
        
        // ✅ REGISTRAR EVENTO NO HISTÓRICO ANTES DE EXCLUIR
        ProcessoEvento::registrarDocumentoExcluido($processo, $documento);
        
        // Excluir arquivo físico
        if ($documento->caminho_arquivo) {
            Storage::disk('public')->delete($documento->caminho_arquivo);
        }
        
        // Excluir registro
        $documento->delete();
        
        return redirect()->back()->with('success', 'Documento excluído com sucesso!');
    }
}
```

---

## 5. Exemplo Completo: Controller de Documento Digital

```php
<?php

namespace App\Http\Controllers;

use App\Models\Processo;
use App\Models\DocumentoDigital;
use App\Models\ProcessoEvento;
use Illuminate\Http\Request;

class DocumentoDigitalController extends Controller
{
    /**
     * Criação de documento digital
     */
    public function store(Request $request)
    {
        $request->validate([
            'processo_id' => 'required|exists:processos,id',
            'tipo_documento_id' => 'required|exists:tipo_documentos,id',
        ]);

        $processo = Processo::findOrFail($request->processo_id);
        
        // Criar documento digital
        $documentoDigital = DocumentoDigital::create([
            'processo_id' => $processo->id,
            'tipo_documento_id' => $request->tipo_documento_id,
            'status' => 'rascunho',
            'conteudo' => $request->conteudo,
        ]);
        
        // ✅ REGISTRAR EVENTO NO HISTÓRICO
        ProcessoEvento::registrarDocumentoDigitalCriado($processo, $documentoDigital);
        
        return redirect()->route('admin.documentos.edit', $documentoDigital->id)
            ->with('success', 'Documento criado com sucesso!');
    }
    
    /**
     * Exclusão de documento digital
     */
    public function destroy($id)
    {
        $documentoDigital = DocumentoDigital::findOrFail($id);
        $processo = $documentoDigital->processo;
        
        // ✅ REGISTRAR EVENTO NO HISTÓRICO ANTES DE EXCLUIR
        ProcessoEvento::registrarDocumentoDigitalExcluido($processo, $documentoDigital);
        
        // Excluir documento
        $documentoDigital->delete();
        
        return redirect()->route('admin.processos.show', [$processo->estabelecimento_id, $processo->id])
            ->with('success', 'Documento excluído com sucesso!');
    }
}
```

---

## 6. Consultar Histórico

### 6.1. No Controller

```php
// Buscar todos os eventos do processo
$eventos = $processo->eventos()->with('usuario')->get();

// Buscar eventos de um tipo específico
$exclusoes = $processo->eventos()
    ->where('tipo_evento', 'documento_excluido')
    ->get();

// Buscar eventos de um período
$eventosHoje = $processo->eventos()
    ->whereDate('created_at', today())
    ->get();
```

### 6.2. No Blade (já implementado)

```blade
@php
    $eventos = $processo->eventos()->with('usuario')->get();
@endphp

@foreach($eventos as $evento)
    <div class="evento">
        <h4>{{ $evento->titulo }}</h4>
        <p>{{ $evento->descricao }}</p>
        <small>{{ $evento->usuario->nome ?? 'Sistema' }} - {{ $evento->created_at->format('d/m/Y H:i') }}</small>
    </div>
@endforeach
```

---

## 7. Dados Adicionais (JSON)

Os eventos armazenam dados extras em formato JSON:

```php
// Exemplo de dados_adicionais para documento excluído
[
    'documento_id' => 123,
    'nome_arquivo' => 'comprovante.pdf',
    'tipo_documento' => 'arquivo',
]

// Acessar no Blade
{{ $evento->dados_adicionais['nome_arquivo'] ?? 'N/A' }}
```

---

## 8. Vantagens do Sistema

✅ **Histórico Imutável** - Eventos nunca são deletados  
✅ **Rastreabilidade Completa** - Sabe-se quem fez o quê e quando  
✅ **Independente de Documentos** - Eventos existem mesmo após exclusão  
✅ **Metadados Ricos** - IP, user agent, dados adicionais  
✅ **Fácil Auditoria** - Consulta simples via relacionamento  
✅ **Escalável** - Novos tipos de eventos podem ser adicionados  

---

## 9. Checklist de Implementação

- [x] Migration criada (`processo_eventos`)
- [x] Model `ProcessoEvento` criado
- [x] Relacionamento adicionado ao model `Processo`
- [x] Métodos estáticos para registrar eventos
- [x] View do histórico atualizada
- [ ] Adicionar chamadas nos controllers de upload
- [ ] Adicionar chamadas nos controllers de exclusão
- [ ] Adicionar chamadas nos controllers de documento digital
- [ ] Testar criação de eventos
- [ ] Testar visualização do histórico

---

## 10. Próximos Passos

1. Execute a migration: `php artisan migrate`
2. Adicione as chamadas de registro de eventos nos controllers existentes
3. Teste o sistema criando, anexando e excluindo documentos
4. Verifique o histórico no modal de histórico do processo

**Importante:** Sempre registre o evento ANTES de excluir o documento, para garantir que os dados ainda estão disponíveis!
