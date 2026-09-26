#!/bin/bash
export PATH="/opt/alt/alt-nodejs20/root/usr/bin:$PATH"
echo "=== Node & NPM Version ==="
node -v
npm -v

echo "=== Installing Baileys Service Dependencies ==="
cd /home/u858498424/domains/iamraheem.com/public_html/baileys-service
npm install --ignore-scripts
npm install pm2 --ignore-scripts

echo "=== Testing PM2 ==="
./node_modules/.bin/pm2 -v

echo "=== Starting Baileys Service via PM2 ==="
./node_modules/.bin/pm2 delete baileys-whatsapp 2>/dev/null || true
./node_modules/.bin/pm2 start server.js --name "baileys-whatsapp" --max-memory-restart 500M
./node_modules/.bin/pm2 save

echo "=== Checking PM2 Status ==="
./node_modules/.bin/pm2 status

echo "=== Testing HTTP Port 3000 ==="
sleep 2
curl -s http://127.0.0.1:3000/health || curl -s http://localhost:3000/health || true
echo ""
