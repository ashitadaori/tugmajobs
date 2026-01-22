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
            background: #f97316;
            width: 100%;
            height: 100%;
        }

        .poster-container {
            width: 560px;
            height: 700px;
            position: relative;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 50%, #dc2626 100%);
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
            background: #c2410c;
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

        <!-- Decorative circles -->
        <div style="position: absolute; top: -80px; right: -80px; width: 200px; height: 200px; border: 30px solid rgba(255,255,255,0.1); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: -100px; left: -100px; width: 250px; height: 250px; border: 25px solid rgba(255,255,255,0.08); border-radius: 50%;"></div>

        <!-- Header -->
        <table width="100%" cellpadding="0" cellspacing="0" style="padding-top: 50px;">
            <tr>
                <td align="center">
                    <div style="background: #fff; color: #ea580c; display: inline-block; padding: 8px 30px; font-size: 14px; font-weight: 800; letter-spacing: 4px; text-transform: uppercase; border-radius: 30px; margin-bottom: 20px;">
                        Now Hiring
                    </div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <div style="color: #fff; font-size: 58px; font-weight: 900; line-height: 1; letter-spacing: -2px; text-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                        JOIN US!
                    </div>
                </td>
            </tr>
        </table>

        <!-- Main Content -->
        <div style="padding: 40px 45px;">

            <!-- Position Card -->
            <div style="background: #fff; border-radius: 16px; padding: 30px; box-shadow: 0 15px 40px rgba(0,0,0,0.15);">

                <!-- Job Title -->
                <div style="margin-bottom: 25px;">
                    <div style="color: #9ca3af; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 8px;">
                        We're Looking For
                    </div>
                    <div style="color: #1f2937; font-size: 24px; font-weight: 800; line-height: 1.2;">
                        {{ Str::limit($poster->job_title, 40) }}
                    </div>
                    @if($poster->employment_type || $poster->salary_range)
                        <div style="margin-top: 10px;">
                            @if($poster->employment_type)
                                <span style="background: #fff7ed; color: #ea580c; padding: 4px 12px; font-size: 11px; font-weight: 700; border-radius: 15px; display: inline-block;">
                                    {{ $poster->employment_type }}
                                </span>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Divider -->
                <div style="height: 2px; background: linear-gradient(90deg, #f97316, #ea580c); border-radius: 2px; margin-bottom: 20px;"></div>

                <!-- Requirements -->
                <div>
                    <div style="color: #ea580c; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">
                        Requirements
                    </div>

                    <table width="100%" cellpadding="0" cellspacing="0">
                        @php
                            $requirements = preg_split('/[\r\n]+/', $poster->requirements);
                            $requirements = array_filter(array_map('trim', $requirements));
                            $requirements = array_slice($requirements, 0, 5);
                        @endphp
                        @foreach($requirements as $index => $req)
                            @if(!empty($req))
                                <tr>
                                    <td width="28" valign="top" style="padding-bottom: 10px;">
                                        <div style="width: 20px; height: 20px; background: linear-gradient(135deg, #f97316, #ea580c); border-radius: 50%; color: #fff; font-size: 10px; font-weight: bold; text-align: center; line-height: 20px;">
                                            {{ $index + 1 }}
                                        </div>
                                    </td>
                                    <td valign="top" style="padding-bottom: 10px; padding-left: 8px; font-size: 14px; color: #4b5563; line-height: 1.5;">
                                        {{ Str::limit($req, 48) }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </table>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding: 25px 0;">
            <table width="470" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                <tr>
                    <td valign="middle" style="padding-left: 10px;">
                        <div style="color: #fff; font-weight: 800; font-size: 18px; text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            {{ Str::limit($poster->company_name, 20) }}
                        </div>
                        @if($poster->location)
                            <div style="color: rgba(255,255,255,0.85); font-size: 13px; margin-top: 3px;">
                                {{ Str::limit($poster->location, 25) }}
                            </div>
                        @endif
                    </td>
                    <td align="right" valign="middle" style="padding-right: 10px;">
                        <div style="background: #fff; color: #ea580c; padding: 14px 32px; font-size: 13px; font-weight: 800; border-radius: 30px; text-transform: uppercase; display: inline-block; box-shadow: 0 5px 20px rgba(0,0,0,0.15);">
                            Apply Now
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    </div>
</body>

</html>
