<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Hiring Poster - {{ $poster->job_title }}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: #18181b;
            width: 100%;
            height: 100%;
        }

        .poster-container {
            width: 560px;
            height: 700px;
            position: relative;
            background: #18181b;
            margin: 0 auto;
            overflow: hidden;
            page-break-inside: avoid;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td {
            vertical-align: top;
        }

        .no-print {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }

        .no-print a,
        .no-print button {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 13px;
            margin-left: 8px;
            display: inline-block;
            font-family: sans-serif;
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-edit {
            background: #3b82f6;
            color: white;
        }

        .btn-download {
            background: #059669;
            color: white;
        }

        .btn-print {
            background: white;
            color: #1f2937;
            border: 1px solid #d1d5db !important;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    @if(!isset($isPdf) || !$isPdf)
        <div class="no-print">
            @if(isset($isEmployer) && $isEmployer)
                <a href="{{ route('employer.posters.index') }}" class="btn-back">Back</a>
                <a href="{{ route('employer.posters.edit', $poster->id) }}" class="btn-edit">Edit</a>
                <a href="{{ route('employer.posters.download', $poster->id) }}" class="btn-download">Download PDF</a>
            @else
                <a href="{{ route('admin.posters.index') }}" class="btn-back">Back</a>
                <a href="{{ route('admin.posters.edit', $poster->id) }}" class="btn-edit">Edit</a>
                <a href="{{ route('admin.posters.download', $poster->id) }}" class="btn-download">Download PDF</a>
            @endif
            <button onclick="window.print()" class="btn-print">Print</button>
        </div>
    @endif

    <div class="poster-container">

        <!-- Grid pattern overlay -->
        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: linear-gradient(rgba(59,130,246,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(59,130,246,0.03) 1px, transparent 1px); background-size: 30px 30px;"></div>

        <!-- Accent glow -->
        <div style="position: absolute; top: -100px; right: -100px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(59,130,246,0.15) 0%, transparent 70%);"></div>

        <!-- Header -->
        <table width="100%" cellpadding="0" cellspacing="0" style="padding-top: 45px; position: relative; z-index: 1;">
            <tr>
                <td style="padding-left: 50px;">
                    <div style="color: #3b82f6; font-size: 12px; font-weight: 700; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 12px; font-family: monospace;">
                        &lt;/&gt; TECH CAREERS
                    </div>
                    <div style="color: #fff; font-size: 48px; font-weight: 900; line-height: 1.1;">
                        WE'RE<br>
                        <span style="color: #3b82f6;">HIRING</span>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Main Content -->
        <div style="padding: 35px 50px; position: relative; z-index: 1;">

            <!-- Position -->
            <div style="margin-bottom: 25px;">
                <div style="color: #71717a; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 8px; font-family: monospace;">
                    // OPEN POSITION
                </div>
                <div style="color: #fff; font-size: 26px; font-weight: 700; line-height: 1.2;">
                    {{ Str::limit($poster->job_title, 40) }}
                </div>
                <div style="margin-top: 12px;">
                    @if($poster->employment_type)
                        <span style="background: #27272a; color: #3b82f6; border: 1px solid #3b82f6; padding: 5px 14px; font-size: 11px; font-weight: 600; border-radius: 4px; display: inline-block; font-family: monospace;">
                            {{ $poster->employment_type }}
                        </span>
                    @endif
                    @if($poster->location)
                        <span style="background: #27272a; color: #a1a1aa; border: 1px solid #3f3f46; padding: 5px 14px; font-size: 11px; font-weight: 600; border-radius: 4px; display: inline-block; margin-left: 8px; font-family: monospace;">
                            {{ Str::limit($poster->location, 20) }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Requirements -->
            <div style="background: #27272a; border: 1px solid #3f3f46; border-radius: 8px; padding: 25px; border-left: 3px solid #3b82f6;">
                <div style="color: #3b82f6; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 18px; font-family: monospace;">
                    REQUIREMENTS.TXT
                </div>

                <table width="100%" cellpadding="0" cellspacing="0">
                    @php
                        $requirements = preg_split('/[\r\n]+/', $poster->requirements);
                        $requirements = array_filter(array_map('trim', $requirements));
                        $requirements = array_slice($requirements, 0, 5);
                    @endphp
                    @foreach($requirements as $req)
                        @if(!empty($req))
                            <tr>
                                <td width="22" valign="top" style="padding-bottom: 12px;">
                                    <div style="color: #3b82f6; font-size: 14px; font-family: monospace;">
                                        &gt;
                                    </div>
                                </td>
                                <td valign="top" style="padding-bottom: 12px; font-size: 14px; color: #d4d4d8; line-height: 1.5;">
                                    {{ Str::limit($req, 50) }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            </div>

        </div>

        <!-- Footer -->
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; background: #27272a; border-top: 1px solid #3f3f46; padding: 25px 0;">
            <table width="460" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                <tr>
                    <td valign="middle" style="padding-left: 10px;">
                        <div style="color: #fff; font-weight: 700; font-size: 16px;">
                            {{ Str::limit($poster->company_name, 22) }}
                        </div>
                        <div style="color: #71717a; font-size: 11px; margin-top: 4px; font-family: monospace;">
                            Building the future
                        </div>
                    </td>
                    <td align="right" valign="middle" style="padding-right: 10px;">
                        <div style="background: #3b82f6; color: #fff; padding: 12px 28px; font-size: 12px; font-weight: 700; border-radius: 6px; text-transform: uppercase; letter-spacing: 1px; display: inline-block;">
                            Apply Now
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    </div>
</body>

</html>
