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
            background: #dc2626;
            width: 100%;
            height: 100%;
        }

        .poster-container {
            width: 560px;
            height: 700px;
            position: relative;
            background: #dc2626;
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
            background: #991b1b;
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

        <!-- Decorative diagonal stripe -->
        <div style="position: absolute; top: -50px; right: -50px; width: 200px; height: 200px; background: #fef2f2; transform: rotate(45deg); opacity: 0.1;"></div>

        <!-- Header -->
        <table width="100%" cellpadding="0" cellspacing="0" style="padding-top: 50px;">
            <tr>
                <td align="center">
                    <div style="color: #fff; font-size: 22px; font-weight: 800; letter-spacing: 8px; text-transform: uppercase; margin-bottom: 10px;">
                        JOIN OUR TEAM
                    </div>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <div style="background: #fff; color: #dc2626; display: inline-block; padding: 15px 50px; font-size: 48px; font-weight: 900; letter-spacing: -1px;">
                        HIRING
                    </div>
                </td>
            </tr>
        </table>

        <!-- Main Content -->
        <div style="padding: 35px 50px;">

            <!-- Position -->
            <div style="margin-bottom: 25px;">
                <div style="background: #991b1b; color: #fecaca; display: inline-block; padding: 5px 14px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; border-radius: 3px; margin-bottom: 10px;">
                    Open Position
                </div>
                <div style="color: #fff; font-size: 28px; font-weight: 800; line-height: 1.2;">
                    {{ Str::limit($poster->job_title, 40) }}
                </div>
                @if($poster->employment_type)
                    <div style="color: #fecaca; font-size: 14px; font-weight: 600; margin-top: 8px;">
                        {{ $poster->employment_type }}
                        @if($poster->salary_range)
                            | {{ $poster->salary_range }}
                        @endif
                    </div>
                @endif
            </div>

            <!-- Requirements -->
            <div style="background: #fff; border-radius: 10px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                <div style="color: #dc2626; font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; border-bottom: 2px solid #fecaca; padding-bottom: 10px;">
                    What We're Looking For
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
                                <td width="24" valign="top" style="padding-bottom: 10px;">
                                    <div style="width: 18px; height: 18px; background: #dc2626; border-radius: 50%; color: #fff; font-size: 10px; font-weight: bold; text-align: center; line-height: 18px;">
                                        ✓
                                    </div>
                                </td>
                                <td valign="top" style="padding-bottom: 10px; padding-left: 8px; font-size: 14px; color: #374151; line-height: 1.5;">
                                    {{ Str::limit($req, 50) }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            </div>

        </div>

        <!-- Footer -->
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; background: #991b1b; padding: 25px 0;">
            <table width="480" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                <tr>
                    <td valign="middle" style="padding-left: 10px;">
                        <div style="color: #fff; font-weight: 800; font-size: 18px;">
                            {{ Str::limit($poster->company_name, 22) }}
                        </div>
                        @if($poster->location)
                            <div style="color: #fecaca; font-size: 12px; margin-top: 3px;">
                                {{ Str::limit($poster->location, 30) }}
                            </div>
                        @endif
                    </td>
                    <td align="right" valign="middle" style="padding-right: 10px;">
                        <div style="background: #fff; color: #dc2626; padding: 12px 30px; font-size: 13px; font-weight: 800; border-radius: 25px; text-transform: uppercase; display: inline-block;">
                            Apply Now
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    </div>
</body>

</html>
