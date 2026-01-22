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
            background: #fbbf24;
            width: 100%;
            height: 100%;
        }

        .poster-container {
            width: 560px;
            height: 700px;
            position: relative;
            background: #fbbf24;
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
        <!-- Top 'Hazard' Strip -->
        <table cellpadding="0" cellspacing="0" width="100%" style="height: 20px;">
            <tr>
                @for($i = 0; $i < 20; $i++)
                    <td width="5%" style="background: #000; transform: skewX(-20deg);"></td>
                    <td width="5%" style="background: #fbbf24; transform: skewX(-20deg);"></td>
                @endfor
            </tr>
        </table>

        <!-- Header -->
        <div style="padding: 40px 40px 20px 40px; text-align: center;">
            <div
                style="background: #000; color: #fff; padding: 15px 30px; display: inline-block; transform: rotate(-2deg); box-shadow: 5px 5px 0px rgba(0,0,0,0.1);">
                <h1
                    style="margin: 0; font-size: 36px; text-transform: uppercase; font-weight: 900; letter-spacing: 2px;">
                    Attention</h1>
                <h2
                    style="margin: 0; font-size: 36px; text-transform: uppercase; font-weight: 900; letter-spacing: 2px; color: #fbbf24;">
                    Please!</h2>
            </div>
        </div>

        <!-- Main Card -->
        <div style="padding: 0 40px; position: relative;">
            <div
                style="background: #fff; border-radius: 4px; padding: 35px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border-bottom: 8px solid #000;">

                <!-- Badge -->
                <div style="text-align: center; margin-bottom: 25px;">
                    <span
                        style="background: #fbbf24; color: #000; padding: 6px 16px; font-weight: bold; text-transform: uppercase; font-size: 12px; letter-spacing: 1px;">We
                        Are Hiring</span>
                </div>

                <!-- Job Title -->
                <div style="text-align: center; margin-bottom: 30px;">
                    <h2
                        style="margin: 0; font-size: 32px; color: #1f2937; line-height: 1.2; text-transform: uppercase; font-weight: 800;">
                        {{ Str::limit($poster->job_title, 40) }}
                    </h2>
                    @if($poster->employment_type)
                        <div style="margin-top: 10px; font-size: 14px; font-weight: bold; color: #4b5563;">
                            {{ $poster->employment_type }}
                        </div>
                    @endif
                </div>

                <!-- Requirements Section -->
                <div
                    style="background: #fdf2f8; border-left: 4px solid #f97316; padding: 20px; background: #fff7ed; margin-bottom: 25px;">
                    <h3
                        style="margin: 0 0 15px 0; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; color: #c2410c;">
                        Requirements</h3>
                    <table width="100%" cellpadding="0" cellspacing="0">
                        @php
                            $requirements = preg_split('/[\r\n]+/', $poster->requirements);
                            $requirements = array_filter(array_map('trim', $requirements));
                            $requirements = array_slice($requirements, 0, 5); // Limit to 5 items
                        @endphp
                        @foreach($requirements as $req)
                            @if(!empty($req))
                                <tr>
                                    <td width="20" valign="top" style="padding-bottom: 10px;">
                                        <div style="width: 6px; height: 6px; background: #f97316; margin-top: 7px;"></div>
                                    </td>
                                    <td valign="top"
                                        style="padding-bottom: 10px; font-size: 15px; color: #374151; line-height: 1.4;">
                                        {{ Str::limit($req, 60) }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </table>
                </div>

                <!-- Footer Info -->
                <table width="100%" cellpadding="0" cellspacing="0"
                    style="border-top: 1px solid #e5e7eb; padding-top: 20px;">
                    <tr>
                        <td valign="middle">
                            <div style="font-weight: 800; font-size: 18px; color: #111;">
                                {{ Str::limit($poster->company_name, 25) }}</div>
                            @if($poster->location)
                                <div style="font-size: 13px; color: #6b7280; margin-top: 4px;">
                                    {{ Str::limit($poster->location, 30) }}</div>
                            @endif
                        </td>
                        <td align="right" valign="middle">
                            <div
                                style="background: #000; color: #fbbf24; padding: 12px 24px; font-size: 14px; font-weight: bold; text-transform: uppercase; display: inline-block;">
                                Apply Now
                            </div>
                        </td>
                    </tr>
                </table>

            </div>
        </div>

        <!-- Bottom Decoration -->
        <div style="position: absolute; bottom: 30px; left: 0; width: 100%; text-align: center;">
            <div style="display: inline-block; width: 60px; height: 8px; background: rgba(0,0,0,0.1); margin: 0 5px;">
            </div>
            <div style="display: inline-block; width: 60px; height: 8px; background: rgba(0,0,0,0.1); margin: 0 5px;">
            </div>
            <div style="display: inline-block; width: 60px; height: 8px; background: rgba(0,0,0,0.1); margin: 0 5px;">
            </div>
        </div>
    </div>
</body>

</html>