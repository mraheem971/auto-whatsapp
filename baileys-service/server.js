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

// Global process error handlers to prevent crash on Baileys socket timeouts
process.on('uncaughtException', (err) => {
    console.error('[Uncaught Exception]', err?.message || err);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('[Unhandled Rejection]', reason?.message || reason);
});

app.use(cors());
app.use(express.json());

const SESSIONS_DIR = path.join(__dirname, 'sessions');
if (!fs.existsSync(SESSIONS_DIR)) {
    fs.mkdirSync(SESSIONS_DIR, { recursive: true });
}

// In-memory store for active sessions
const sessions = new Map();

// Auto-Reply Engine Cache & Cooldown Store
const autoReplyRuleCache = new Map(); // sessionId -> { rules, lastFetched }
const userCooldowns = new Map(); // `${ruleId}_${remoteJid}_${senderPhone}` -> timestamp

async function getActiveAutoReplies(sessionId) {
    const cached = autoReplyRuleCache.get(sessionId);
    const now = Date.now();
    if (cached && (now - cached.lastFetched < 10000)) { // 10s memory cache
        return cached.rules;
    }

    try {
        const res = await fetch(`http://127.0.0.1:8000/api/autoreply/rules/${sessionId}`, { signal: AbortSignal.timeout(3000) });
        if (res.ok) {
            const data = await res.json();
            if (data.success && Array.isArray(data.rules)) {
                autoReplyRuleCache.set(sessionId, { rules: data.rules, lastFetched: now });
                return data.rules;
            }
        }
    } catch (e) {
        if (cached) return cached.rules;
    }
    return [];
}

async function processIncomingAutoReply(sessionId, sock, msg) {
    if (!msg || !msg.message || msg.key?.fromMe) return;

    const remoteJid = msg.key?.remoteJid;
    if (!remoteJid || remoteJid.endsWith('@broadcast')) return;

    const msgContent = msg.message;
    const incomingText = (msgContent.conversation || 
                          msgContent.extendedTextMessage?.text || 
                          msgContent.imageMessage?.caption || 
                          msgContent.videoMessage?.caption || '').trim();

    if (!incomingText) return;

    const isGroup = remoteJid.endsWith('@g.us');
    const chatType = isGroup ? 'group' : 'individual';
    const participantJid = msg.key.participant || remoteJid;
    const senderPhone = participantJid.split('@')[0].split(':')[0];
    const senderName = msg.pushName || `+${senderPhone}`;

    const rules = await getActiveAutoReplies(sessionId);
    if (!rules || rules.length === 0) return;

    const lowerText = incomingText.toLowerCase();
    let matchedRule = null;
    let fallbackRule = null;

    for (const rule of rules) {
        if (rule.chat_scope !== 'all' && rule.chat_scope !== chatType) {
            continue;
        }

        if (rule.match_type === 'fallback') {
            fallbackRule = rule;
            continue;
        }

        let kwList = [];
        if (Array.isArray(rule.keywords)) {
            kwList = rule.keywords;
        } else if (typeof rule.keywords === 'string') {
            try {
                const parsed = JSON.parse(rule.keywords);
                kwList = Array.isArray(parsed) ? parsed : [rule.keywords];
            } catch {
                kwList = rule.keywords.split(',').map(s => s.trim()).filter(Boolean);
            }
        }

        let isMatch = false;
        for (const kw of kwList) {
            const lowerKw = kw.toLowerCase().trim();
            if (!lowerKw) continue;

            if (rule.match_type === 'exact' && lowerText === lowerKw) {
                isMatch = true;
                break;
            } else if (rule.match_type === 'contains' && lowerText.includes(lowerKw)) {
                isMatch = true;
                break;
            } else if (rule.match_type === 'starts_with' && lowerText.startsWith(lowerKw)) {
                isMatch = true;
                break;
            } else if (rule.match_type === 'ends_with' && lowerText.endsWith(lowerKw)) {
                isMatch = true;
                break;
            }
        }

        if (isMatch) {
            matchedRule = rule;
            break;
        }
    }

    const targetRule = matchedRule || fallbackRule;
    if (!targetRule) return;

    // Check anti-spam cooldown
    const cooldownKey = `${targetRule.id}_${remoteJid}_${senderPhone}`;
    const now = Date.now();
    if (targetRule.cooldown_seconds && targetRule.cooldown_seconds > 0) {
        const lastHit = userCooldowns.get(cooldownKey) || 0;
        if (now - lastHit < targetRule.cooldown_seconds * 1000) {
            console.log(`[AutoReply Cooldown] Skipped rule "${targetRule.name}" for ${senderPhone} (Cooldown active)`);
            return;
        }
    }
    userCooldowns.set(cooldownKey, now);

    // Variable substitutions
    const processedText = targetRule.reply_message
        .replace(/\{name\}/g, senderName)
        .replace(/\{sender_phone\}/g, senderPhone)
        .replace(/\{time\}/g, new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }))
        .replace(/\{date\}/g, new Date().toLocaleDateString());

    console.log(`[AutoReply Triggered] Rule: "${targetRule.name}" -> Replying to ${remoteJid}`);

    try {
        await sock.sendMessage(remoteJid, { text: processedText }, { quoted: msg });
        fetch(`http://127.0.0.1:8000/api/autoreply/log-hit/${targetRule.id}`, { method: 'POST' }).catch(() => {});
    } catch (err) {
        console.error(`[AutoReply Send Error]:`, err?.message || err);
    }
}

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
        contacts: new Map(),
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

    sock.ev.on('contacts.upsert', (contacts) => {
        for (const contact of contacts) {
            if (contact.id && !contact.id.endsWith('@g.us') && !contact.id.endsWith('@broadcast')) {
                const phone = contact.id.split('@')[0].split(':')[0];
                const name = contact.name || contact.notify || contact.verifiedName || `+${phone}`;
                sessionData.contacts.set(phone, {
                    id: contact.id,
                    phone: phone,
                    name: name
                });
            }
        }
    });

    sock.ev.on('contacts.update', (updates) => {
        for (const update of updates) {
            if (update.id && !update.id.endsWith('@g.us') && !update.id.endsWith('@broadcast')) {
                const phone = update.id.split('@')[0].split(':')[0];
                const existing = sessionData.contacts.get(phone) || { id: update.id, phone: phone, name: `+${phone}` };
                if (update.name) existing.name = update.name;
                if (update.notify) existing.name = update.notify;
                sessionData.contacts.set(phone, existing);
            }
        }
    });

    sock.ev.on('chats.upsert', (chats) => {
        for (const chat of chats) {
            if (chat.id && !chat.id.endsWith('@g.us') && !chat.id.endsWith('@broadcast')) {
                const phone = chat.id.split('@')[0].split(':')[0];
                if (!sessionData.contacts.has(phone)) {
                    sessionData.contacts.set(phone, {
                        id: chat.id,
                        phone: phone,
                        name: chat.name || `+${phone}`
                    });
                }
            }
        }
    });

    sock.ev.on('messages.upsert', async ({ messages, type }) => {
        for (const msg of messages) {
            if (msg.key && msg.pushName && !msg.key.fromMe) {
                const jid = msg.key.participant || msg.key.remoteJid;
                if (jid && !jid.endsWith('@g.us') && !jid.endsWith('@broadcast')) {
                    const phone = jid.split('@')[0].split(':')[0];
                    const existing = sessionData.contacts.get(phone) || { id: jid, phone: phone };
                    if (!existing.name || existing.name.startsWith('+')) {
                        existing.name = msg.pushName;
                    }
                    sessionData.contacts.set(phone, existing);
                }
            }

            // Process Auto-Reply & Keyword Bot Rules
            if (type === 'notify' || !type) {
                processIncomingAutoReply(sessionId, sock, msg).catch(e => {
                    console.error('[AutoReply Trigger Error]', e?.message || e);
                });
            }
        }
    });

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

