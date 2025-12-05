#!/bin/bash

echo "=== AI DATABASE QUERY DEBUG ==="
echo ""
echo "1. Testing database connection..."
echo ""

# Check if database exists
mysql -u root -p -e "SHOW DATABASES;" 2>&1 | grep -v "mysql\|information_schema\|performance_schema\|sys"

echo ""
echo "2. Which database should we use for toko_madura?"
echo "   (The one that contains your product/sales data)"
echo ""
echo "3. Check /tmp/query-debug.txt for AI query attempts:"
tail -20 /tmp/query-debug.txt 2>/dev/null || echo "No debug log yet"

echo ""
echo "4. To reconfigure database connection:"
echo "   a) Open your AI app in browser"
echo "   b) Click Database (🗄️) menu"
echo "   c) Edit 'toko_madura' connection"
echo "   d) Change Database Name to the correct one"
echo "   e) Test & Save"
echo ""
