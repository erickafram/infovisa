# 📝 Visualizador de PDF com Anotações

## 🎯 Visão Geral

Sistema completo de visualização e anotação de PDFs anexados aos processos, especialmente útil para análise de projetos arquitetônicos e documentos técnicos.

## ✨ Funcionalidades Implementadas

### 1. **Visualizador Avançado de PDF**
- Navegação entre páginas
- Controles de zoom (aumentar, diminuir, ajustar à largura)
- Renderização de alta qualidade usando PDF.js

### 2. **Ferramentas de Anotação**

#### 🎨 Tipos de Anotações Disponíveis:

1. **Selecionar** (Cursor padrão)
   - Navegar pelo documento sem fazer anotações

2. **Destacar** (Highlight)
   - Marcar áreas importantes com destaque amarelo
   - Útil para chamar atenção para seções específicas

3. **Texto** (Text)
   - Adicionar anotações de texto
   - Ideal para observações e comentários

4. **Desenhar** (Drawing)
   - Desenho livre sobre o PDF
   - Perfeito para marcar detalhes em projetos arquitetônicos

5. **Área/Retângulo** (Area)
   - Selecionar áreas retangulares
   - Útil para delimitar zonas específicas do projeto

6. **Comentário** (Comment)
   - Adicionar comentários pontuais
   - Cada comentário pode ter texto explicativo

### 3. **Gerenciamento de Anotações**
- ✅ Salvar anotações no banco de dados
- ✅ Carregar anotações existentes
- ✅ Visualizar anotações por página
- ✅ Excluir anotações individuais
- ✅ Exportar PDF com anotações (em desenvolvimento)
- ✅ Anotações são associadas ao usuário que as criou

## 🗂️ Arquivos Modificados/Criados

### **1. Migration**
- **Arquivo**: `database/migrations/2025_10_29_023548_create_processo_pdf_anotacoes_table.php`
- **Descrição**: Cria tabela para armazenar anotações de PDFs
- **Campos**:
  - `processo_documento_id`: Referência ao documento
  - `usuario_interno_id`: Usuário que criou a anotação
  - `pagina`: Número da página
  - `tipo`: Tipo de anotação (highlight, text, drawing, area, comment)
  - `dados`: JSON com coordenadas e propriedades
  - `comentario`: Texto do comentário (opcional)

### **2. Modelo**
- **Arquivo**: `app/Models/ProcessoPdfAnotacao.php`
- **Descrição**: Modelo Eloquent para anotações
- **Relacionamentos**:
  - `documento()`: Pertence a ProcessoDocumento
  - `usuario()`: Pertence a UsuarioInterno

### **3. Controller**
- **Arquivo**: `app/Http/Controllers/ProcessoController.php`
- **Métodos Adicionados**:
  - `salvarAnotacoes($request, $documentoId)`: Salva anotações do usuário
  - `carregarAnotacoes($documentoId)`: Carrega anotações existentes

### **4. Rotas**
- **Arquivo**: `routes/web.php`
- **Rotas Adicionadas**:
  ```php
  POST   /processos/documentos/{documento}/anotacoes  // Salvar anotações
  GET    /processos/documentos/{documento}/anotacoes  // Carregar anotações
  ```

### **5. Componente Blade**
- **Arquivo**: `resources/views/components/pdf-viewer-anotacoes.blade.php`
- **Descrição**: Componente reutilizável do visualizador
- **Tecnologias**:
  - PDF.js 3.11.174 (renderização de PDF) - carregado globalmente no layout
  - Alpine.js (reatividade)
  - TailwindCSS (estilização)
  - Canvas API (desenho de anotações)

### **5.1. Layout Principal**
- **Arquivo**: `resources/views/layouts/admin.blade.php`
- **Modificação**: Adicionado PDF.js globalmente (linhas 209-216)
- **Motivo**: Garantir que PDF.js esteja disponível antes do Alpine.js inicializar

### **6. View do Processo**
- **Arquivo**: `resources/views/estabelecimentos/processos/show.blade.php`
- **Modificações**:
  - Adicionado modal do visualizador com anotações
  - Modificado clique em arquivos externos para abrir visualizador
  - Adicionadas variáveis Alpine.js para controle do modal
  - Adicionada função `abrirVisualizadorAnotacoes()`

## 🚀 Como Usar

### **Para Usuários do Sistema:**

1. **Acessar um Processo**
   - Navegue até: `admin/estabelecimentos/{id}/processos/{processoId}`

