<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hiring Poster - {{ $poster->job_title }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; background: #f3f4f6; min-height: 100vh; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }

        .action-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .action-bar .left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .action-bar .right {
            display: flex;
            gap: 0.75rem;
        }

        .action-bar a, .action-bar button {
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }

        .btn-back {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-back:hover {
            background: #e5e7eb;
        }

        .btn-edit {
            background: #6366f1;
            color: white;
        }

        .btn-edit:hover {
            background: #4f46e5;
        }

        .btn-download {
            background: #10b981;
            color: white;
        }

        .btn-download:hover {
            background: #059669;
        }

        .btn-print {
            background: white;
            color: #374151;
            border: 1px solid #d1d5db !important;
        }

        .btn-print:hover {
            background: #f9fafb;
        }

        .poster-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .poster-container {
            padding-top: 80px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 100px 20px 40px;
        }

        .poster-wrapper {
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border-radius: 4px;
            overflow: hidden;
        }

        @media print {
            .action-bar { display: none !important; }
            .poster-container { padding: 0; }
            body { background: white; }
        }
    </style>
</head>
<body>
    @if(!isset($isPdf) || !$isPdf)
    <div class="action-bar">
        <div class="left">
            <a href="{{ route('employer.posters.index') }}" class="btn-back">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                </svg>
                Back
            </a>
            <h1 class="poster-title">{{ $poster->job_title }}</h1>
        </div>
        <div class="right">
            <a href="{{ route('employer.posters.edit', $poster->id) }}" class="btn-edit">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                </svg>
                Edit
            </a>
            <a href="{{ route('employer.posters.download', $poster->id) }}" class="btn-download">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                    <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                </svg>
                Download PDF
            </a>
            <button onclick="window.print()" class="btn-print">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
                </svg>
                Print
            </button>
        </div>
    </div>
    @endif

    <div class="poster-container">
        <div class="poster-wrapper">
            @php
                $templateSlug = $poster->template->slug ?? 'blue-megaphone';
            @endphp

            @if($templateSlug == 'blue-megaphone')
            <!-- Blue Megaphone Design -->
            <div style="width: 432px; min-height: 540px; background: #2563eb; border: 4px solid #1d4ed8;">
                <div style="padding: 35px 20px 20px 20px; text-align: center;">
                    <div style="display: inline-block; background: #fbbf24; color: #000; padding: 10px 30px; font-size: 20px; font-weight: bold;">WE ARE</div>
                    <div style="font-size: 65px; font-weight: bold; color: #fff; letter-spacing: -2px; line-height: 1; margin-top: 10px;">HIRING</div>
                </div>
                <div style="text-align: right; padding: 10px 30px 25px 30px;">
                    <div style="display: inline-block; vertical-align: middle; margin-right: 12px;">
                        <div style="width: 40px; height: 4px; background: #fbbf24; margin: 6px 0 6px auto;"></div>
                        <div style="width: 55px; height: 4px; background: #fbbf24; margin: 6px 0 6px auto;"></div>
                        <div style="width: 40px; height: 4px; background: #fbbf24; margin: 6px 0 6px auto;"></div>
                    </div>
                    <div style="display: inline-block; vertical-align: middle;">
                        <div style="width: 0; height: 0; border-left: 50px solid #d1d5db; border-top: 25px solid transparent; border-bottom: 25px solid transparent; display: inline-block;"></div>
                        <div style="width: 16px; height: 32px; background: #9ca3af; display: inline-block; vertical-align: middle; margin-left: -5px;"></div>
                    </div>
                </div>
                <div style="padding: 20px 30px 12px 30px;">
                    <p style="font-size: 16px; font-weight: bold; color: #fff; margin: 0 0 10px 0;">Title:</p>
                    <p style="font-size: 22px; font-weight: bold; color: #fff; margin: 0; line-height: 1.3;">{{ Str::limit($poster->job_title, 40) }}</p>
                </div>
                <div style="padding: 20px 30px;">
                    <div style="display: inline-block; background: #fbbf24; color: #000; padding: 7px 16px; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px;">Requirement:</div>
                    <div style="font-size: 13px; color: #fff; line-height: 1.9; margin: 0;">
                        @php
                            $requirements = preg_split('/[\r\n]+/', $poster->requirements);
                            $requirements = array_filter(array_map('trim', $requirements));
                            $requirements = array_slice($requirements, 0, 5);
                        @endphp
                        @foreach($requirements as $req)
                            @if(!empty($req))
                            <div style="margin-bottom: 5px;">
                                <span style="color: #fbbf24; margin-right: 8px;">&#8226;</span>{{ Str::limit($req, 40) }}
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div style="padding: 25px 30px 35px 30px;">
                    <span style="font-size: 16px; font-weight: bold; color: #fff;">{{ Str::limit($poster->company_name, 22) }}</span>
                    <div style="float: right;">
                        <span style="display: inline-block; background: #fbbf24; color: #000; padding: 12px 24px; font-size: 12px; font-weight: bold; border-radius: 20px; text-transform: uppercase;">Apply Now</span>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            </div>

            @elseif($templateSlug == 'yellow-attention')
            <!-- Yellow Attention Design -->
            <div style="width: 432px; min-height: 540px; background: #fbbf24; border: 4px solid #d97706;">
                <div style="padding: 30px 25px 20px 25px;">
                    <div style="background: #000; padding: 25px; text-align: center;">
                        <span style="font-size: 34px; font-weight: bold; color: #fff; line-height: 1.2;">ATTENTION</span><br>
                        <span style="font-size: 34px; font-weight: bold; color: #fff; line-height: 1.2;">PLEASE!</span>
                    </div>
                </div>
                <div style="margin: 0 25px; background: #ffffff; padding: 25px;">
                    <p style="font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 6px 0;">We're Looking For</p>
                    <p style="font-size: 20px; font-weight: bold; color: #000; margin: 0 0 18px 0; line-height: 1.3;">{{ Str::limit($poster->job_title, 35) }}</p>
                    <div style="display: inline-block; background: #fbbf24; color: #000; padding: 6px 14px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">Requirements</div>
                    <div style="font-size: 12px; color: #374151; line-height: 1.9; margin: 0;">
                        @php
                            $requirements = preg_split('/[\r\n]+/', $poster->requirements);
                            $requirements = array_filter(array_map('trim', $requirements));
                            $requirements = array_slice($requirements, 0, 5);
                        @endphp
                        @foreach($requirements as $req)
                            @if(!empty($req))
                            <div style="margin-bottom: 5px;">
                                <span style="color: #f97316; margin-right: 8px;">&#8226;</span>{{ Str::limit($req, 38) }}
                            </div>
                            @endif
                        @endforeach
                    </div>
                    <div style="border-top: 2px solid #e5e7eb; margin-top: 18px; padding-top: 18px;">
                        <span style="font-size: 15px; font-weight: bold; color: #000;">{{ Str::limit($poster->company_name, 20) }}</span>
                        <div style="float: right;">
                            <span style="display: inline-block; background: #f97316; color: #fff; padding: 12px 20px; font-size: 11px; font-weight: bold; border-radius: 18px; text-transform: uppercase;">Apply Now</span>
                        </div>
                        <div style="clear: both;"></div>
                    </div>
                </div>
                <div style="padding: 20px 25px 30px 25px;"></div>
            </div>

            @elseif($templateSlug == 'modern-corporate')
            <!-- Modern Corporate Design -->
            <div style="width: 432px; min-height: 540px; background: #1f2937; border: 4px solid #374151;">
                <div style="padding: 35px 20px 20px 20px; text-align: center;">
                    <div style="display: inline-block; background: #fbbf24; color: #000; padding: 12px 35px; font-size: 24px; font-weight: bold;">WE'RE</div><br>
                    <div style="display: inline-block; background: #374151; color: #fff; padding: 12px 35px; font-size: 24px; font-weight: bold; margin-top: 12px;">HIRING</div>
                </div>
                <div style="text-align: right; padding-right: 35px; padding-bottom: 20px;">
                    <div style="display: inline-block; width: 24px; height: 24px; background: #fbbf24; border-radius: 50%;"></div>
                    <br>
                    <div style="display: inline-block; width: 32px; height: 44px; background: #fbbf24; border-radius: 16px 16px 6px 6px; margin-top: 4px;"></div>
                </div>
                <div style="margin: 0 20px; background: #ffffff; padding: 25px;">
                    <p style="font-size: 11px; color: #9ca3af; text-transform: uppercase; letter-spacing: 1px; margin: 0 0 6px 0;">Position</p>
                    <p style="font-size: 20px; font-weight: bold; color: #111827; margin: 0 0 20px 0; line-height: 1.3;">{{ Str::limit($poster->job_title, 35) }}</p>
                    <div style="display: inline-block; background: #fbbf24; color: #000; padding: 6px 14px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">Requirements</div>
                    <div style="font-size: 12px; color: #374151; line-height: 1.9; margin: 0;">
                        @php
                            $requirements = preg_split('/[\r\n]+/', $poster->requirements);
                            $requirements = array_filter(array_map('trim', $requirements));
                            $requirements = array_slice($requirements, 0, 5);
                        @endphp
                        @foreach($requirements as $req)
                            @if(!empty($req))
                            <div style="margin-bottom: 5px;">
                                <span style="color: #fbbf24; margin-right: 8px;">&#8226;</span>{{ Str::limit($req, 40) }}
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div style="padding: 25px;">
                    <div style="display: inline-block; width: 36px; height: 36px; background: #374151; border-radius: 6px; color: #fff; font-size: 12px; font-weight: bold; text-align: center; line-height: 36px; vertical-align: middle;">{{ strtoupper(substr($poster->company_name, 0, 2)) }}</div>
                    <span style="font-size: 14px; font-weight: bold; color: #fff; margin-left: 10px; vertical-align: middle;">{{ Str::limit($poster->company_name, 18) }}</span>
                    <div style="float: right;">
                        <span style="display: inline-block; background: #fbbf24; color: #000; padding: 12px 22px; font-size: 12px; font-weight: bold; border-radius: 20px; text-transform: uppercase;">Apply Now</span>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            </div>

            @elseif($templateSlug == 'gradient-purple')
            <!-- Gradient Purple Design -->
            <div style="width: 432px; min-height: 540px; background: #764ba2;">
                <!-- Header Section -->
                <div style="padding: 40px 30px 25px 30px; text-align: center;">
                    <div style="display: inline-block; background: #8b5ec7; color: #fff; padding: 8px 25px; font-size: 14px; font-weight: 600; border-radius: 25px; letter-spacing: 2px; margin-bottom: 15px;">JOIN OUR TEAM</div>
                    <div style="font-size: 55px; font-weight: 900; color: #fff; letter-spacing: -2px; line-height: 1;">HIRING</div>
                    <div style="font-size: 20px; font-weight: 300; color: #e8dff0; margin-top: 5px; letter-spacing: 8px;">NOW</div>
                </div>

                <!-- Content Card -->
                <div style="margin: 0 25px; background: #ffffff; border-radius: 16px; padding: 25px;">
                    <!-- Job Title -->
                    <div style="margin-bottom: 18px;">
                        <p style="font-size: 11px; font-weight: 600; color: #764ba2; margin: 0 0 5px 0; text-transform: uppercase; letter-spacing: 1px;">Position</p>
                        <p style="font-size: 20px; font-weight: 700; color: #1f2937; margin: 0; line-height: 1.2;">{{ Str::limit($poster->job_title, 35) }}</p>
                    </div>

                    <!-- Requirements -->
                    <div style="margin-bottom: 15px;">
                        <div style="display: inline-block; background: #764ba2; color: #fff; padding: 5px 14px; font-size: 10px; font-weight: 600; border-radius: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px;">Requirements</div>
                        <div style="font-size: 12px; color: #4b5563; line-height: 1.8; margin: 0;">
                            @php
                                $requirements = preg_split('/[\r\n]+/', $poster->requirements);
                                $requirements = array_filter(array_map('trim', $requirements));
                                $requirements = array_slice($requirements, 0, 5);
                            @endphp
                            @foreach($requirements as $req)
                                @if(!empty($req))
                                <div style="margin-bottom: 4px;">
                                    <span style="color: #764ba2; margin-right: 8px; font-size: 14px;">&#10003;</span>{{ Str::limit($req, 38) }}
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div style="padding: 20px 30px 30px 30px;">
                    <span style="font-size: 14px; font-weight: 700; color: #fff;">{{ Str::limit($poster->company_name, 20) }}</span>
                    @if($poster->location)
                        <br><span style="font-size: 10px; color: #e8dff0;">{{ Str::limit($poster->location, 25) }}</span>
                    @endif
                    <div style="float: right;">
                        <span style="display: inline-block; background: #fff; color: #764ba2; padding: 12px 22px; font-size: 11px; font-weight: 700; border-radius: 25px; text-transform: uppercase; letter-spacing: 1px;">Apply Now</span>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            </div>

            @elseif($templateSlug == 'minimalist-green')
            <!-- Minimalist Green Design -->
            <div style="width: 432px; min-height: 540px; background: #f8fafc; border: 3px solid #10b981;">
                <!-- Green accent bar -->
                <div style="height: 8px; background: #10b981;"></div>

                <!-- Header Section -->
                <div style="padding: 35px 30px 20px 30px;">
                    <div style="margin-bottom: 25px;">
                        <!-- Company Initial -->
                        <div style="display: inline-block; width: 50px; height: 50px; background: #10b981; border-radius: 12px; color: white; font-size: 22px; font-weight: 700; text-align: center; line-height: 50px; vertical-align: middle;">{{ strtoupper(substr($poster->company_name, 0, 1)) }}</div>
                        <div style="display: inline-block; vertical-align: middle; margin-left: 15px;">
                            <p style="font-size: 16px; font-weight: 700; color: #1f2937; margin: 0;">{{ Str::limit($poster->company_name, 22) }}</p>
                            @if($poster->location)
                                <p style="font-size: 11px; color: #6b7280; margin: 3px 0 0 0;">{{ Str::limit($poster->location, 30) }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Hiring Badge -->
                    <div style="margin-bottom: 15px;">
                        <span style="display: inline-block; background: #10b981; color: white; padding: 6px 16px; font-size: 11px; font-weight: 600; border-radius: 20px; text-transform: uppercase; letter-spacing: 1px;">We're Hiring</span>
                    </div>

                    <!-- Job Title -->
                    <p style="font-size: 28px; font-weight: 800; color: #1f2937; margin: 0 0 8px 0; line-height: 1.2;">{{ Str::limit($poster->job_title, 30) }}</p>

                    @if($poster->employment_type)
                        <span style="display: inline-block; background: #ecfdf5; color: #059669; padding: 4px 12px; font-size: 11px; font-weight: 500; border-radius: 6px;">{{ $poster->employment_type }}</span>
                    @endif
                </div>

                <!-- Divider -->
                <div style="height: 1px; background: #e5e7eb; margin: 0 30px;"></div>

                <!-- Requirements Section -->
                <div style="padding: 20px 30px;">
                    <p style="font-size: 12px; font-weight: 600; color: #10b981; margin: 0 0 12px 0; text-transform: uppercase; letter-spacing: 1px;">What We're Looking For</p>
                    <div style="font-size: 13px; color: #374151; line-height: 2;">
                        @php
                            $requirements = preg_split('/[\r\n]+/', $poster->requirements);
                            $requirements = array_filter(array_map('trim', $requirements));
                            $requirements = array_slice($requirements, 0, 5);
                        @endphp
                        @foreach($requirements as $req)
                            @if(!empty($req))
                            <div style="margin-bottom: 6px;">
                                <span style="display: inline-block; width: 6px; height: 6px; background: #10b981; border-radius: 50%; margin-right: 10px;"></span>{{ Str::limit($req, 40) }}
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Salary (if provided) -->
                @if($poster->salary_range)
                <div style="padding: 0 30px 15px;">
                    <div style="background: #f0fdf4; border-radius: 10px; padding: 12px 16px;">
                        <span style="font-size: 18px;">&#128176;</span>
                        <span style="font-size: 10px; color: #6b7280; margin-left: 10px;">Salary Range</span>
                        <br>
                        <span style="font-size: 14px; font-weight: 600; color: #1f2937; margin-left: 28px;">{{ $poster->salary_range }}</span>
                    </div>
                </div>
                @endif

                <!-- Footer -->
                <div style="padding: 20px 30px; background: white; border-top: 1px solid #e5e7eb;">
                    @if($poster->contact_email)
                        <span style="font-size: 11px; color: #6b7280;">{{ $poster->contact_email }}</span>
                    @endif
                    @if($poster->deadline)
                        <br><span style="font-size: 10px; color: #9ca3af;">Deadline: {{ $poster->deadline->format('M d, Y') }}</span>
                    @endif
                    <div style="float: right;">
                        <span style="display: inline-block; background: #10b981; color: white; padding: 12px 24px; font-size: 12px; font-weight: 700; border-radius: 8px; text-transform: uppercase; letter-spacing: 1px;">Apply Now</span>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            </div>

            @else
            <!-- Default Blue Megaphone Design -->
            <div style="width: 432px; min-height: 540px; background: #2563eb; border: 4px solid #1d4ed8;">
                <div style="padding: 35px 20px 20px 20px; text-align: center;">
                    <div style="display: inline-block; background: #fbbf24; color: #000; padding: 10px 30px; font-size: 20px; font-weight: bold;">WE ARE</div>
                    <div style="font-size: 65px; font-weight: bold; color: #fff; letter-spacing: -2px; line-height: 1; margin-top: 10px;">HIRING</div>
                </div>
                <div style="padding: 20px 30px 12px 30px;">
                    <p style="font-size: 16px; font-weight: bold; color: #fff; margin: 0 0 10px 0;">Title:</p>
                    <p style="font-size: 22px; font-weight: bold; color: #fff; margin: 0; line-height: 1.3;">{{ Str::limit($poster->job_title, 40) }}</p>
                </div>
                <div style="padding: 20px 30px;">
                    <div style="display: inline-block; background: #fbbf24; color: #000; padding: 7px 16px; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px;">Requirement:</div>
                    <div style="font-size: 13px; color: #fff; line-height: 1.9; margin: 0;">
                        @php
                            $requirements = preg_split('/[\r\n]+/', $poster->requirements);
                            $requirements = array_filter(array_map('trim', $requirements));
                            $requirements = array_slice($requirements, 0, 5);
                        @endphp
                        @foreach($requirements as $req)
                            @if(!empty($req))
                            <div style="margin-bottom: 5px;">
                                <span style="color: #fbbf24; margin-right: 8px;">&#8226;</span>{{ Str::limit($req, 40) }}
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div style="padding: 25px 30px 35px 30px;">
                    <span style="font-size: 16px; font-weight: bold; color: #fff;">{{ Str::limit($poster->company_name, 22) }}</span>
                    <div style="float: right;">
                        <span style="display: inline-block; background: #fbbf24; color: #000; padding: 12px 24px; font-size: 12px; font-weight: bold; border-radius: 20px; text-transform: uppercase;">Apply Now</span>
                    </div>
                    <div style="clear: both;"></div>
                </div>
            </div>
            @endif
        </div>
    </div>
</body>
</html>
