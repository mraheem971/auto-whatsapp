const express = require('express');
const cors = require('cors');
const path = require('path');
const fs = require('fs');
const QRCode = require('qrcode');
const pino = require('pino');
const {
    default: makeWASocket,
    useMultiFileAuthState,
    DisconnectReason,
    fetchLatestBaileysVersion
} = require('@whiskeysockets/baileys');

const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors());
app.use(express.json());

const SESSIONS_DIR = path.join(__dirname, 'sessions');
if (!fs.existsSync(SESSIONS_DIR)) {
    fs.mkdirSync(SESSIONS_DIR, { recursive: true });
}

// In-memory store for active sessions
const sessions = new Map();

const logger = pino({ level: 'silent' });

async function initBaileysSession(sessionId, accountName) {
    const sessionPath = path.join(SESSIONS_DIR, sessionId);
    const { state, saveCreds } = await useMultiFileAuthState(sessionPath);
    const { version } = await fetchLatestBaileysVersion();

    const sessionData = {
        sessionId,
        accountName: accountName || sessionId,
        status: 'initializing',
        qr: null,
        qrImage: null,
        user: null,
        socket: null,
        lastUpdated: new Date()
    };

    sessions.set(sessionId, sessionData);

    const sock = makeWASocket({
        version,
        logger,
        printQRInTerminal: false,
        auth: state,
        browser: ['Auto WhatsApp', 'Chrome', '1.0.0'],
        syncFullHistory: false
    });

    sessionData.socket = sock;

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', async (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            sessionData.qr = qr;
            sessionData.status = 'qr_ready';
            try {
                sessionData.qrImage = await QRCode.toDataURL(qr, {
                    width: 300,
                    margin: 2,
                    color: {
                        dark: '#111b21',
                        light: '#ffffff'
                    }
                });
            } catch (err) {
                console.error('Error generating QR image:', err);
            }
            sessionData.lastUpdated = new Date();
        }

        if (connection === 'connecting') {
            sessionData.status = 'connecting';
            sessionData.lastUpdated = new Date();
        }

        if (connection === 'open') {
            sessionData.status = 'connected';
            sessionData.qr = null;
            sessionData.qrImage = null;

            const userJid = sock.user?.id || '';
            const phone = userJid ? userJid.split(':')[0].split('@')[0] : '';

            sessionData.user = {
                id: userJid,
                phone: phone,
                name: sock.user?.name || accountName || phone
            };
            sessionData.lastUpdated = new Date();
            console.log(`[Baileys] Session ${sessionId} connected successfully! Phone: ${phone}`);
        }

        if (connection === 'close') {
            const statusCode = lastDisconnect?.error?.output?.statusCode;
            const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

            console.log(`[Baileys] Session ${sessionId} closed. StatusCode: ${statusCode}. Reconnecting: ${shouldReconnect}`);

            if (shouldReconnect) {
                sessionData.status = 'reconnecting';
                // Wait briefly before reconnecting
                setTimeout(() => {
                    if (sessions.has(sessionId)) {
                        initBaileysSession(sessionId, accountName).catch(console.error);
                    }
                }, 3000);
            } else {
                sessionData.status = 'disconnected';
                sessionData.qr = null;
                sessionData.qrImage = null;
            }
            sessionData.lastUpdated = new Date();
        }
    });

    return sessionData;
}

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({ status: 'ok', activeSessions: sessions.size, timestamp: new Date() });
});

// Start or retrieve a session and generate QR
app.post('/api/sessions/start', async (req, res) => {
    try {
        const { sessionId, accountName } = req.body;

        if (!sessionId) {
            return res.status(400).json({ error: 'sessionId is required' });
        }

        let session = sessions.get(sessionId);

        if (session && session.status === 'connected') {
            return res.json({
                sessionId,
                status: 'connected',
                user: session.user
            });
        }

        // Clean previous session folder if we are starting fresh
        const sessionPath = path.join(SESSIONS_DIR, sessionId);
        if (req.body.fresh && fs.existsSync(sessionPath)) {
            try {
                if (session?.socket) {
                    session.socket.end();
                }
                fs.rmSync(sessionPath, { recursive: true, force: true });
                sessions.delete(sessionId);
            } catch (e) {
                console.error('Error cleaning session dir:', e);
            }
        }

        session = await initBaileysSession(sessionId, accountName);

        // Wait up to 5 seconds for initial QR if not already ready
        let attempts = 0;
        while (!session.qrImage && attempts < 25 && session.status !== 'connected') {
            await new Promise(resolve => setTimeout(resolve, 200));
            attempts++;
        }

        res.json({
            sessionId,
            status: session.status,
            qrImage: session.qrImage,
            user: session.user
        });
    } catch (error) {
        console.error('Error starting session:', error);
        res.status(500).json({ error: error.message });
    }
});

