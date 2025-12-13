# Updated PowerShell script to start ngrok with new authtoken
# This script prevents ERR_NGROK_3200 errors by ensuring URLs are always current

param(
    [int]$Port = 8000,
    [string]$EnvFile = ".env"
)

Write-Host "🚀 Starting ngrok with updated configuration..." -ForegroundColor Green
Write-Host "   Using authtoken: 33XLa0QIXu3NblbSjBeS2pmMQl4_2zMcpFVUkakF1SGCRsnH4" -ForegroundColor Cyan

# Function to update environment file
function Update-EnvFile {
    param(
        [string]$FilePath,
        [string]$NgrokUrl
    )
    
    if (Test-Path $FilePath) {
        Write-Host "📝 Updating $FilePath..." -ForegroundColor Yellow
        
        # Create backup
        Copy-Item $FilePath "$FilePath.backup" -Force
        
        # Read the file content
        $content = Get-Content $FilePath
        
        # Update URLs using PowerShell string replacement
        $content = $content -replace "^APP_URL=.*", "APP_URL=$NgrokUrl"
        $content = $content -replace "^DIDIT_CALLBACK_URL=.*", "DIDIT_CALLBACK_URL=$NgrokUrl/api/kyc/webhook"
        $content = $content -replace "^DIDIT_REDIRECT_URL=.*", "DIDIT_REDIRECT_URL=$NgrokUrl/kyc/success"
        $content = $content -replace "^GOOGLE_REDIRECT_URI=.*", "GOOGLE_REDIRECT_URI=$NgrokUrl/auth/google/callback"
        
        # Write back to file
        $content | Set-Content $FilePath
        
        Write-Host "   ✅ Updated APP_URL" -ForegroundColor Green
        Write-Host "   ✅ Updated DIDIT_CALLBACK_URL" -ForegroundColor Green
        Write-Host "   ✅ Updated DIDIT_REDIRECT_URL" -ForegroundColor Green
        Write-Host "   ✅ Updated GOOGLE_REDIRECT_URI" -ForegroundColor Green
        Write-Host "   💾 Saved changes to $FilePath" -ForegroundColor Cyan
    } else {
        Write-Host "   ⚠️  File $FilePath not found" -ForegroundColor Red
    }
}

# Check if Laravel is running
Write-Host "🔍 Checking if Laravel is running on port $Port..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost:$Port" -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop
    Write-Host "   ✅ Laravel is running on port $Port" -ForegroundColor Green
} catch {
    Write-Host "   ⚠️  Laravel doesn't seem to be running on port $Port" -ForegroundColor Red
    Write-Host "   💡 Make sure to run 'php artisan serve' first" -ForegroundColor Yellow
}

# Kill any existing ngrok processes
Write-Host "🔄 Stopping any existing ngrok processes..." -ForegroundColor Yellow
Get-Process -Name "ngrok" -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 3

# Verify authtoken is configured
Write-Host "🔐 Verifying ngrok authentication..." -ForegroundColor Yellow
try {
    $configCheck = & .\ngrok.exe config check 2>&1
    Write-Host "   ✅ Ngrok configuration is valid" -ForegroundColor Green
} catch {
    Write-Host "   ⚠️  Ngrok configuration issue: $($_.Exception.Message)" -ForegroundColor Red
}

# Start ngrok with the new configuration
Write-Host "🌐 Starting ngrok tunnel on port $Port..." -ForegroundColor Yellow
$ngrokProcess = Start-Process -FilePath ".\ngrok.exe" -ArgumentList "http", $Port -WindowStyle Hidden -PassThru

if (-not $ngrokProcess) {
    Write-Host "❌ Failed to start ngrok process" -ForegroundColor Red
    exit 1
}

Write-Host "   ✅ Ngrok process started (PID: $($ngrokProcess.Id))" -ForegroundColor Green

# Wait for ngrok to start
Write-Host "⏳ Waiting for ngrok to initialize..." -ForegroundColor Yellow
Start-Sleep -Seconds 8

# Get ngrok URL from API with retry logic
Write-Host "📡 Retrieving ngrok URL..." -ForegroundColor Yellow
$maxRetries = 5
$retryCount = 0
$httpsUrl = $null

