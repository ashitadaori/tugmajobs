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
            background: #1f2937;
            width: 100%;
            height: 100%;
        }

        .poster-container {
            width: 560px;
            height: 700px;
            position: relative;
            background: #1f2937;
            margin: 0 auto;
            overflow: hidden;
            page-break-inside: avoid;
        }

        /* Utility */
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
            background: #2563eb;
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

        <!-- Header Section -->
        <table width="100%" cellpadding="0" cellspacing="0" style="padding-top: 40px; padding-bottom: 30px;">
            <tr>
                <td align="center">
                    <table cellpadding="0" cellspacing="0">
                        <tr>
                            <td
                                style="background: #fbbf24; color: #000; padding: 12px 35px; font-size: 24px; font-weight: 800; letter-spacing: 1px;">
                                WE'RE</td>
                        </tr>
                        <tr>
                            <td height="10"></td>
                        </tr>
                        <tr>
                            <td
                                style="background: #374151; color: #fff; padding: 12px 35px; font-size: 24px; font-weight: 800; letter-spacing: 1px;">
                                HIRING</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Content Card -->
        <div style="padding: 0 40px;">
            <div
                style="background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <!-- Top Stripe -->
                <div style="height: 6px; background: #fbbf24; width: 100%;"></div>

                <div style="padding: 40px 30px;">
                    <div
                        style="text-transform: uppercase; font-size: 12px; font-weight: bold; color: #9ca3af; letter-spacing: 2px; margin-bottom: 10px;">
                        Position</div>
                    <div
                        style="font-size: 30px; font-weight: 800; color: #111827; margin-bottom: 25px; line-height: 1.1;">
                        {{ Str::limit($poster->job_title, 35) }}
                    </div>

                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td style="background: #f3f4f6; padding: 25px; border-radius: 8px;">
                                <div
                                    style="display: inline-block; background: #fbbf24; color: #000; padding: 5px 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">
                                    Requirements</div>

                                <table width="100%" cellpadding="0" cellspacing="0">
                                    @php
                                        $requirements = preg_split('/[\r\n]+/', $poster->requirements);
                                        $requirements = array_filter(array_map('trim', $requirements));
                                        $requirements = array_slice($requirements, 0, 5);
                                    @endphp
                                    @foreach($requirements as $req)
                                        @if(!empty($req))
                                            <tr>
                                                <td width="20" valign="top" style="padding-bottom: 8px;">
                                                    <div
                                                        style="width: 6px; height: 6px; background: #fbbf24; border-radius: 50%; margin-top: 7px;">
                                                    </div>
                                                </td>
                                                <td valign="top"
                                                    style="padding-bottom: 8px; font-size: 14px; color: #374151; line-height: 1.5;">
                                                    {{ Str::limit($req, 55) }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="position: absolute; bottom: 0; left: 0; width: 100%;">
            <table width="480" cellpadding="0" cellspacing="0" style="margin: 0 auto; padding-bottom: 40px;">
                <tr>
                    <td valign="middle">
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="40" height="40" align="center" valign="middle"
                                    style="background: #374151; border-radius: 8px; color: #fff; font-weight: bold; font-size: 18px;">
                                    {{ strtoupper(substr($poster->company_name, 0, 1)) }}
                                </td>
                                <td style="padding-left: 12px;">
                                    <div style="color: #fff; font-weight: bold; font-size: 16px;">
                                        {{ Str::limit($poster->company_name, 20) }}
                                    </div>
                                    @if($poster->location)
                                        <div style="color: #9ca3af; font-size: 12px; margin-top: 2px;">
                                            {{ Str::limit($poster->location, 25) }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td align="right" valign="middle">
                        <div
                            style="background: #fbbf24; color: #000; padding: 12px 26px; font-size: 14px; font-weight: 800; border-radius: 30px; text-transform: uppercase; display: inline-block;">
                            Apply Now
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    </div>
</body>

</html>