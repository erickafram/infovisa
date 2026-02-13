const express = require('express');
const { makeWASocket, useMultiFileAuthState, DisconnectReason, fetchLatestBaileysVersion } = require('@whiskeysockets/baileys');
const pino = require('pino');
const QRCode = require('qrcode');
const path = require('path');
const fs = require('fs');

const app = express();
app.use(express.json());

// ============================================
// Configuração
// ============================================
const PORT = process.env.PORT || 3000;
const API_KEY = process.env.API_KEY || ''; // Deixe vazio para desativar autenticação
const SESSIONS_DIR = path.join(__dirname, '..', 'sessions');

// Garantir que o diretório de sessões existe
if (!fs.existsSync(SESSIONS_DIR)) {
    fs.mkdirSync(SESSIONS_DIR, { recursive: true });
}

// ============================================
// Armazenamento de sessões em memória
// ============================================
const sessions = new Map();
// Estrutura: sessionId -> { socket, qr, status, saveCreds }

// Logger silencioso para o Baileys
const logger = pino({ level: 'silent' });

// ============================================
// Middleware de autenticação
// ============================================
function authMiddleware(req, res, next) {
    if (!API_KEY) return next(); // Sem API_KEY = sem autenticação

    const auth = req.headers.authorization;
    if (!auth || auth !== `Bearer ${API_KEY}`) {
        return res.status(401).json({ message: 'Não autorizado. Forneça uma API Key válida.' });
    }
    next();
}

app.use(authMiddleware);

// ============================================
// Funções auxiliares
// ============================================

async function createSession(sessionId) {
    const sessionPath = path.join(SESSIONS_DIR, sessionId);

    if (!fs.existsSync(sessionPath)) {
        fs.mkdirSync(sessionPath, { recursive: true });
    }

    const { state, saveCreds } = await useMultiFileAuthState(sessionPath);
    const { version } = await fetchLatestBaileysVersion();

    const socket = makeWASocket({
        version,
        auth: state,
        logger,
        printQRInTerminal: false,
        browser: ['INFOVISA', 'Chrome', '1.0.0'],
        generateHighQualityLinkPreview: false,
        syncFullHistory: false,
    });

    // Armazenar sessão
    const sessionData = {
        socket,
        qr: null,
        qrDataUrl: null,
        status: 'aguardando_qr',
        saveCreds,
    };
    sessions.set(sessionId, sessionData);

    // Evento: QR Code recebido
    socket.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;
        const session = sessions.get(sessionId);
        if (!session) return;

        if (qr) {
            session.qr = qr;
            session.status = 'aguardando_qr';
            // Gerar QR Code como Data URL (imagem base64)
            try {
                session.qrDataUrl = await QRCode.toDataURL(qr, { width: 300, margin: 2 });
            } catch (err) {
                console.error(`[${sessionId}] Erro ao gerar QR Code:`, err.message);
            }
            console.log(`[${sessionId}] QR Code gerado. Escaneie com o WhatsApp.`);
        }

        if (connection === 'open') {
            session.status = 'conectado';
            session.qr = null;
            session.qrDataUrl = null;
            console.log(`[${sessionId}] ✅ Conectado ao WhatsApp!`);
        }

        if (connection === 'close') {
            const statusCode = lastDisconnect?.error?.output?.statusCode;
            const reason = DisconnectReason;

            console.log(`[${sessionId}] ❌ Desconectado. Código: ${statusCode}`);

            if (statusCode === reason.loggedOut) {
                // Usuário deslogou - limpar sessão
                session.status = 'desconectado';
                sessions.delete(sessionId);
                // Limpar arquivos de autenticação
                if (fs.existsSync(sessionPath)) {
                    fs.rmSync(sessionPath, { recursive: true, force: true });
                }
                console.log(`[${sessionId}] Sessão removida (logout).`);
            } else if (statusCode !== reason.connectionClosed) {
                // Reconectar automaticamente
                console.log(`[${sessionId}] Tentando reconectar...`);
                session.status = 'reconectando';
                setTimeout(() => createSession(sessionId), 3000);
            } else {
                session.status = 'desconectado';
            }
        }
    });

    // Evento: Credenciais atualizadas
    socket.ev.on('creds.update', saveCreds);

    return sessionData;
}

// ============================================
// Rotas da API
// ============================================

// Health check
app.get('/', (req, res) => {
    res.json({
        name: 'INFOVISA WhatsApp Server',
        version: '1.0.0',
        status: 'running',
        sessions: sessions.size,
        uptime: process.uptime(),
    });
});

