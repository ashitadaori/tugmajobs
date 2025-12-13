<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hiring Poster - {{ $poster->job_title }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #2563eb; }
        body { font-family: Helvetica, Arial, sans-serif; }
        .no-print { position: fixed; top: 20px; right: 20px; z-index: 1000; }
        .no-print a, .no-print button { padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; border: none; cursor: pointer; font-size: 14px; margin-left: 10px; display: inline-block; }
        .btn-back { background: #6c757d; color: white; }
        .btn-edit { background: #0d6efd; color: white; }
        .btn-download { background: #198754; color: white; }
        .btn-print { background: white; color: #333; border: 1px solid #333 !important; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>
    @if(!isset($isPdf) || !$isPdf)
    <div class="no-print">
        <a href="{{ route('admin.posters.index') }}" class="btn-back">Back</a>
        <a href="{{ route('admin.posters.edit', $poster->id) }}" class="btn-edit">Edit</a>
        <a href="{{ route('admin.posters.download', $poster->id) }}" class="btn-download">Download PDF</a>
        <button onclick="window.print()" class="btn-print">Print</button>
    </div>
    @endif

    <div style="width: 432px; min-height: 540px; background: #2563eb; margin: 0 auto; border: 4px solid #1d4ed8;">
        <!-- Header Section -->
        <div style="padding: 35px 20px 20px 20px; text-align: center;">
            <div style="display: inline-block; background: #fbbf24; color: #000; padding: 10px 30px; font-size: 20px; font-weight: bold;">WE ARE</div>
            <div style="font-size: 65px; font-weight: bold; color: #fff; letter-spacing: -2px; line-height: 1; margin-top: 10px;">HIRING</div>
        </div>

        <!-- Sound Waves and Megaphone -->
        <div style="text-align: right; padding: 10px 30px 25px 30px;">
            <!-- Sound waves -->
            <div style="display: inline-block; vertical-align: middle; margin-right: 12px;">
                <div style="width: 40px; height: 4px; background: #fbbf24; margin: 6px 0 6px auto;"></div>
                <div style="width: 55px; height: 4px; background: #fbbf24; margin: 6px 0 6px auto;"></div>
                <div style="width: 40px; height: 4px; background: #fbbf24; margin: 6px 0 6px auto;"></div>
            </div>
            <!-- Megaphone -->
            <div style="display: inline-block; vertical-align: middle;">
                <div style="width: 0; height: 0; border-left: 50px solid #d1d5db; border-top: 25px solid transparent; border-bottom: 25px solid transparent; display: inline-block;"></div>
                <div style="width: 16px; height: 32px; background: #9ca3af; display: inline-block; vertical-align: middle; margin-left: -5px;"></div>
            </div>
        </div>

        <!-- Title Section -->
        <div style="padding: 20px 30px 12px 30px;">
            <p style="font-size: 16px; font-weight: bold; color: #fff; margin: 0 0 10px 0;">Title:</p>
            <p style="font-size: 22px; font-weight: bold; color: #fff; margin: 0; line-height: 1.3;">{{ Str::limit($poster->job_title, 40) }}</p>
        </div>

        <!-- Requirements Section -->
        <div style="padding: 20px 30px;">
            <div style="display: inline-block; background: #fbbf24; color: #000; padding: 7px 16px; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px;">Requirement:</div>
            <div style="font-size: 13px; color: #fff; line-height: 1.9; margin: 0;">
                @php
                    $requirements = preg_split('/[\r\n]+/', $poster->requirements);
                    $requirements = array_filter(array_map('trim', $requirements));
                    $requirements = array_slice($requirements, 0, 5);
                @endphp
                @foreach($requirements as $req)
                    @if(!empty($req))
                    <div style="margin-bottom: 5px;">
                        <span style="color: #fbbf24; margin-right: 8px;">&#8226;</span>{{ Str::limit($req, 40) }}
                    </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Footer Section -->
        <div style="padding: 25px 30px 35px 30px;">
            <span style="font-size: 16px; font-weight: bold; color: #fff;">{{ Str::limit($poster->company_name, 22) }}</span>
            <div style="float: right;">
                <span style="display: inline-block; background: #fbbf24; color: #000; padding: 12px 24px; font-size: 12px; font-weight: bold; border-radius: 20px; text-transform: uppercase;">Apply Now</span>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
</body>
</html>