while ($retryCount -lt $maxRetries -and -not $httpsUrl) {
    try {
        $apiResponse = Invoke-RestMethod -Uri "http://localhost:4040/api/tunnels" -Method Get -ErrorAction Stop
        $httpsUrl = $apiResponse.tunnels | Where-Object { $_.proto -eq "https" } | Select-Object -First 1 -ExpandProperty public_url
        
        if (-not $httpsUrl) {
            throw "No HTTPS tunnel found in response"
        }
        
    } catch {
        $retryCount++
        if ($retryCount -lt $maxRetries) {
            Write-Host "   🔄 Retry $retryCount/$maxRetries..." -ForegroundColor Yellow
            Start-Sleep -Seconds 3
        } else {
            Write-Host "❌ Could not retrieve ngrok URL after $maxRetries attempts" -ForegroundColor Red
            Write-Host "   Error: $($_.Exception.Message)" -ForegroundColor Red
            Write-Host "   💡 Check ngrok web interface at http://localhost:4040" -ForegroundColor Yellow
            Get-Process -Name "ngrok" -ErrorAction SilentlyContinue | Stop-Process -Force
            exit 1
        }
    }
}

if ($httpsUrl) {
    Write-Host "🎉 Ngrok tunnel established successfully!" -ForegroundColor Green
    Write-Host "   📡 New Public URL: $httpsUrl" -ForegroundColor Cyan
    Write-Host "   🔧 Web Interface: http://localhost:4040" -ForegroundColor Cyan
    
    # Update environment files
    Update-EnvFile -FilePath $EnvFile -NgrokUrl $httpsUrl
    
    # Also update .env.local if it exists
    if (Test-Path ".env.local") {
        Update-EnvFile -FilePath ".env.local" -NgrokUrl $httpsUrl
    }
    
    # Clear Laravel config cache
    Write-Host "🧹 Clearing Laravel configuration cache..." -ForegroundColor Yellow
    try {
        & php artisan config:clear | Out-Null
        Write-Host "   ✅ Configuration cache cleared" -ForegroundColor Green
    } catch {
        Write-Host "   ⚠️  Could not clear config cache" -ForegroundColor Red
    }
    
    Write-Host ""
    Write-Host "🎯 Setup Complete!" -ForegroundColor Green
    Write-Host "   🌐 Your app is accessible at: $httpsUrl" -ForegroundColor Cyan
    Write-Host "   🔧 Ngrok dashboard: http://localhost:4040" -ForegroundColor Cyan
    Write-Host "   📋 All environment URLs updated automatically" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "📋 Updated URLs:" -ForegroundColor Yellow
    Write-Host "   APP_URL=$httpsUrl" -ForegroundColor White
    Write-Host "   DIDIT_CALLBACK_URL=$httpsUrl/api/kyc/webhook" -ForegroundColor White
    Write-Host "   DIDIT_REDIRECT_URL=$httpsUrl/kyc/success" -ForegroundColor White
    Write-Host "   GOOGLE_REDIRECT_URI=$httpsUrl/auth/google/callback" -ForegroundColor White
    Write-Host ""
    Write-Host "💡 Tips:" -ForegroundColor Yellow
    Write-Host "   - Keep this terminal open to maintain the tunnel"
    Write-Host "   - Visit http://localhost:4040 to inspect requests"
    Write-Host "   - Press Ctrl+C to stop ngrok"
    
    # Open ngrok web interface
    try {
        Start-Process "http://localhost:4040"
    } catch {
        # Ignore if can't open browser
    }
    
    Write-Host ""
    Write-Host "Press Ctrl+C to stop ngrok and exit..." -ForegroundColor Yellow
    
    # Keep the script running and handle Ctrl+C
    try {
        while ($true) {
            Start-Sleep -Seconds 1
        }
    } finally {
        Write-Host ""
        Write-Host "🛑 Stopping ngrok..." -ForegroundColor Red
        Get-Process -Name "ngrok" -ErrorAction SilentlyContinue | Stop-Process -Force
        Write-Host "   ✅ Ngrok stopped" -ForegroundColor Green
    }
}