@echo off
echo Starting ngrok and updating environment URLs...
powershell -ExecutionPolicy Bypass -File start-ngrok.ps1
pause