<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hiring Poster - {{ $poster->job_title }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #1f2937; }
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

    <div style="width: 432px; min-height: 540px; background: #1f2937; margin: 0 auto; border: 4px solid #374151;">
        <!-- Header Section -->
        <div style="padding: 35px 20px 20px 20px; text-align: center;">
            <div style="display: inline-block; background: #fbbf24; color: #000; padding: 12px 35px; font-size: 24px; font-weight: bold;">WE'RE</div>
            <br>
            <div style="display: inline-block; background: #374151; color: #fff; padding: 12px 35px; font-size: 24px; font-weight: bold; margin-top: 12px;">HIRING</div>
        </div>

        <!-- Person Icon -->
        <div style="text-align: right; padding-right: 35px; padding-bottom: 20px;">
            <div style="display: inline-block; width: 24px; height: 24px; background: #fbbf24; border-radius: 50%;"></div>
            <br>
            <div style="display: inline-block; width: 32px; height: 44px; background: #fbbf24; border-radius: 16px 16px 6px 6px; margin-top: 4px;"></div>
        </div>

        <!-- Content Section - White Card -->
        <div style="margin: 0 20px; background: #ffffff; padding: 25px;">
            <p style="font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 6px 0;">Position</p>
            <p style="font-size: 20px; font-weight: bold; color: #111827; margin: 0 0 20px 0; line-height: 1.3;">{{ Str::limit($poster->job_title, 35) }}</p>

            <div style="display: inline-block; background: #fbbf24; color: #000; padding: 6px 14px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">Requirements</div>
            <div style="font-size: 12px; color: #374151; line-height: 1.9; margin: 0;">
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
        <div style="padding: 25px;">
            <div style="display: inline-block; width: 36px; height: 36px; background: #374151; border-radius: 6px; color: #fff; font-size: 12px; font-weight: bold; text-align: center; line-height: 36px; vertical-align: middle;">{{ strtoupper(substr($poster->company_name, 0, 2)) }}</div>
            <span style="font-size: 14px; font-weight: bold; color: #fff; margin-left: 10px; vertical-align: middle;">{{ Str::limit($poster->company_name, 18) }}</span>
            <div style="float: right;">
                <span style="display: inline-block; background: #fbbf24; color: #000; padding: 12px 22px; font-size: 12px; font-weight: bold; border-radius: 20px; text-transform: uppercase;">Apply Now</span>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
</body>
</html>
