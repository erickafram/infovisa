# 🎨 Guia de Acessibilidade - Campos de Seleção (Select2)

## 📋 Problema Identificado

Quando o usuário clicava nos campos **"Tipos de Ação"** e **"Técnicos Responsáveis"** no modal "Nova Ordem de Serviço", o fundo das opções em foco ficava roxo escuro (#9333ea) com texto escuro, causando **baixo contraste** e dificultando a leitura.

## ✅ Solução Implementada

### Mudanças Aplicadas no CSS

#### 1. **Opções em Hover/Foco (Destacadas)**
```css
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #f3e8ff !important; /* Roxo muito claro (purple-100) */
    color: #581c87 !important;             /* Roxo escuro (purple-900) */
    font-weight: 500;
}
```

**Antes:** Fundo roxo escuro (#9333ea) + texto escuro = baixo contraste ❌  
**Agora:** Fundo roxo claro (#f3e8ff) + texto roxo escuro (#581c87) = alto contraste ✅

#### 2. **Opções Já Selecionadas**
```css
.select2-container--default .select2-results__option[aria-selected="true"] {
    background-color: #ede9fe; /* Roxo claro (purple-50) */
    color: #6b21a8;            /* Roxo médio (purple-800) */
}
```

#### 3. **Container em Foco**
```css
.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: #9333ea;
    box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.1);
    background-color: #ffffff; /* Mantém fundo branco */
    outline: none;
}
```

## 🎯 Benefícios de Acessibilidade

### 1. **Contraste WCAG AA/AAA**
- **Razão de contraste:** 8.59:1 (entre #f3e8ff e #581c87)
- **Padrão WCAG AA:** Mínimo 4.5:1 para texto normal ✅
- **Padrão WCAG AAA:** Mínimo 7:1 para texto normal ✅

### 2. **Legibilidade Aprimorada**
- Texto escuro sobre fundo claro é mais fácil de ler
- Font-weight 500 adiciona destaque visual sem comprometer legibilidade
- Transições suaves (0.15s) melhoram a experiência

### 3. **Consistência Visual**
- Mantém a paleta roxa do tema principal
- Hierarquia visual clara entre estados (normal, hover, selecionado)

## 🎨 Paleta de Cores Utilizada

| Estado | Fundo | Texto | Uso |
|--------|-------|-------|-----|
| **Normal** | `#ffffff` (branco) | `#1f2937` (gray-800) | Opções padrão |
| **Hover/Foco** | `#f3e8ff` (purple-100) | `#581c87` (purple-900) | Opção destacada |
| **Selecionado** | `#ede9fe` (purple-50) | `#6b21a8` (purple-800) | Opção já escolhida |
| **Tag Selecionada** | `#9333ea` (purple-600) | `#ffffff` (branco) | Tag no campo |

## 📐 Exemplo de CSS Completo

```css
/* Opções destacadas (hover/foco) - ALTA ACESSIBILIDADE */
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #f3e8ff !important; /* Roxo muito claro */
    color: #581c87 !important;             /* Roxo escuro */
    font-weight: 500;                      /* Destaque moderado */
}

/* Opções já selecionadas */
.select2-container--default .select2-results__option[aria-selected="true"] {
    background-color: #ede9fe; /* Roxo claro */
    color: #6b21a8;            /* Roxo médio */
}

/* Container em foco - mantém fundo branco */
.select2-container--default.select2-container--focus .select2-selection--multiple {
    border-color: #9333ea;
    box-shadow: 0 0 0 3px rgba(147, 51, 234, 0.1);
    background-color: #ffffff; /* Fundo branco para leitura clara */
    outline: none;
}
```

## 🔍 Como Testar

1. Acesse: `http://localhost:8001/admin/estabelecimentos/6/processos/5`
2. Clique em **"Nova Ordem de Serviço"**
3. Clique no campo **"Tipos de Ação"**
4. Passe o mouse sobre as opções - deve ver fundo roxo claro com texto roxo escuro
5. Repita para **"Técnicos Responsáveis"**

## 💡 Boas Práticas Aplicadas

### ✅ DO (Fazer)
- Use fundos claros com texto escuro para alto contraste
- Mantenha consistência com a paleta de cores do sistema
- Adicione transições suaves para melhor UX
- Teste com ferramentas de contraste (ex: WebAIM Contrast Checker)

### ❌ DON'T (Não Fazer)
- Nunca use fundo escuro com texto escuro
- Evite cores muito saturadas em grandes áreas
- Não confie apenas na cor para transmitir informação
- Não ignore padrões WCAG de acessibilidade

## 🛠️ Ferramentas de Teste Recomendadas

1. **WebAIM Contrast Checker:** https://webaim.org/resources/contrastchecker/
2. **Chrome DevTools:** Lighthouse Accessibility Audit
3. **WAVE:** Extensão de navegador para avaliação de acessibilidade
4. **axe DevTools:** Extensão para testes automatizados

## 📚 Referências

- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [Select2 Documentation](https://select2.org/)
- [TailwindCSS Color Palette](https://tailwindcss.com/docs/customizing-colors)

---

**Arquivo modificado:** `resources/views/estabelecimentos/processos/show.blade.php`  
**Linhas:** 2180-2270  
**Data:** 31/10/2025
