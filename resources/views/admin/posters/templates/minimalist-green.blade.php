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
            font-family: Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #ffffff;
            width: 100%;
            height: 100%;
        }

        .poster-container {
            width: 560px;
            /* Reduced from 576px */
            height: 700px;
            /* Reduced from 720px */
            position: relative;
            background: #ffffff;
            border: 4px solid #10b981;
            overflow: hidden;
            /* Force single page */
            margin: 0 auto;
            page-break-inside: avoid;
        }

        /* Tables */
        table {
            border-collapse: collapse;
            border-spacing: 0;
        }

        td {
            vertical-align: middle;
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
            background: #0d6efd;
            color: white;
        }

        .btn-download {
            background: #198754;
            color: white;
        }

        .btn-print {
            background: white;
            color: #333;
            border: 1px solid #333 !important;
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
        <!-- Decoration Bar -->
        <div style="height: 12px; background: #10b981; width: 100%;"></div>

        <!-- Main Content Area -->
        <div style="padding: 30px 40px;">

            <!-- Header Table -->
            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 25px;">
                <tr>
                    <td align="center">
                        <!-- Inner Header Table for centering -->
                        <table cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                            <tr>
                                <td width="60" align="right" style="padding-right: 15px;">
                                    <table cellpadding="0" cellspacing="0" width="54" height="54"
                                        style="background: #10b981; border-radius: 12px; margin: 0 auto;">
                                        <tr>
                                            <td align="center" valign="middle"
                                                style="color: white; font-size: 26px; font-weight: bold; border: none;">
                                                {{ strtoupper(substr($poster->company_name, 0, 1)) }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td align="left">
                                    <div style="font-size: 24px; font-weight: bold; color: #1f2937; line-height: 1.2;">
                                        {{ Str::limit($poster->company_name, 20) }}
                                    </div>
                                    @if($poster->location)
                                        <div style="font-size: 14px; color: #6b7280; margin-top: 4px;">
                                            {{ Str::limit($poster->location, 30) }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <!-- Title & Badge Section -->
            <div style="text-align: center; margin-bottom: 25px;">
                <div style="margin-bottom: 15px;">
                    <span
                        style="background: #10b981; color: white; padding: 6px 18px; font-size: 12px; font-weight: bold; border-radius: 50px; text-transform: uppercase; letter-spacing: 1px;">We're
                        Hiring</span>
                </div>
                <div style="font-size: 36px; font-weight: 800; color: #1f2937; margin-bottom: 10px; line-height: 1.1;">
                    {{ Str::limit($poster->job_title, 25) }}
                </div>
                @if($poster->employment_type)
                    <div>
                        <span
                            style="background: #ecfdf5; color: #047857; padding: 5px 14px; font-size: 13px; font-weight: 600; border-radius: 6px;">{{ $poster->employment_type }}</span>
                    </div>
                @endif
            </div>

            <!-- Divider -->
            <div style="height: 1px; background: #e5e7eb; width: 100%; margin-bottom: 30px;"></div>

            <!-- Requirements -->
            <div style="margin-bottom: 30px;">
                <div
                    style="text-align: center; font-size: 13px; font-weight: bold; color: #10b981; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 20px;">
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
                                <td width="20" valign="top" style="padding-bottom: 10px;">
                                    <div
                                        style="width: 6px; height: 6px; background: #10b981; border-radius: 50%; margin-top: 8px;">
                                    </div>
                                </td>
                                <td valign="top"
                                    style="padding-bottom: 10px; font-size: 15px; color: #374151; line-height: 1.5;">
                                    {{ Str::limit($req, 65) }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </table>
            </div>

            <!-- Salary -->
            @if($poster->salary_range)
                <div
                    style="background: #effdf5; border: 1px solid #d1fae5; border-radius: 12px; padding: 15px; text-align: center; margin-bottom: 20px; width: 80%; margin-left: auto; margin-right: auto;">
                    <span style="font-size: 20px;">&#128176;</span>
                    <span
                        style="font-size: 16px; font-weight: bold; color: #1f2937; margin-left: 8px;">{{ $poster->salary_range }}</span>
                </div>
            @endif

        </div>

        <!-- Footer - Fixed Bottom -->
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; padding-bottom: 25px;">
            <div style="margin: 0 40px; border-top: 1px solid #e5e7eb; padding-top: 20px;">
                <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="left" valign="middle">
                            <div style="font-size: 13px; color: #4b5563; line-height: 1.4;">
                                @if($poster->contact_email)
                                    <div><strong>Email:</strong> {{ $poster->contact_email }}</div>
                                @endif
                                @if($poster->deadline)
                                    <div><strong>Deadline:</strong> {{ $poster->deadline->format('M d, Y') }}</div>
                                @endif
                            </div>
                        </td>
                        <td align="right" valign="middle">
                            <div
                                style="background: #10b981; color: white; padding: 12px 24px; font-size: 14px; font-weight: bold; border-radius: 8px; text-transform: uppercase; display: inline-block;">
                                Apply Now
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>

</html>