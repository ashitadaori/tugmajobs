#!/bin/bash

# Shell script to start ngrok and automatically update environment URLs
# This script prevents ERR_NGROK_3200 errors by ensuring URLs are always current

PORT=${1:-8000}
ENV_FILE=${2:-.env}

echo "🚀 Starting ngrok and updating environment URLs..."

# Function to update environment file
update_env_file() {
    local file_path=$1
    local ngrok_url=$2
    
    if [ -f "$file_path" ]; then
        echo "📝 Updating $file_path..."
        
        # Create backup
        cp "$file_path" "$file_path.backup"
        
        # Update URLs using sed
        sed -i.tmp "s|^APP_URL=.*|APP_URL=$ngrok_url|" "$file_path"
        sed -i.tmp "s|^DIDIT_CALLBACK_URL=.*|DIDIT_CALLBACK_URL=$ngrok_url/api/kyc/webhook|" "$file_path"
        sed -i.tmp "s|^DIDIT_REDIRECT_URL=.*|DIDIT_REDIRECT_URL=$ngrok_url/kyc/success|" "$file_path"
        sed -i.tmp "s|^GOOGLE_REDIRECT_URI=.*|GOOGLE_REDIRECT_URI=$ngrok_url/auth/google/callback|" "$file_path"
        
        # Remove temporary file
        rm -f "$file_path.tmp"
        
        echo "   ✅ Updated APP_URL"
        echo "   ✅ Updated DIDIT_CALLBACK_URL"
        echo "   ✅ Updated DIDIT_REDIRECT_URL"
        echo "   ✅ Updated GOOGLE_REDIRECT_URI"
        echo "   💾 Saved changes to $file_path"
    else
        echo "   ⚠️  File $file_path not found"
    fi
}

# Check if Laravel is running
echo "🔍 Checking if Laravel is running on port $PORT..."
if curl -s "http://localhost:$PORT" > /dev/null 2>&1; then
    echo "   ✅ Laravel is running on port $PORT"
else
    echo "   ⚠️  Laravel doesn't seem to be running on port $PORT"
    echo "   💡 Make sure to run 'php artisan serve' first"
fi

# Kill any existing ngrok processes
echo "🔄 Stopping any existing ngrok processes..."
pkill -f ngrok 2>/dev/null || true
sleep 2

# Start ngrok in background
echo "🌐 Starting ngrok tunnel on port $PORT..."
ngrok http $PORT > /dev/null 2>&1 &
NGROK_PID=$!

# Wait for ngrok to start
echo "⏳ Waiting for ngrok to initialize..."
sleep 5

# Get ngrok URL from API
echo "📡 Retrieving ngrok URL..."
NGROK_URL=$(curl -s http://localhost:4040/api/tunnels | python3 -c "
import sys, json
try:
    data = json.load(sys.stdin)
    for tunnel in data['tunnels']:
        if tunnel['proto'] == 'https':
            print(tunnel['public_url'])
            break
except:
    pass
" 2>/dev/null)

if [ -n "$NGROK_URL" ]; then
    echo "🎉 ngrok tunnel established!"
    echo "   📡 Public URL: $NGROK_URL"
    
    # Update environment files
    update_env_file "$ENV_FILE" "$NGROK_URL"
    
    # Also update .env.local if it exists
    if [ -f ".env.local" ]; then
        update_env_file ".env.local" "$NGROK_URL"
    fi
    
    # Clear Laravel config cache
    echo "🧹 Clearing Laravel configuration cache..."
    if php artisan config:clear 2>/dev/null; then
        echo "   ✅ Configuration cache cleared"
    else
        echo "   ⚠️  Could not clear config cache (Laravel might not be running)"
    fi
    
    echo ""
    echo "🎯 Setup Complete!"
    echo "   🌐 Your app is now accessible at: $NGROK_URL"
    echo "   🔧 ngrok web interface: http://localhost:4040"
    echo "   📋 All environment URLs have been updated automatically"
    echo ""
    echo "💡 Tips:"
    echo "   - Keep this terminal open to maintain the tunnel"
    echo "   - Visit http://localhost:4040 to inspect requests"
    echo "   - Press Ctrl+C to stop ngrok"
    
    # Open ngrok web interface (if possible)
    if command -v open > /dev/null 2>&1; then
        open http://localhost:4040
    elif command -v xdg-open > /dev/null 2>&1; then
        xdg-open http://localhost:4040 2>/dev/null &
    fi
    
    # Keep the script running and handle Ctrl+C
    echo ""
    echo "Press Ctrl+C to stop ngrok and exit..."
    
    # Trap Ctrl+C
    trap 'echo ""; echo "🛑 Stopping ngrok..."; pkill -f ngrok; exit 0' INT
    
    # Keep running
    while true; do
        sleep 1
    done
else
    echo "❌ Could not retrieve ngrok URL"
    echo "   💡 Make sure ngrok is properly installed and authenticated"
    pkill -f ngrok 2>/dev/null || true
    exit 1
fi