# 🏥 InfoVISA 3.0 - Sistema de Gestão de Vigilância Sanitária

## 📋 Sobre o Projeto

Sistema completo de gerenciamento de vigilância sanitária municipal desenvolvido em Laravel 11 com PostgreSQL. O InfoVISA 3.0 permite o gerenciamento de processos de licenciamento sanitário, estabelecimentos comerciais, documentos, ordens de serviço e muito mais.

### Principais Funcionalidades

- ✅ **Gestão de Processos de Licenciamento Sanitário**
- 🏢 **Cadastro e Gerenciamento de Estabelecimentos**
- 📄 **Sistema de Documentos e Arquivos**
- 👥 **Gestão de Usuários Internos e Externos**
- 🔔 **Sistema de Alertas e Notificações**
- 💬 **Chat/Mensagens em Tempo Real**
- 📝 **Ordens de Serviço**
- 📊 **Relatórios e Dashboards**
- 🔏 **Assinatura Digital de Documentos**
- 📋 **Portarias Municipais Públicas**
- 🔒 **Controle de Permissões e Auditoria**

---

## 🚀 Stack Tecnológica

### Backend
- **Framework**: Laravel 11.x
- **PHP**: 8.2+
- **Banco de Dados**: PostgreSQL 15+
- **Autenticação**: Laravel Sanctum
- **Permissões**: Spatie Laravel Permission

### Frontend
- **CSS Framework**: Tailwind CSS 3.x
- **JavaScript**: Alpine.js 3.x
- **Build Tool**: Vite 5.x

### Dependências Principais
- **PDFs**: DomPDF, FPDI
- **QR Codes**: Endroid QR Code
- **Imagens**: Intervention Image
- **Excel**: Maatwebsite Excel
- **Debug**: Laravel Telescope, Debugbar (dev)

---

## 📁 Estrutura do Projeto

```
laravel-infovisa/
├── app/
│   ├── Console/Commands/       # Comandos Artisan customizados
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/         # Controllers administrativos
│   │   │   ├── Company/       # Controllers área empresas
│   │   │   ├── Public/        # Controllers públicos
│   │   │   └── Api/           # API Controllers
│   │   ├── Middleware/        # Middlewares customizados
│   │   └── Requests/          # Form Requests
│   ├── Models/                # Eloquent Models
│   ├── Policies/              # Authorization Policies
│   ├── Services/              # Business Logic Services
│   ├── Traits/                # Traits reutilizáveis
│   ├── Observers/             # Model Observers
│   └── Enums/                 # Enumerações
├── database/
│   ├── migrations/            # Database Migrations
│   ├── seeders/               # Database Seeders
│   └── factories/             # Model Factories
├── resources/
│   ├── views/
│   │   ├── layouts/           # Layouts base
│   │   ├── components/        # Blade Components
│   │   ├── admin/             # Views admin
│   │   ├── company/           # Views empresas
│   │   └── public/            # Views públicas
│   ├── css/                   # Arquivos CSS
│   └── js/                    # Arquivos JavaScript
├── routes/
│   ├── web.php                # Rotas web
│   ├── api.php                # Rotas API
│   └── console.php            # Comandos Artisan
├── storage/
│   ├── app/
│   │   ├── private/           # Arquivos privados
│   │   └── uploads/           # Uploads de usuários
│   └── logs/                  # Logs da aplicação
└── tests/
    ├── Feature/               # Testes Feature
    └── Unit/                  # Testes Unit
```

---

## ⚙️ Instalação e Configuração

### Pré-requisitos

- PHP 8.2 ou superior
- Composer 2.x
- PostgreSQL 15 ou superior
- Node.js 18+ e npm
- Redis (opcional, recomendado para cache)

### Passo a Passo

1. **Clone o repositório**
```bash
git clone <url-do-repositorio>
cd laravel-infovisa
```

2. **Instale as dependências PHP**
```bash
composer install
```

3. **Instale as dependências Node.js**
```bash
npm install
```

4. **Configure o arquivo .env**
```bash
cp .env.example .env
php artisan key:generate
```

Edite o `.env` e configure o banco de dados:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=infovisa
DB_USERNAME=postgres
DB_PASSWORD=sua_senha
```

5. **Execute as migrations**
```bash
php artisan migrate
```

6. **Crie usuário administrador (seed)**
```bash
php artisan db:seed
```

7. **Compile os assets**
```bash
npm run dev
```

8. **Inicie o servidor de desenvolvimento**
```bash
php artisan serve
```

Acesse: `http://localhost:8000`

---

## 🗄️ Banco de Dados

### Principais Tabelas

- `usuarios` - Usuários internos (funcionários)
- `usuarios_externos` - Usuários externos (empresas)
- `estabelecimentos` - Estabelecimentos comerciais
- `processos` - Processos de licenciamento
- `documentos` - Documentos do sistema
- `arquivos` - Arquivos enviados
- `ordem_servico` - Ordens de serviço
- `portarias` - Portarias municipais
- `mensagens` - Sistema de chat
- `alertas` - Sistema de alertas
- `assinaturas` - Assinatura digital
- `logs` - Logs de auditoria

---

## 👥 Níveis de Acesso

