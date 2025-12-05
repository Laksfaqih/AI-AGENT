#!/bin/bash

# Indonesia AI Agent - API Test Script
# Quick test untuk cek status aplikasi

echo "═══════════════════════════════════════════════════════════"
echo "    🧪 Indonesia AI Agent - API Test"
echo "═══════════════════════════════════════════════════════════"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Test 1: Check if server is running
echo -n "1. Server Status... "
if lsof -i :8123 >/dev/null 2>&1; then
    echo -e "${GREEN}✓ Running${NC}"
else
    echo -e "${RED}✗ Not Running${NC}"
    echo "   Start server: php -S localhost:8123 -t public"
    exit 1
fi

# Test 2: Check API endpoint
echo -n "2. API Endpoint... "
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost:8123/api)
if [ "$HTTP_CODE" == "200" ]; then
    echo -e "${GREEN}✓ OK (${HTTP_CODE})${NC}"
else
    echo -e "${RED}✗ Failed (${HTTP_CODE})${NC}"
fi

# Test 3: Check database
echo -n "3. Database... "
if [ -f "storage/database.sqlite" ]; then
    echo -e "${GREEN}✓ Exists${NC}"
else
    echo -e "${RED}✗ Not Found${NC}"
    echo "   Run: php migrate.php"
fi

# Test 4: Check vendor
echo -n "4. Composer Dependencies... "
if [ -d "vendor" ]; then
    echo -e "${GREEN}✓ Installed${NC}"
else
    echo -e "${RED}✗ Not Installed${NC}"
    echo "   Run: composer install"
fi

# Test 5: Login test
echo -n "5. Login Endpoint... "
LOGIN_RESPONSE=$(curl -s -X POST http://localhost:8123/api/login \
    -H "Content-Type: application/json" \
    -d '{"username":"admin","password":"admin123"}')

if echo "$LOGIN_RESPONSE" | grep -q "success.*true"; then
    echo -e "${GREEN}✓ Working${NC}"
    
    # Extract session token
    SESSION_TOKEN=$(echo "$LOGIN_RESPONSE" | grep -o '"session_token":"[^"]*"' | cut -d'"' -f4)
    
    # Test 6: Status endpoint
    echo -n "6. Auth Status... "
    STATUS_RESPONSE=$(curl -s http://localhost:8123/api/status)
    if echo "$STATUS_RESPONSE" | grep -q "logged_in"; then
        echo -e "${GREEN}✓ OK${NC}"
    else
        echo -e "${YELLOW}⚠ Check Required${NC}"
    fi
    
    # Test 7: Chat endpoint (will likely hit rate limit)
    echo -n "7. Chat Endpoint... "
    CHAT_RESPONSE=$(curl -s -X POST http://localhost:8123/api/chat \
        -H "Content-Type: application/json" \
        -H "X-Session-Token: $SESSION_TOKEN" \
        -d '{"message":"test"}' | head -c 200)
    
    if echo "$CHAT_RESPONSE" | grep -q "429\|quota exceeded\|Too Many Requests"; then
        echo -e "${YELLOW}⚠ Rate Limited (Normal)${NC}"
        echo "   Tunggu 5-10 menit untuk reset"
    elif echo "$CHAT_RESPONSE" | grep -q "success.*true"; then
        echo -e "${GREEN}✓ Working${NC}"
    else
        echo -e "${RED}✗ Error${NC}"
        echo "   Response: $CHAT_RESPONSE"
    fi
else
    echo -e "${RED}✗ Failed${NC}"
fi

echo ""
echo "═══════════════════════════════════════════════════════════"
echo "📊 Test Summary"
echo "═══════════════════════════════════════════════════════════"
echo ""
echo "Server URL: http://localhost:8123/chat.html"
echo "Login: admin / admin123"
echo ""
echo "Jika ada Rate Limit:"
echo "  • Tunggu 5-10 menit"
echo "  • Jalankan: php auth.php"
echo "  • Refresh browser"
echo ""
echo "═══════════════════════════════════════════════════════════"
