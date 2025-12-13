# PowerShell script to start ngrok and automatically update environment URLs
# This script prevents ERR_NGROK_3200 errors by ensuring URLs are always current

param(
    [int]$Port = 8000,
    [string]$EnvFile = ".env"
)

Write-Host "Starting ngrok and updating environment URLs..." -ForegroundColor Green

# Function to update environment file
function Update-EnvFile {
    param(
        [string]$FilePath,
        [string]$NgrokUrl
    )
    
    if (Test-Path $FilePath) {
        Write-Host "Updating $FilePath..." -ForegroundColor Yellow
        
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
        
        Write-Host "   Updated APP_URL" -ForegroundColor Green
        Write-Host "   Updated DIDIT_CALLBACK_URL" -ForegroundColor Green
        Write-Host "   Updated DIDIT_REDIRECT_URL" -ForegroundColor Green
        Write-Host "   Updated GOOGLE_REDIRECT_URI" -ForegroundColor Green
        Write-Host "   Saved changes to $FilePath" -ForegroundColor Cyan
    } else {
        Write-Host "   File $FilePath not found" -ForegroundColor Red
    }
}

# Check if Laravel is running
Write-Host "Checking if Laravel is running on port $Port..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost:$Port" -UseBasicParsing -TimeoutSec 5 -ErrorAction Stop
    Write-Host "   Laravel is running on port $Port" -ForegroundColor Green
} catch {
    Write-Host "   Laravel doesn't seem to be running on port $Port" -ForegroundColor Red
    Write-Host "   Make sure to run 'php artisan serve' first" -ForegroundColor Yellow
}

# Kill any existing ngrok processes
Write-Host "Stopping any existing ngrok processes..." -ForegroundColor Yellow
Get-Process -Name "ngrok" -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 2

# Start ngrok in background
Write-Host "Starting ngrok tunnel on port $Port..." -ForegroundColor Yellow

# Try different ways to find and start ngrok
$ngrokPath = $null
$ngrokPaths = @(
    ".\ngrok.exe",
    "ngrok",
    "ngrok.exe",
    "C:\Users\$env:USERNAME\AppData\Local\ngrok\ngrok.exe",
    "C:\Program Files\ngrok\ngrok.exe",
    "C:\ngrok\ngrok.exe"
)

foreach ($path in $ngrokPaths) {
    try {
        $testResult = Get-Command $path -ErrorAction SilentlyContinue
        if ($testResult) {
            $ngrokPath = $path
            break
        }
    } catch {
        continue
    }
}

if (-not $ngrokPath) {
    Write-Host "ngrok not found. Please install ngrok and make sure it's in your PATH." -ForegroundColor Red
    Write-Host "   Download from: https://ngrok.com/download" -ForegroundColor Yellow
    exit 1
}

Write-Host "   Using ngrok at: $ngrokPath" -ForegroundColor Cyan

try {
    $ngrokProcess = Start-Process -FilePath $ngrokPath -ArgumentList "http", $Port -WindowStyle Hidden -PassThru
    if (-not $ngrokProcess) {
        throw "Failed to start ngrok process"
    }
} catch {
    Write-Host "Failed to start ngrok: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Wait for ngrok to start
Write-Host "Waiting for ngrok to initialize..." -ForegroundColor Yellow
Start-Sleep -Seconds 5

# Get ngrok URL from API with retry logic
Write-Host "Retrieving ngrok URL..." -ForegroundColor Yellow
$httpsUrl = $null
$maxRetries = 12
$retryCount = 0

while (-not $httpsUrl -and $retryCount -lt $maxRetries) {
    try {
        $apiResponse = Invoke-RestMethod -Uri "http://localhost:4040/api/tunnels" -Method Get -ErrorAction Stop
        $httpsUrl = $apiResponse.tunnels | Where-Object { $_.proto -eq "https" } | Select-Object -First 1 -ExpandProperty public_url
        
        if (-not $httpsUrl) {
            Write-Host "   Waiting for tunnel to be ready... (attempt $($retryCount + 1)/$maxRetries)" -ForegroundColor Yellow
            Start-Sleep -Seconds 2
            $retryCount++
        }
    } catch {
        Write-Host "   API not ready yet... (attempt $($retryCount + 1)/$maxRetries)" -ForegroundColor Yellow
        Start-Sleep -Seconds 2
        $retryCount++
    }
}

try {
    
    if ($httpsUrl) {
        Write-Host "ngrok tunnel established!" -ForegroundColor Green
        Write-Host "   Public URL: $httpsUrl" -ForegroundColor Cyan
        
        # Update environment files
        Update-EnvFile -FilePath $EnvFile -NgrokUrl $httpsUrl
        
        # Also update .env.local if it exists
        if (Test-Path ".env.local") {
            Update-EnvFile -FilePath ".env.local" -NgrokUrl $httpsUrl
        }
        
        # Clear Laravel config cache
        Write-Host "Clearing Laravel configuration cache..." -ForegroundColor Yellow
        try {
            $artisanResult = & php artisan config:clear 2>&1
            Write-Host "   Configuration cache cleared" -ForegroundColor Green
        } catch {
            Write-Host "   Could not clear config cache (Laravel might not be running)" -ForegroundColor Red
        }
        
        Write-Host ""
        Write-Host "Setup Complete!" -ForegroundColor Green
        Write-Host "   Your app is now accessible at: $httpsUrl" -ForegroundColor Cyan
        Write-Host "   ngrok web interface: http://localhost:4040" -ForegroundColor Cyan
        Write-Host "   All environment URLs have been updated automatically" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "Tips:" -ForegroundColor Yellow
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
            Write-Host "Stopping ngrok..." -ForegroundColor Red
            Get-Process -Name "ngrok" -ErrorAction SilentlyContinue | Stop-Process -Force
        }
        
    } else {
        throw "No HTTPS tunnel found in response"
    }
} catch {
    Write-Host "Could not retrieve ngrok URL: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "   Make sure ngrok is properly installed and authenticated" -ForegroundColor Yellow
    Get-Process -Name "ngrok" -ErrorAction SilentlyContinue | Stop-Process -Force
    exit 1
}
