Write-Host "Testing ngrok installation..." -ForegroundColor Green

# Test if ngrok is accessible
try {
    $process = Start-Process -FilePath "ngrok" -ArgumentList "version" -Wait -PassThru -WindowStyle Hidden -RedirectStandardOutput "ngrok_version.txt"
    if (Test-Path "ngrok_version.txt") {
        $version = Get-Content "ngrok_version.txt"
        Write-Host "ngrok version: $version" -ForegroundColor Cyan
        Remove-Item "ngrok_version.txt" -ErrorAction SilentlyContinue
    }
} catch {
    Write-Host "Error testing ngrok: $($_.Exception.Message)" -ForegroundColor Red
}

# Try to start ngrok for a quick test
Write-Host "Starting ngrok on port 8000..." -ForegroundColor Yellow
$ngrokProcess = Start-Process -FilePath "ngrok" -ArgumentList "http", "8000" -PassThru
Write-Host "ngrok process started with ID: $($ngrokProcess.Id)" -ForegroundColor Green

Start-Sleep -Seconds 5

try {
    $apiResponse = Invoke-RestMethod -Uri "http://localhost:4040/api/tunnels" -Method Get -ErrorAction Stop
    $tunnels = $apiResponse.tunnels
    Write-Host "Found $($tunnels.Count) tunnels" -ForegroundColor Green
    foreach ($tunnel in $tunnels) {
        Write-Host "  - $($tunnel.proto): $($tunnel.public_url)" -ForegroundColor Cyan
    }
} catch {
    Write-Host "Error connecting to ngrok API: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "Stopping ngrok..." -ForegroundColor Yellow
Stop-Process -Id $ngrokProcess.Id -Force -ErrorAction SilentlyContinue
