# Simple ngrok starter script
param(
    [int]$Port = 8000
)

Write-Host "Starting ngrok tunnel on port $Port..." -ForegroundColor Green

# Kill any existing ngrok processes
Get-Process -Name "ngrok" -ErrorAction SilentlyContinue | Stop-Process -Force
Start-Sleep -Seconds 2

# Start ngrok and capture output
Write-Host "Starting ngrok process..." -ForegroundColor Yellow
$ngrokProcess = Start-Process -FilePath ".\ngrok.exe" -ArgumentList "http", $Port -NoNewWindow -PassThru

if ($ngrokProcess) {
    Write-Host "ngrok process started with PID: $($ngrokProcess.Id)" -ForegroundColor Green
    
    # Wait a bit for ngrok to initialize
    Write-Host "Waiting for ngrok to initialize..." -ForegroundColor Yellow
    Start-Sleep -Seconds 8
    
    # Try to get the tunnel URL
    Write-Host "Checking for tunnel URL..." -ForegroundColor Yellow
    
    for ($i = 1; $i -le 10; $i++) {
        try {
            $response = Invoke-RestMethod -Uri "http://localhost:4040/api/tunnels" -Method Get -TimeoutSec 5
            
            if ($response.tunnels -and $response.tunnels.Count -gt 0) {
                $httpsUrl = $response.tunnels | Where-Object { $_.proto -eq "https" } | Select-Object -First 1 -ExpandProperty public_url
                
                if ($httpsUrl) {
                    Write-Host "Success! ngrok tunnel is running" -ForegroundColor Green
                    Write-Host "Public URL: $httpsUrl" -ForegroundColor Cyan
                    Write-Host "Web Interface: http://localhost:4040" -ForegroundColor Cyan
                    
                    # Try to open the web interface
                    try {
                        Start-Process "http://localhost:4040"
                    } catch {
                        # Ignore if cannot open browser
                    }
                    
                    Write-Host ""
                    Write-Host "Your Laravel app is now accessible at: $httpsUrl" -ForegroundColor Green
                    Write-Host "Keep this window open to maintain the tunnel" -ForegroundColor Yellow
                    Write-Host "Press Ctrl+C to stop" -ForegroundColor Yellow
                    
                    # Keep running
                    try {
                        while ($true) {
                            Start-Sleep -Seconds 1
                        }
                    } finally {
                        Write-Host ""
                        Write-Host "Stopping ngrok..." -ForegroundColor Red
                        Get-Process -Name "ngrok" -ErrorAction SilentlyContinue | Stop-Process -Force
                    }
                    
                    return
                }
            }
            
            Write-Host "   Attempt $i/10: Waiting for tunnel..." -ForegroundColor Yellow
            Start-Sleep -Seconds 2
            
        } catch {
            Write-Host "   Attempt $i/10: API not ready yet..." -ForegroundColor Yellow
            Start-Sleep -Seconds 2
        }
    }
    
    Write-Host "Could not retrieve tunnel URL after 10 attempts" -ForegroundColor Red
    Write-Host "Try these troubleshooting steps:" -ForegroundColor Yellow
    Write-Host "   1. Make sure you are authenticated with ngrok" -ForegroundColor White
    Write-Host "   2. Check if port $Port is available" -ForegroundColor White
    Write-Host "   3. Visit http://localhost:4040 to see ngrok status" -ForegroundColor White
    
} else {
    Write-Host "Failed to start ngrok process" -ForegroundColor Red
}

# Cleanup
Get-Process -Name "ngrok" -ErrorAction SilentlyContinue | Stop-Process -Force