// Check session status
app.get('/api/sessions/status/:sessionId', (req, res) => {
    const { sessionId } = req.params;
    const session = sessions.get(sessionId);

    if (!session) {
        // Check if session directory exists on disk
        const sessionPath = path.join(SESSIONS_DIR, sessionId);
        if (fs.existsSync(sessionPath)) {
            return res.json({
                sessionId,
                status: 'stored_offline',
                user: null
            });
        }
        return res.status(404).json({ error: 'Session not found', status: 'not_found' });
    }

    res.json({
        sessionId: session.sessionId,
        status: session.status,
        qrImage: session.qrImage,
        user: session.user,
        lastUpdated: session.lastUpdated
    });
});

// Stop / Disconnect session
app.post('/api/sessions/stop/:sessionId', async (req, res) => {
    const { sessionId } = req.params;
    const session = sessions.get(sessionId);

    if (session?.socket) {
        try {
            await session.socket.logout();
        } catch (e) {
            console.error('Logout error:', e);
        }
    }

    sessions.delete(sessionId);
    res.json({ success: true, message: 'Session stopped' });
});

// Delete session and remove credentials
app.post('/api/sessions/delete/:sessionId', async (req, res) => {
    const { sessionId } = req.params;
    const session = sessions.get(sessionId);

    if (session?.socket) {
        try {
            session.socket.end();
        } catch (e) {}
    }

    sessions.delete(sessionId);

    const sessionPath = path.join(SESSIONS_DIR, sessionId);
    if (fs.existsSync(sessionPath)) {
        try {
            fs.rmSync(sessionPath, { recursive: true, force: true });
        } catch (e) {
            console.error('Error deleting session path:', e);
        }
    }

    res.json({ success: true, message: 'Session deleted' });
});

// Send Message
app.post('/api/messages/send', async (req, res) => {
    try {
        const { sessionId, receiver, message } = req.body;

        if (!sessionId || !receiver || !message) {
            return res.status(400).json({ error: 'sessionId, receiver, and message are required' });
        }

        let session = sessions.get(sessionId);

        // If session not in memory but directory exists, initialize it
        if (!session) {
            const sessionPath = path.join(SESSIONS_DIR, sessionId);
            if (fs.existsSync(sessionPath)) {
                session = await initBaileysSession(sessionId);
                let waitAttempts = 0;
                while (session.status !== 'connected' && waitAttempts < 20) {
                    await new Promise(r => setTimeout(r, 250));
                    waitAttempts++;
                }
            }
        }

        if (!session || !session.socket || session.status !== 'connected') {
            return res.status(400).json({ 
                error: 'WhatsApp session is not connected. Please scan the QR code first.' 
            });
        }

        // Format receiver number to WhatsApp JID
        let cleaned = receiver.toString().replace(/[^0-9]/g, '');
        let jid = cleaned.includes('@s.whatsapp.net') ? cleaned : `${cleaned}@s.whatsapp.net`;

        const result = await session.socket.sendMessage(jid, { text: message });

        res.json({
            success: true,
            message: 'Message sent successfully!',
            messageId: result?.key?.id,
            timestamp: new Date()
        });
    } catch (error) {
        console.error('Error sending message:', error);
        res.status(500).json({ error: error.message || 'Failed to send message' });
    }
});

app.listen(PORT, '127.0.0.1', () => {
    console.log(`Baileys WhatsApp Service running on http://127.0.0.1:${PORT}`);
});
