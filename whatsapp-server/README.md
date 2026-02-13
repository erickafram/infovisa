# INFOVISA WhatsApp Server

Servidor WhatsApp baseado em Baileys para integração com o sistema INFOVISA.

## Instalação no Servidor de Produção

### Pré-requisitos
- Node.js 18+ instalado no servidor
- npm

### 1. Instalar Node.js (se não tiver)

```bash
# CentOS/RHEL/Amazon Linux
curl -fsSL https://rpm.nodesource.com/setup_20.x | sudo bash -
sudo yum install -y nodejs

# Ubuntu/Debian
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs
```

Verificar instalação:
```bash
node --version   # v20.x.x
npm --version    # 10.x.x
```

### 2. Copiar o servidor para o servidor de produção

```bash
# Criar diretório para o servidor WhatsApp
sudo mkdir -p /opt/infovisa-whatsapp
sudo chown -R $USER:$USER /opt/infovisa-whatsapp

# Copiar os arquivos (do repositório do INFOVISA)
cp -r /var/www/html/infovisa/whatsapp-server/* /opt/infovisa-whatsapp/

# Instalar dependências
cd /opt/infovisa-whatsapp
npm install --production
```

### 3. Testar manualmente

```bash
cd /opt/infovisa-whatsapp

# Sem API Key (modo aberto)
node src/server.js

# Com API Key (recomendado para produção)
API_KEY=sua_chave_secreta_aqui PORT=3000 node src/server.js
```

Acesse `http://localhost:3000` no navegador. Deve retornar:
```json
{"name": "INFOVISA WhatsApp Server", "version": "1.0.0", "status": "running"}
```

### 4. Configurar como serviço (systemd)

Criar o arquivo de serviço:

```bash
sudo nano /etc/systemd/system/infovisa-whatsapp.service
```

Conteúdo:
```ini
[Unit]
Description=INFOVISA WhatsApp Server (Baileys)
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=/opt/infovisa-whatsapp
ExecStart=/usr/bin/node src/server.js
Restart=on-failure
RestartSec=10
Environment=PORT=3000
Environment=API_KEY=sua_chave_secreta_aqui
StandardOutput=syslog
StandardError=syslog
SyslogIdentifier=infovisa-whatsapp

[Install]
WantedBy=multi-user.target
```

Ativar e iniciar:
```bash
sudo systemctl daemon-reload
sudo systemctl enable infovisa-whatsapp
sudo systemctl start infovisa-whatsapp

# Verificar status
sudo systemctl status infovisa-whatsapp

# Ver logs
sudo journalctl -u infovisa-whatsapp -f
```

### 5. Configurar no INFOVISA

1. Acesse `/admin/whatsapp/configuracao` no sistema
2. Configure:
   - **URL do Servidor Baileys:** `http://localhost:3000`
   - **Chave de API:** a mesma que definiu no `API_KEY` (ou deixe vazio se não usou)
   - **Nome da Sessão:** `infovisa`
3. Clique em **Salvar Configurações**
4. Clique em **Conectar WhatsApp**
5. Escaneie o QR Code com o celular
6. Ative o envio

### Comandos úteis

```bash
# Reiniciar o servidor WhatsApp
sudo systemctl restart infovisa-whatsapp

# Parar
sudo systemctl stop infovisa-whatsapp

# Ver logs em tempo real
sudo journalctl -u infovisa-whatsapp -f

# Ver status
sudo systemctl status infovisa-whatsapp
```

## Endpoints da API

| Método | Rota | Descrição |
|--------|------|-----------|
| GET | `/` | Health check |
| POST | `/sessions` | Criar sessão (body: `{sessionId}`) |
| GET | `/sessions/:id/status` | Status da sessão + QR Code |
| DELETE | `/sessions/:id` | Encerrar sessão |
| POST | `/sessions/:id/messages/send` | Enviar mensagem (body: `{jid, message}`) |
| GET | `/sessions` | Listar todas as sessões |

## Porta e Firewall

O servidor roda na porta **3000** por padrão. Como o Laravel acessa localhost, não precisa abrir esta porta no firewall externo. Apenas garanta que a comunicação interna funcione:

```bash
# Testar do próprio servidor
curl http://localhost:3000
```