// Criar/iniciar uma sessão
app.post('/sessions', async (req, res) => {
    try {
        const { sessionId } = req.body;

        if (!sessionId) {
            return res.status(400).json({ message: 'sessionId é obrigatório.' });
        }

        // Se já existe e está conectada, retornar status
        const existing = sessions.get(sessionId);
        if (existing && existing.status === 'conectado') {
            return res.json({
                message: 'Sessão já está conectada.',
                status: 'conectado',
            });
        }

        // Se existe mas está aguardando QR, retornar o QR
        if (existing && existing.status === 'aguardando_qr' && existing.qrDataUrl) {
            return res.json({
                message: 'Sessão aguardando QR Code.',
                status: 'aguardando_qr',
                qr: existing.qrDataUrl,
            });
        }

        // Criar nova sessão
        const session = await createSession(sessionId);

        // Aguardar um pouco para o QR Code ser gerado
        await new Promise(resolve => setTimeout(resolve, 3000));

        const updatedSession = sessions.get(sessionId);

        res.json({
            message: updatedSession?.status === 'conectado'
                ? 'Sessão conectada automaticamente (credenciais salvas).'
                : 'Sessão iniciada. Escaneie o QR Code.',
            status: updatedSession?.status || 'aguardando_qr',
            qr: updatedSession?.qrDataUrl || null,
        });
    } catch (err) {
        console.error('Erro ao criar sessão:', err.message);
        res.status(500).json({ message: 'Erro ao criar sessão: ' + err.message });
    }
});

// Verificar status de uma sessão
app.get('/sessions/:sessionId/status', (req, res) => {
    const { sessionId } = req.params;
    const session = sessions.get(sessionId);

    if (!session) {
        // Verificar se existem credenciais salvas
        const sessionPath = path.join(SESSIONS_DIR, sessionId);
        if (fs.existsSync(sessionPath)) {
            return res.json({
                status: 'desconectado',
                message: 'Sessão existe mas não está ativa. Inicie a sessão novamente.',
                hasCredentials: true,
            });
        }
        return res.json({
            status: 'desconectado',
            message: 'Sessão não encontrada.',
            hasCredentials: false,
        });
    }

    res.json({
        status: session.status,
        qr: session.status === 'aguardando_qr' ? session.qrDataUrl : null,
    });
});

// Encerrar/deletar uma sessão
app.delete('/sessions/:sessionId', async (req, res) => {
    const { sessionId } = req.params;
    const session = sessions.get(sessionId);

    if (session) {
        try {
            await session.socket.logout();
        } catch (err) {
            // Ignorar erro de logout
        }
        try {
            session.socket.end();
        } catch (err) {
            // Ignorar
        }
        sessions.delete(sessionId);
    }

    // Limpar arquivos
    const sessionPath = path.join(SESSIONS_DIR, sessionId);
    if (fs.existsSync(sessionPath)) {
        fs.rmSync(sessionPath, { recursive: true, force: true });
    }

    res.json({ message: 'Sessão encerrada e removida.', status: 'desconectado' });
});

// Enviar mensagem de texto
app.post('/sessions/:sessionId/messages/send', async (req, res) => {
    const { sessionId } = req.params;
    const { jid, type, message } = req.body;

    const session = sessions.get(sessionId);

    if (!session || session.status !== 'conectado') {
        return res.status(400).json({
            message: 'Sessão não conectada. Inicie e conecte a sessão primeiro.',
        });
    }

    if (!jid || !message) {
        return res.status(400).json({
            message: 'jid e message são obrigatórios.',
        });
    }

    try {
        const result = await session.socket.sendMessage(jid, { text: message });

        res.json({
            message: 'Mensagem enviada com sucesso.',
            messageId: result.key.id,
            key: result.key,
        });
    } catch (err) {
        console.error(`[${sessionId}] Erro ao enviar mensagem:`, err.message);
        res.status(500).json({
            message: 'Erro ao enviar mensagem: ' + err.message,
        });
    }
});

// Listar todas as sessões
app.get('/sessions', (req, res) => {
    const list = [];
    for (const [id, session] of sessions) {
        list.push({
            sessionId: id,
            status: session.status,
        });
    }
    res.json({ sessions: list });
});

// ============================================
// Restaurar sessões salvas ao iniciar
// ============================================
async function restoreSessions() {
    if (!fs.existsSync(SESSIONS_DIR)) return;

    const dirs = fs.readdirSync(SESSIONS_DIR, { withFileTypes: true })
        .filter(d => d.isDirectory())
        .map(d => d.name);

    for (const sessionId of dirs) {
        const credsFile = path.join(SESSIONS_DIR, sessionId, 'creds.json');
        if (fs.existsSync(credsFile)) {
            console.log(`Restaurando sessão: ${sessionId}...`);
            try {
                await createSession(sessionId);
                // Aguardar conexão
                await new Promise(resolve => setTimeout(resolve, 2000));
            } catch (err) {
                console.error(`Erro ao restaurar sessão ${sessionId}:`, err.message);
            }
        }
    }
}

// ============================================
// Iniciar servidor
// ============================================
app.listen(PORT, async () => {
    console.log('');
    console.log('╔══════════════════════════════════════════╗');
    console.log('║   INFOVISA WhatsApp Server               ║');
    console.log(`║   Rodando na porta: ${PORT}                  ║`);
    console.log('║   Powered by Baileys                     ║');
    console.log('╚══════════════════════════════════════════╝');
    console.log('');

    if (API_KEY) {
        console.log('🔐 Autenticação por API Key: ATIVADA');
    } else {
        console.log('⚠️  Autenticação por API Key: DESATIVADA');
    }

    console.log('');

    // Restaurar sessões existentes
    await restoreSessions();
});
