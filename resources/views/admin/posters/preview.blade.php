<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hiring Poster - {{ $poster->job_title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none !important; }
            .poster-container { box-shadow: none !important; }
        }
        body {
            background: #f5f5f5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }
        .poster-container {
            width: 210mm;
            min-height: 297mm;
            background: white;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .action-bar {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>
<body>
    <div class="action-bar no-print">
        <a href="{{ route('admin.posters.index') }}" class="btn btn-secondary me-2">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        <a href="{{ route('admin.posters.edit', $poster->id) }}" class="btn btn-primary me-2">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <a href="{{ route('admin.posters.download', $poster->id) }}" class="btn btn-success me-2">
            <i class="bi bi-download me-1"></i>Download PDF
        </a>
        <button onclick="window.print()" class="btn btn-outline-dark">
            <i class="bi bi-printer me-1"></i>Print
        </button>
    </div>

    <div class="poster-container">
        <div style="background: #3B5998; min-height: 297mm; padding: 40px; color: white;">
            <!-- Header -->
            <div style="margin-bottom: 20px;">
                <div style="background: #F59E0B; color: #000; padding: 10px 25px; font-weight: bold; font-size: 1.5rem; display: inline-block; transform: rotate(-3deg);">
                    WE ARE
                </div>
            </div>

            <!-- HIRING Text -->
            <div style="font-size: 5rem; font-weight: 900; line-height: 1; letter-spacing: -3px; margin-bottom: 30px;">
                HIRING
            </div>

            <!-- Job Title -->
            <div style="margin-bottom: 30px;">
                <div style="font-size: 1.2rem; opacity: 0.8;">Title:</div>
                <div style="font-size: 2rem; font-weight: bold;">{{ $poster->job_title }}</div>
            </div>

            <!-- Requirements -->
            <div style="margin-bottom: 30px;">
                <div style="background: #F59E0B; color: #000; padding: 10px 20px; font-weight: bold; display: inline-block; margin-bottom: 15px;">
                    REQUIREMENT:
                </div>
                <div style="font-size: 1.1rem; line-height: 1.8; white-space: pre-line;">{{ $poster->requirements }}</div>
            </div>

            <!-- Company -->
            <div style="margin-top: auto; padding-top: 30px; border-top: 2px solid rgba(255,255,255,0.3);">
                <div style="font-size: 1.5rem; font-weight: bold;">{{ $poster->company_name }}</div>
            </div>
        </div>
    </div>
</body>
</html>
