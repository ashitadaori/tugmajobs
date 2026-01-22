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
            background: #1e3a8a;
            width: 100%;
            height: 100%;
        }

        .poster-container {
            width: 560px;
            height: 700px;
            position: relative;
            background: #1e3a8a;
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

        <!-- Header -->
        <table width="100%" cellpadding="0" cellspacing="0" style="padding-top: 40px;">
            <tr>
                <td align="center">
                    <div
                        style="display: inline-block; background: #fbbf24; color: #000; padding: 10px 30px; font-size: 20px; font-weight: 800; border-radius: 4px; transform: rotate(-2deg); box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                        WE ARE
                    </div>
                </td>
            </tr>
            <tr>
                <td align="center" style="padding-top: 15px;">
                    <div
                        style="color: #fff; font-size: 70px; font-weight: 900; line-height: 1; letter-spacing: -2px; text-shadow: 0 4px 0 rgba(0,0,0,0.1);">
                        HIRING
                    </div>
                </td>
            </tr>
        </table>

        <!-- Megaphone icon/graphic simulation -->
        <div style="text-align: right; padding-right: 40px; margin-top: -20px;">
            <div
                style="display: inline-block; width: 60px; height: 60px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <!-- Simple Megaphone Shape using CSS borders can be tricky in PDF, using simple block -->
                <div style="width: 30px; height: 30px; background: #fbbf24; transform: rotate(-20deg);"></div>
            </div>
        </div>

        <!-- Main Content -->
        <div style="padding: 20px 50px;">

            <div style="margin-bottom: 30px;">
                <div
                    style="color: #93c5fd; font-weight: bold; font-size: 16px; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px;">
                    Position</div>
                <div style="color: #fff; font-size: 32px; font-weight: 800; line-height: 1.1;">
                    {{ Str::limit($poster->job_title, 40) }}
                </div>
                @if($poster->employment_type)
                    <div
                        style="background: rgba(255,255,255,0.2); color: #fff; display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 13px; font-weight: bold; margin-top: 10px;">
                        {{ $poster->employment_type }}
                    </div>
                @endif
            </div>

            <!-- Requirements Box -->
            <div
                style="background: rgba(255,255,255,0.1); border-radius: 12px; padding: 25px; border: 1px solid rgba(255,255,255,0.2);">
                <div
                    style="background: #fbbf24; color: #000; display: inline-block; padding: 6px 14px; font-size: 12px; font-weight: 800; text-transform: uppercase; border-radius: 4px; margin-bottom: 15px;">
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
                                <td width="20" valign="top" style="padding-bottom: 10px;">
                                    <div
                                        style="width: 6px; height: 6px; background: #fbbf24; border-radius: 50%; margin-top: 8px;">
                                    </div>
                                </td>
                                <td valign="top" style="padding-bottom: 10px; font-size: 15px; color: #fff; line-height: 1.5;">
                                    {{ Str::limit($req, 50) }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            </div>

        </div>

        <!-- Footer -->
        <div style="position: absolute; bottom: 0; left: 0; width: 100%;">
            <table width="500" cellpadding="0" cellspacing="0"
                style="margin: 0 auto; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 25px; padding-bottom: 40px;">
                <tr>
                    <td valign="middle" style="padding-left: 20px;">
                        <div style="color: #fff; font-weight: 800; font-size: 18px;">
                            {{ Str::limit($poster->company_name, 20) }}
                        </div>
                        @if($poster->location)
                            <div style="color: #93c5fd; font-size: 13px; margin-top: 4px;">
                                {{ Str::limit($poster->location, 25) }}
                            </div>
                        @endif
                    </td>
                    <td align="right" valign="middle" style="padding-right: 20px;">
                        <div
                            style="background: #fbbf24; color: #09090b; padding: 12px 28px; font-size: 14px; font-weight: 800; border-radius: 30px; text-transform: uppercase; display: inline-block; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                            Apply Now
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    </div>
</body>

</html>