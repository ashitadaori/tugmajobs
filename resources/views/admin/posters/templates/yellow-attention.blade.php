<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hiring Poster - {{ $poster->job_title }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #fbbf24; }
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

    <div style="width: 432px; min-height: 540px; background: #fbbf24; margin: 0 auto; border: 4px solid #d97706;">
        <!-- Header Section -->
        <div style="padding: 30px 25px 20px 25px;">
            <div style="background: #000; padding: 25px; text-align: center;">
                <span style="font-size: 34px; font-weight: bold; color: #fff; line-height: 1.2;">ATTENTION</span>
                <br>
                <span style="font-size: 34px; font-weight: bold; color: #fff; line-height: 1.2;">PLEASE!</span>
                <div style="float: right; margin-top: -55px;">
                    <div style="width: 45px; height: 45px; background: #f97316; border-radius: 50%;"></div>
                </div>
                <div style="clear: both;"></div>
            </div>
        </div>

        <!-- Person Icon -->
        <div style="text-align: right; padding-right: 30px; padding-bottom: 15px;">
            <div style="display: inline-block; width: 20px; height: 20px; background: #f97316; border-radius: 50%;"></div>
            <br>
            <div style="display: inline-block; width: 26px; height: 36px; background: #f97316; border-radius: 13px 13px 5px 5px; margin-top: 3px;"></div>
        </div>

        <!-- Content Card -->
        <div style="margin: 0 25px; background: #ffffff; padding: 25px;">
            <!-- Spiral Holes -->
            <div style="text-align: center; margin-bottom: 18px;">
                <span style="display: inline-block; width: 12px; height: 12px; background: #fbbf24; border-radius: 50%; margin: 0 5px;"></span>
                <span style="display: inline-block; width: 12px; height: 12px; background: #fbbf24; border-radius: 50%; margin: 0 5px;"></span>
                <span style="display: inline-block; width: 12px; height: 12px; background: #fbbf24; border-radius: 50%; margin: 0 5px;"></span>
                <span style="display: inline-block; width: 12px; height: 12px; background: #fbbf24; border-radius: 50%; margin: 0 5px;"></span>
                <span style="display: inline-block; width: 12px; height: 12px; background: #fbbf24; border-radius: 50%; margin: 0 5px;"></span>
                <span style="display: inline-block; width: 12px; height: 12px; background: #fbbf24; border-radius: 50%; margin: 0 5px;"></span>
                <span style="display: inline-block; width: 12px; height: 12px; background: #fbbf24; border-radius: 50%; margin: 0 5px;"></span>
                <span style="display: inline-block; width: 12px; height: 12px; background: #fbbf24; border-radius: 50%; margin: 0 5px;"></span>
            </div>

            <!-- Content -->
            <p style="font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 6px 0;">We're Looking For</p>
            <p style="font-size: 20px; font-weight: bold; color: #000; margin: 0 0 18px 0; line-height: 1.3;">{{ Str::limit($poster->job_title, 35) }}</p>

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
                        <span style="color: #f97316; margin-right: 8px;">&#8226;</span>{{ Str::limit($req, 38) }}
                    </div>
                    @endif
                @endforeach
            </div>

            <!-- Footer inside card -->
            <div style="border-top: 2px solid #e5e7eb; margin-top: 18px; padding-top: 18px;">
                <span style="font-size: 15px; font-weight: bold; color: #000;">{{ Str::limit($poster->company_name, 20) }}</span>
                <div style="float: right;">
                    <span style="display: inline-block; background: #f97316; color: #fff; padding: 12px 20px; font-size: 11px; font-weight: bold; border-radius: 18px; text-transform: uppercase;">Apply Now</span>
                </div>
                <div style="clear: both;"></div>
            </div>
        </div>

        <!-- Bottom Decorations -->
        <div style="padding: 20px 25px 30px 25px;">
            <div style="display: inline-block; width: 40px; height: 26px; background: #f97316; border-radius: 5px;"></div>
            <div style="float: right;">
                <div style="display: inline-block; width: 18px; height: 18px; background: #f97316; border-radius: 50%;"></div>
                <br>
                <div style="display: inline-block; width: 24px; height: 32px; background: #f97316; border-radius: 12px 12px 5px 5px; margin-top: 3px;"></div>
            </div>
            <div style="clear: both;"></div>
        </div>
    </div>
</body>
</html>