// Send Message (Direct Contact or Group)
app.post('/api/messages/send', async (req, res) => {
    try {
        const { sessionId, receiver, message, isGroup } = req.body;

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

        // Format receiver to either WhatsApp user JID or group JID
        let target = receiver.toString().trim();
        let jid;

        if (target.endsWith('@g.us')) {
            jid = target;
        } else if (target.endsWith('@s.whatsapp.net')) {
            jid = target;
        } else if (isGroup || target.includes('-') || (target.startsWith('120') && target.length >= 18)) {
            let cleanGroup = target.replace(/[^0-9\-]/g, '');
            jid = `${cleanGroup}@g.us`;
        } else {
            let cleaned = target.replace(/[^0-9]/g, '');
            jid = `${cleaned}@s.whatsapp.net`;
        }

        console.log(`[Baileys] Sending message via session ${sessionId} to JID: ${jid}`);
        const result = await session.socket.sendMessage(jid, { text: message });

        res.json({
            success: true,
            message: 'Message sent successfully!',
            targetJid: jid,
            messageId: result?.key?.id,
            timestamp: new Date()
        });
    } catch (error) {
        console.error('Error sending message:', error);
        let errMsg = error.message || 'Failed to send message';
        const statusCode = error?.output?.statusCode || error?.status || 500;

        if (errMsg.toLowerCase().includes('forbidden') || statusCode === 403) {
            errMsg = 'Forbidden: This WhatsApp number cannot send to this group. (Either it is not a member of this group, was removed, or the group is set to "Only Admins Can Send Messages"). Try selecting your other connected WhatsApp account.';
        } else if (errMsg.toLowerCase().includes('item-not-found') || statusCode === 404) {
            errMsg = 'WhatsApp recipient or group not found. Please verify the Group JID.';
        } else if (errMsg.toLowerCase().includes('rate-overlimit') || statusCode === 429) {
            errMsg = 'Rate limit reached on WhatsApp. Please wait a moment.';
        }

        res.status(400).json({ error: errMsg, originalError: error.message });
    }
});