### Sistema Interno
1. **Nível 1** - Administrador Total
2. **Nível 2** - Coordenador/Gestor
3. **Nível 3** - Técnico/Fiscal
4. **Nível 4** - Suporte/Consulta
5. **Nível 5** - Visualizador

### Sistema Externo
- **Empresas** - Proprietários de estabelecimentos
- **Responsáveis Técnicos** - RT
- **Responsáveis Legais** - RL

---

## 🧪 Testes

Execute os testes com:

```bash
# Todos os testes
php artisan test

# Testes específicos
php artisan test --filter=NomeDoTeste

# Com coverage
php artisan test --coverage
```

---

## 📚 Comandos Úteis

```bash
# Limpar cache
php artisan optimize:clear

# Gerar cache de configuração (produção)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Executar queues
php artisan queue:work

# Acessar Tinker (console interativo)
php artisan tinker

# Ver logs em tempo real
php artisan pail

# Acessar Telescope (debug)
# http://localhost:8000/telescope
```

---

## 🔒 Segurança

- ✅ Autenticação multi-guard (interno/externo)
- ✅ Controle de permissões granular (Spatie Permission)
- ✅ CSRF Protection nativo do Laravel
- ✅ Validação rigorosa de inputs
- ✅ Upload seguro de arquivos
- ✅ Logs de auditoria completos
- ✅ Rate limiting em APIs

---

## 📦 Dependências Instaladas

### PHP
- `spatie/laravel-permission` - Gestão de permissões
- `laravel/sanctum` - Autenticação API
- `intervention/image-laravel` - Manipulação de imagens
- `barryvdh/laravel-dompdf` - Geração de PDFs
- `endroid/qr-code` - Geração de QR Codes
- `maatwebsite/excel` - Import/Export Excel
- `setasign/fpdi` - Manipulação de PDFs
- `laravel/telescope` (dev) - Debug e monitoramento
- `barryvdh/laravel-debugbar` (dev) - Debug bar

### JavaScript
- `alpinejs` - Framework JavaScript reativo
- `tailwindcss` - Framework CSS
- `postcss` - Processador CSS
- `autoprefixer` - Prefixos CSS automáticos

---

## 🚀 Deploy

### Checklist de Deploy

- [ ] Configurar variáveis de ambiente de produção
- [ ] Executar migrations
- [ ] Otimizar autoload: `composer install --optimize-autoloader --no-dev`
- [ ] Gerar caches: `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`
- [ ] Compilar assets: `npm run build`
- [ ] Configurar supervisor para queues
- [ ] Configurar SSL/HTTPS
- [ ] Configurar backups automáticos
- [ ] Desabilitar debug: `APP_DEBUG=false`
- [ ] Desabilitar Telescope: `TELESCOPE_ENABLED=false`

---

## 📝 Roadmap

### Fase 1: Setup Inicial ✅
- [x] Criar projeto Laravel 11
- [x] Configurar PostgreSQL
- [x] Instalar dependências
- [x] Configurar Tailwind CSS e Alpine.js
- [x] Estrutura de pastas

### Fase 2: Banco de Dados (Em Andamento)
- [ ] Criar todas as migrations
- [ ] Definir relacionamentos
- [ ] Criar seeders
- [ ] Criar factories

### Fase 3: Models e Relacionamentos
- [ ] Criar Models Eloquent
- [ ] Definir relacionamentos
- [ ] Implementar Observers
- [ ] Criar Traits

### Fase 4: Autenticação e Autorização
- [ ] Multi-auth (interno/externo)
- [ ] Policies e Gates
- [ ] Middleware de permissões
- [ ] Recuperação de senha

### Fase 5: Controllers e Rotas
- [ ] Resource Controllers
- [ ] Form Requests
- [ ] API Routes

### Fase 6: Views e Frontend
- [ ] Layouts Blade
- [ ] Components reutilizáveis
- [ ] Migrar views para Blade

### Fase 7: Funcionalidades Específicas
- [ ] Sistema de upload
- [ ] Geração de PDFs
- [ ] Sistema de alertas
- [ ] Chat em tempo real
- [ ] Assinatura digital

### Fase 8: Testes
- [ ] Feature Tests
- [ ] Unit Tests

### Fase 9: Otimização
- [ ] Otimizar queries
- [ ] Implementar cache
- [ ] Configurar queues

### Fase 10: Deploy
- [ ] Servidor de produção
- [ ] Migração de dados
- [ ] Documentação

---

## 👨‍💻 Desenvolvimento

### Padrões de Código

- PSR-12 Coding Style
- SOLID Principles
- Repository Pattern (quando necessário)
- Service Layer para lógica de negócio
- DTOs para transferência de dados

### Commits

Siga o padrão Conventional Commits:
```
feat: adiciona nova funcionalidade
fix: corrige bug
docs: atualiza documentação
style: formatação de código
refactor: refatoração
test: adiciona testes
chore: tarefas de manutenção
```

---

## 📞 Suporte

Para questões e suporte, entre em contato com a equipe de desenvolvimento.

---

## 📄 Licença

Este projeto é proprietário e confidencial.

---

**Desenvolvido para Vigilância Sanitária Municipal**  
*InfoVISA 3.0 - Versão Laravel*  
*© 2025 - Todos os direitos reservados*
