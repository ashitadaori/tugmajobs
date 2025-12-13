<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(isset($userId))
    <meta name="user-id" content="{{ $userId }}">
    @endif
    @if(isset($sessionId))
    <meta name="kyc-session-id" content="{{ $sessionId }}">
    @endif
    <title>Verification Complete - {{ config('app.name') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .success-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .success-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px 30px;
            text-align: center;
            max-width: 400px;
            width: 100%;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: pulse 2s infinite;
        }
        
        .success-icon i {
            color: white;
            font-size: 40px;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .btn-custom {
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom {
            background: #667eea;
            color: white;
            border: none;
        }
        
        .btn-primary-custom:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }
        
        .btn-secondary-custom {
            background: #f7fafc;
            color: #4a5568;
            border: 2px solid #e2e8f0;
        }
        
        .btn-secondary-custom:hover {
            background: #edf2f7;
            transform: translateY(-2px);
        }
        
        .instruction-box {
            background: #f0f9ff;
            border: 2px solid #0ea5e9;
            border-radius: 15px;
            padding: 20px;
            margin: 30px 0;
        }
        
        .instruction-box h6 {
            color: #0369a1;
            margin-bottom: 10px;
        }
        
        .instruction-box p {
            color: #0c4a6e;
            margin: 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <h2 class="text-success mb-3">Verification Complete!</h2>
            <p class="lead mb-4">Your identity has been successfully verified.</p>
            
            <div class="instruction-box">
                <h6><i class="fas fa-desktop me-2"></i>Next Steps</h6>
                <p>Return to your computer browser where you started the verification. The page will automatically update and redirect you to your dashboard.</p>
            </div>
            
            <div class="d-flex flex-column align-items-center">
                <button onclick="window.close()" class="btn-custom btn-secondary-custom">
                    <i class="fas fa-times me-2"></i>Close This Window
                </button>
                <a href="/" class="btn-custom btn-primary-custom">
                    <i class="fas fa-home me-2"></i>Go to Homepage
                </a>
            </div>
            
            <div class="mt-4">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    You can safely close this window and return to your computer.
                </small>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Cross-device handler -->
    <script src="{{ asset('assets/js/kyc-cross-device-handler.js') }}"></script>
    
    <script>
        // Auto-close after 10 seconds if possible
        setTimeout(function() {
            try {
                window.close();
            } catch (e) {
                console.log('Cannot auto-close window');
            }
        }, 10000);
        
        // Try to notify parent window if in iframe
        try {
            if (window.parent && window.parent !== window) {
                window.parent.postMessage({
                    type: 'kyc_verification_complete',
                    sessionId: '{{ $sessionId ?? "" }}',
                    userId: '{{ $userId ?? "" }}'
                }, '*');
            }
        } catch (e) {
            console.log('Cannot communicate with parent window');
        }
    </script>
</body>
</html>