// Extract Groups
app.get('/api/groups/:sessionId', async (req, res) => {
    try {
        const { sessionId } = req.params;
        let session = sessions.get(sessionId);

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
                error: 'WhatsApp session is not connected. Please connect the account first.' 
            });
        }

        const groups = await session.socket.groupFetchAllParticipating();
        const groupList = Object.values(groups).map(g => ({
            id: g.id,
            subject: g.subject || 'Unnamed Group',
            owner: g.owner || g.subjectOwner || '',
            creation: g.creation || 0,
            desc: g.desc ? g.desc.toString() : '',
            participantsCount: g.participants ? g.participants.length : 0,
            participants: (g.participants || []).map(p => {
                const phone = p.id ? p.id.split('@')[0].split(':')[0] : '';
                const cached = session.contacts ? session.contacts.get(phone) : null;
                const resolvedName = (cached && cached.name && !cached.name.startsWith('+')) ? cached.name : (p.name || `+${phone}`);
                return {
                    id: p.id,
                    admin: p.admin || null,
                    phone: phone,
                    name: resolvedName
                };
            })
        }));

        res.json({
            success: true,
            totalGroups: groupList.length,
            groups: groupList
        });
    } catch (error) {
        console.error('Error fetching groups:', error);
        res.status(500).json({ error: error.message || 'Failed to extract groups' });
    }
});

// Extract / Get Contacts & Groups
app.get('/api/contacts/:sessionId', async (req, res) => {
    try {
        const { sessionId } = req.params;
        const mode = req.query.mode || 'all'; // 'groups_only' | 'contacts_only' | 'all'
        let session = sessions.get(sessionId);

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
                error: 'WhatsApp session is not connected. Please connect the account first.' 
            });
        }

        const groupList = [];
        const contactMap = new Map();
        const unifiedList = [];

        // If groups requested or mode is all
        if (mode === 'groups_only' || mode === 'all') {
            try {
                const groups = await session.socket.groupFetchAllParticipating();
                for (const group of Object.values(groups)) {
                    const groupObj = {
                        type: 'group',
                        id: group.id,
                        target_jid: group.id,
                        phone: group.id,
                        name: group.subject || 'WhatsApp Group',
                        groupName: group.subject || 'WhatsApp Group',
                        groupId: group.id,
                        participantsCount: group.participants ? group.participants.length : 0
                    };
                    groupList.push(groupObj);
                    unifiedList.push(groupObj);

                    // If NOT groups_only, also extract participant numbers
                    if (mode === 'all') {
                        for (const p of group.participants || []) {
                            if (p.id && !p.id.endsWith('@g.us') && !p.id.endsWith('@broadcast')) {
                                const phone = p.id.split('@')[0].split(':')[0];
                                if (phone && !contactMap.has(phone)) {
                                    contactMap.set(phone, {
                                        type: 'contact',
                                        id: p.id,
                                        target_jid: `${phone}@s.whatsapp.net`,
                                        phone: phone,
                                        name: `+${phone}`,
                                        groupName: group.subject || 'WhatsApp Group',
                                        groupId: group.id
                                    });
                                }
                            }
                        }
                    }
                }
            } catch (e) {
                console.error('Error fetching groups in contacts endpoint:', e);
            }
        }

        if (mode === 'contacts_only' || mode === 'all') {
            const syncedContacts = session.contacts ? Array.from(session.contacts.values()) : [];
            for (const c of syncedContacts) {
                if (!contactMap.has(c.phone)) {
                    contactMap.set(c.phone, {
                        type: 'contact',
                        id: c.id,
                        target_jid: `${c.phone}@s.whatsapp.net`,
                        phone: c.phone,
                        name: c.name || `+${c.phone}`,
                        groupName: 'WhatsApp Contacts',
                        groupId: null
                    });
                }
            }
        }

        if (mode !== 'groups_only') {
            for (const c of contactMap.values()) {
                unifiedList.push(c);
            }
        }

        res.json({
            success: true,
            mode: mode,
            totalItems: unifiedList.length,
            totalGroups: groupList.length,
            totalContacts: contactMap.size,
            items: unifiedList,
            groups: groupList,
            contacts: Array.from(contactMap.values())
        });
    } catch (error) {
        console.error('Error fetching contacts:', error);
        res.status(500).json({ error: error.message || 'Failed to extract contacts' });
    }
});

// Auto-restore saved sessions from disk on server launch
async function restoreSavedSessions() {
    try {
        if (!fs.existsSync(SESSIONS_DIR)) return;
        const dirs = fs.readdirSync(SESSIONS_DIR, { withFileTypes: true });
        for (const dir of dirs) {
            if (dir.isDirectory() && fs.existsSync(path.join(SESSIONS_DIR, dir.name, 'creds.json'))) {
                console.log(`[Baileys] Auto-restoring session: ${dir.name}`);
                initBaileysSession(dir.name).catch(e => console.error(`Error restoring session ${dir.name}:`, e));
            }
        }
    } catch (e) {
        console.error('Error auto-restoring sessions:', e);
    }
}

app.listen(PORT, '127.0.0.1', () => {
    console.log(`Baileys WhatsApp Service running on http://127.0.0.1:${PORT}`);
    restoreSavedSessions();
});