2. **Visualizar PDF com Anotações**
   - Na lista de documentos, clique em qualquer **Arquivo Externo** (PDF)
   - O visualizador com ferramentas de anotação será aberto automaticamente

3. **Fazer Anotações**
   - Selecione uma ferramenta na barra de ferramentas
   - Clique e arraste no PDF para criar a anotação
   - Adicione um comentário quando solicitado (opcional)

4. **Navegar pelo PDF**
   - Use os botões de navegação para mudar de página
   - Ajuste o zoom conforme necessário
   - As anotações são exibidas apenas na página onde foram criadas

5. **Salvar Anotações**
   - Clique no botão **"Salvar"** na barra de ferramentas
   - As anotações serão armazenadas no banco de dados

6. **Visualizar Anotações Existentes**
   - Ao abrir um PDF, as anotações salvas são carregadas automaticamente
   - A lista de anotações da página atual é exibida na parte inferior

7. **Excluir Anotações**
   - Na lista de anotações, clique no ícone de lixeira
   - A anotação será removida imediatamente

### **Para Desenvolvedores:**

#### **Executar Migration**
```bash
php artisan migrate
```

#### **Usar o Componente em Outras Views**
```blade
<x-pdf-viewer-anotacoes 
    :documentoId="$documento->id" 
    :pdfUrl="route('caminho.para.pdf', $documento->id)"
    :anotacoes="$anotacoesExistentes" />
```

#### **Carregar Anotações via JavaScript**
```javascript
fetch(`/admin/processos/documentos/${documentoId}/anotacoes`)
    .then(response => response.json())
    .then(anotacoes => {
        console.log(anotacoes);
    });
```

## 🎨 Cores das Ferramentas

- **Highlight**: Amarelo (`rgba(251, 191, 36, 0.5)`)
- **Text**: Azul (`rgba(59, 130, 246, 0.7)`)
- **Drawing**: Vermelho (`rgba(239, 68, 68, 0.7)`)
- **Area**: Verde (`rgba(34, 197, 94, 0.5)`)
- **Comment**: Índigo (`rgba(99, 102, 241, 0.7)`)

## 📊 Estrutura de Dados

### **Formato JSON das Anotações**
```json
{
  "tipo": "area",
  "pagina": 1,
  "dados": {
    "startX": 100,
    "startY": 150,
    "endX": 300,
    "endY": 250
  },
  "comentario": "Área de interesse para análise"
}
```

### **Formato de Drawing**
```json
{
  "tipo": "drawing",
  "pagina": 2,
  "dados": {
    "points": [
      [100, 150],
      [110, 160],
      [120, 155]
    ]
  },
  "comentario": "Marcação manual"
}
```

## 🔒 Segurança

- ✅ Validação de permissões usando `validarPermissaoAcesso()`
- ✅ Usuários só podem acessar PDFs de processos que têm permissão
- ✅ Anotações são associadas ao usuário que as criou
- ✅ CSRF protection em todas as requisições

## 🔄 Próximas Melhorias

- [ ] Exportar PDF com anotações incorporadas
- [ ] Filtrar anotações por usuário
- [ ] Adicionar cores personalizadas para anotações
- [ ] Suporte a anotações colaborativas em tempo real
- [ ] Histórico de alterações de anotações
- [ ] Notificações quando alguém adiciona anotação

## 📝 Notas Técnicas

### **PDF.js**
- Versão: 3.11.174
- CDN: `https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/`
- Documentação: https://mozilla.github.io/pdf.js/

### **Compatibilidade**
- ✅ Chrome/Edge (recomendado)
- ✅ Firefox
- ✅ Safari
- ⚠️ Internet Explorer (não suportado)

### **Performance**
- PDFs grandes (>50MB) podem demorar para carregar
- Recomendado: PDFs até 20MB para melhor experiência
- Canvas é renderizado por página (otimizado)

## 🐛 Troubleshooting

### **PDF não carrega**
- Verifique se o arquivo existe no storage
- Confirme que a rota de visualização está correta
- Verifique permissões do arquivo no servidor

### **Anotações não salvam**
- Verifique o console do navegador para erros
- Confirme que o CSRF token está presente
- Verifique se a migration foi executada

### **Anotações não aparecem**
- Limpe o cache do navegador
- Verifique se está na página correta
- Confirme que as anotações foram salvas no banco

## 📞 Suporte

Para dúvidas ou problemas, consulte:
- Documentação do Laravel: https://laravel.com/docs
- Documentação do Alpine.js: https://alpinejs.dev
- Documentação do PDF.js: https://mozilla.github.io/pdf.js/
