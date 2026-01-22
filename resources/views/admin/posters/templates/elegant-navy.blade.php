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
            background: #0f172a;
            width: 100%;
            height: 100%;
        }

        .poster-container {
            width: 560px;
            height: 700px;
            position: relative;
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
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
            background: #1e40af;
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

        <!-- Gold accent line at top -->
        <div style="height: 5px; background: linear-gradient(90deg, #d4af37 0%, #f4d03f 50%, #d4af37 100%);"></div>

        <!-- Header -->
        <table width="100%" cellpadding="0" cellspacing="0" style="padding-top: 45px;">
            <tr>
                <td align="center">
                    <div style="color: #d4af37; font-size: 14px; font-weight: 600; letter-spacing: 6px; text-transform: uppercase; margin-bottom: 15px;">
                        Career Opportunity
                    </div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <div style="color: #fff; font-size: 52px; font-weight: 300; letter-spacing: 8px; text-transform: uppercase;">
                        WE ARE
                    </div>
                </td>
            </tr>
            <tr>
                <td align="center" style="padding-top: 5px;">
                    <div style="color: #d4af37; font-size: 60px; font-weight: 800; letter-spacing: 3px;">
                        HIRING
                    </div>
                </td>
            </tr>
        </table>

        <!-- Decorative divider -->
        <div style="text-align: center; padding: 25px 0;">
            <div style="display: inline-block; width: 60px; height: 1px; background: #d4af37;"></div>
            <div style="display: inline-block; width: 8px; height: 8px; background: #d4af37; border-radius: 50%; margin: 0 15px; vertical-align: middle;"></div>
            <div style="display: inline-block; width: 60px; height: 1px; background: #d4af37;"></div>
        </div>

        <!-- Main Content -->
        <div style="padding: 0 50px;">

            <!-- Position -->
            <div style="text-align: center; margin-bottom: 30px;">
                <div style="color: #94a3b8; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 10px;">
                    Position
                </div>
                <div style="color: #fff; font-size: 26px; font-weight: 700; line-height: 1.3;">
                    {{ Str::limit($poster->job_title, 45) }}
                </div>
                @if($poster->employment_type)
                    <div style="color: #d4af37; font-size: 13px; font-weight: 600; margin-top: 10px; letter-spacing: 1px;">
                        {{ $poster->employment_type }}
                    </div>
                @endif
            </div>

            <!-- Requirements -->
            <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(212,175,55,0.3); border-radius: 8px; padding: 25px;">
                <div style="color: #d4af37; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 18px; text-align: center;">
                    Requirements
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
                                <td width="20" valign="top" style="padding-bottom: 12px;">
                                    <div style="width: 6px; height: 6px; background: #d4af37; margin-top: 7px;"></div>
                                </td>
                                <td valign="top" style="padding-bottom: 12px; font-size: 14px; color: #e2e8f0; line-height: 1.5;">
                                    {{ Str::limit($req, 55) }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            </div>

        </div>

        <!-- Footer -->
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; border-top: 1px solid rgba(212,175,55,0.3);">
            <table width="480" cellpadding="0" cellspacing="0" style="margin: 0 auto; padding: 25px 0;">
                <tr>
                    <td valign="middle" style="padding-left: 10px;">
                        <div style="color: #fff; font-weight: 700; font-size: 16px; letter-spacing: 0.5px;">
                            {{ Str::limit($poster->company_name, 22) }}
                        </div>
                        @if($poster->location)
                            <div style="color: #94a3b8; font-size: 12px; margin-top: 4px;">
                                {{ Str::limit($poster->location, 28) }}
                            </div>
                        @endif
                    </td>
                    <td align="right" valign="middle" style="padding-right: 10px;">
                        <div style="background: #d4af37; color: #0f172a; padding: 12px 28px; font-size: 12px; font-weight: 800; border-radius: 3px; text-transform: uppercase; letter-spacing: 1px; display: inline-block;">
                            Apply Now
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    </div>
</body>

</html>
