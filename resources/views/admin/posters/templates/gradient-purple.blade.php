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
            background: #764ba2;
            /* Fallback */
            width: 100%;
            height: 100%;
        }

        .poster-container {
            width: 560px;
            height: 700px;
            position: relative;
            background: #764ba2;
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
                        style="display: inline-block; background: rgba(255,255,255,0.2); color: #fff; padding: 10px 35px; font-size: 16px; font-weight: 600; border-radius: 30px; letter-spacing: 2px; text-transform: uppercase;">
                        We Are
                    </div>
                </td>
            </tr>
            <tr>
                <td align="center" style="padding-top: 10px; padding-bottom: 30px;">
                    <div
                        style="font-size: 60px; font-weight: 900; color: #fff; letter-spacing: -1px; line-height: 1; text-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                        HIRING
                    </div>
                </td>
            </tr>
        </table>

        <!-- Main Card -->
        <div style="padding: 0 40px;">
            <div
                style="background: #ffffff; border-radius: 20px; padding: 35px; box-shadow: 0 15px 40px rgba(0,0,0,0.2);">

                <div style="text-align: center; margin-bottom: 25px;">
                    <div
                        style="color: #667eea; font-weight: bold; text-transform: uppercase; font-size: 13px; letter-spacing: 2px; margin-bottom: 10px;">
                        Open Position</div>
                    <div style="color: #1f2937; font-size: 32px; font-weight: 800; line-height: 1.2;">
                        {{ Str::limit($poster->job_title, 40) }}
                    </div>
                </div>

                <!-- Requirements -->
                <div style="padding: 20px; background: #f9f9f9; border-radius: 12px; margin-bottom: 25px;">
                    <div
                        style="color: #764ba2; font-weight: 800; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">
                        Requirements</div>

                    <table width="100%" cellpadding="0" cellspacing="0">
                        @php
                            $requirements = preg_split('/[\r\n]+/', $poster->requirements);
                            $requirements = array_filter(array_map('trim', $requirements));
                            $requirements = array_slice($requirements, 0, 4);
                        @endphp
                        @foreach($requirements as $req)
                            @if(!empty($req))
                                <tr>
                                    <td width="24" valign="top" style="padding-bottom: 10px;">
                                        <div
                                            style="width: 18px; height: 18px; background: #e0e7ff; border-radius: 50%; color: #4f46e5; font-size: 12px; font-weight: bold; text-align: center; line-height: 18px;">
                                            &#10003;</div>
                                    </td>
                                    <td valign="top"
                                        style="padding-bottom: 10px; font-size: 14px; color: #4b5563; line-height: 1.5;">
                                        {{ Str::limit($req, 55) }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </table>
                </div>

            </div>
        </div>

        <!-- Footer -->
        <div style="position: absolute; bottom: 0; left: 0; width: 100%;">
            <table width="480" cellpadding="0" cellspacing="0" style="margin: 0 auto; padding-bottom: 40px;">
                <tr>
                    <td valign="middle">
                        <div style="color: #fff; font-weight: 800; font-size: 20px;">
                            {{ Str::limit($poster->company_name, 22) }}
                        </div>
                        @if($poster->location)
                            <div
                                style="color: #d1d5db; font-size: 13px; margin-top: 5px; display: flex; align-items: center;">
                                <span style="margin-right: 4px;">&#128205;</span> {{ Str::limit($poster->location, 25) }}
                            </div>
                        @endif
                    </td>
                    <td align="right" valign="middle">
                        <div
                            style="background: #fff; color: #764ba2; padding: 14px 28px; font-size: 14px; font-weight: 800; border-radius: 30px; text-transform: uppercase; display: inline-block; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                            Apply Now
                        </div>
                    </td>
                </tr>
            </table>
        </div>

    </div>
</body>

</html>