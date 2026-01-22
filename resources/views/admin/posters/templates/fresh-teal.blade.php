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
            background: #f0fdfa;
            width: 100%;
            height: 100%;
        }

        .poster-container {
            width: 560px;
            height: 700px;
            position: relative;
            background: #f0fdfa;
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
            background: #0d9488;
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

        <!-- Decorative shapes -->
        <div style="position: absolute; top: 30px; left: 30px; width: 80px; height: 80px; border: 4px solid #14b8a6; border-radius: 50%; opacity: 0.3;"></div>
        <div style="position: absolute; top: 60px; left: 60px; width: 40px; height: 40px; background: #14b8a6; border-radius: 50%; opacity: 0.2;"></div>
        <div style="position: absolute; bottom: 150px; right: 30px; width: 60px; height: 60px; border: 3px solid #14b8a6; opacity: 0.2;"></div>

        <!-- Header -->
        <table width="100%" cellpadding="0" cellspacing="0" style="padding-top: 50px;">
            <tr>
                <td align="center">
                    <div style="background: #14b8a6; color: #fff; display: inline-block; padding: 10px 35px; font-size: 14px; font-weight: 700; letter-spacing: 3px; text-transform: uppercase; border-radius: 30px; margin-bottom: 20px;">
                        We're Hiring
                    </div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <div style="color: #0f766e; font-size: 50px; font-weight: 900; line-height: 1; letter-spacing: -1px;">
                        GROW WITH
                    </div>
                </td>
            </tr>
            <tr>
                <td align="center" style="padding-top: 5px;">
                    <div style="color: #14b8a6; font-size: 50px; font-weight: 900; line-height: 1; letter-spacing: -1px;">
                        US
                    </div>
                </td>
            </tr>
        </table>

        <!-- Main Content -->
        <div style="padding: 35px 50px;">

            <!-- Position Card -->
            <div style="background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(20,184,166,0.15);">

                <!-- Job Title -->
                <div style="text-align: center; margin-bottom: 22px;">
                    <div style="color: #14b8a6; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 3px; margin-bottom: 10px;">
                        Open Position
                    </div>
                    <div style="color: #134e4a; font-size: 26px; font-weight: 800; line-height: 1.2;">
                        {{ Str::limit($poster->job_title, 40) }}
                    </div>
                    @if($poster->employment_type)
                        <div style="margin-top: 12px;">
                            <span style="background: #ccfbf1; color: #0f766e; padding: 6px 16px; font-size: 12px; font-weight: 700; border-radius: 20px; display: inline-block;">
                                {{ $poster->employment_type }}
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Divider -->
                <div style="height: 2px; background: linear-gradient(90deg, transparent, #14b8a6, transparent); margin-bottom: 22px;"></div>

                <!-- Requirements -->
                <div>
                    <div style="color: #0f766e; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; text-align: center;">
                        What You'll Need
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
                                    <td width="26" valign="top" style="padding-bottom: 10px;">
                                        <div style="width: 18px; height: 18px; background: #ccfbf1; border-radius: 50%; color: #0f766e; font-size: 10px; font-weight: bold; text-align: center; line-height: 18px;">
                                            ✓
                                        </div>
                                    </td>
                                    <td valign="top" style="padding-bottom: 10px; padding-left: 6px; font-size: 14px; color: #374151; line-height: 1.5;">
                                        {{ Str::limit($req, 50) }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </table>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; background: #14b8a6; padding: 25px 0;">
            <table width="460" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                <tr>
                    <td valign="middle" style="padding-left: 10px;">
                        <div style="color: #fff; font-weight: 800; font-size: 18px;">
                            {{ Str::limit($poster->company_name, 20) }}
                        </div>
                        @if($poster->location)
                            <div style="color: #ccfbf1; font-size: 12px; margin-top: 3px;">
                                {{ Str::limit($poster->location, 28) }}
                            </div>
                        @endif
                    </td>
                    <td align="right" valign="middle" style="padding-right: 10px;">
                        <div style="background: #fff; color: #0f766e; padding: 12px 30px; font-size: 13px; font-weight: 800; border-radius: 25px; text-transform: uppercase; display: inline-block;">
                            Apply Now
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    </div>
</body>

</html>
