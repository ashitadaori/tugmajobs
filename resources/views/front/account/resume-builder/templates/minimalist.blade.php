<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $resume->title }}</title>
    <style>
        @page {
            size: 210mm 297mm;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 210mm;
            min-height: 297mm;
            max-height: 297mm;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        .resume-page {
            width: 210mm;
            height: 297mm;
            min-height: 297mm;
            max-height: 297mm;
            margin: 0;
            padding: 0;
            background: white;
            position: relative;
            overflow: hidden;
            page-break-after: avoid;
            page-break-inside: avoid;
        }

        .resume-container {
            height: 297mm;
            overflow: hidden;
        }

        .sidebar-cell {
            position: absolute;
            top: 0;
            left: 0;
            width: 30%;
            height: 297mm;
            background: #2c3e50;
            color: white;
            padding: 25px 18px;
            overflow: hidden;
        }

        .main-cell {
            position: absolute;
            top: 0;
            left: 30%;
            width: 70%;
            height: 297mm;
            background: white;
            padding: 25px 22px;
            overflow: hidden;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* Sidebar Styles */
        .profile-section {
            text-align: center;
            margin-bottom: 20px;
            padding-top: 10px;
        }

        .profile-photo {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 12px;
            display: block;
            border: 3px solid white;
        }

        .name-sidebar {
            font-size: 13pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .job-title-sidebar {
            font-size: 9pt;
            text-align: center;
            margin-bottom: 15px;
            color: #ecf0f1;
        }

        .sidebar-section {
            margin-bottom: 18px;
        }

        .sidebar-title {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #34495e;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: white;
        }

        .contact-item {
            margin-bottom: 8px;
            font-size: 9pt;
            word-wrap: break-word;
            line-height: 1.5;
        }

        .skill-item {
            margin-bottom: 10px;
        }

        .skill-name {
            font-size: 9pt;
            margin-bottom: 4px;
        }

        .skill-bar {
            height: 6px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .skill-fill {
            height: 100%;
            background: white;
            border-radius: 3px;
        }

        /* Main Content Styles */
        .section {
            margin-bottom: 18px;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
            padding-bottom: 4px;
            border-bottom: 2px solid #2c3e50;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .about-text {
            font-size: 9pt;
            line-height: 1.6;
            color: #555;
        }

        .timeline-item {
            margin-bottom: 14px;
            padding-left: 12px;
            border-left: 2px solid #3498db;
        }

        .timeline-title {
            font-size: 10pt;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 3px;
        }

        .timeline-subtitle {
            font-size: 9pt;
            color: #7f8c8d;
            margin-bottom: 2px;
        }

        .timeline-date {
            font-size: 8pt;
            color: #95a5a6;
            font-style: italic;
            margin-bottom: 4px;
        }

        .timeline-description {
            font-size: 9pt;
            color: #555;
            line-height: 1.5;
        }

        .no-print { display: none; }

        @media screen {
            body { background: #f0f0f0; }
            .resume-page {
                max-width: 210mm;
                box-shadow: 0 0 20px rgba(0,0,0,0.1);
                margin: 20px auto;
            }
            .no-print {
                display: block;
                text-align: center;
                padding: 20px;
                max-width: 210mm;
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    <div class="resume-page">
        <div class="resume-container clearfix">
            <div class="sidebar-cell">
                <!-- Profile Photo -->
                <div class="profile-section">
                    @if(!empty($resume->data->personal_info['photo']))
                        @php
                            if (isset($isPdfDownload) && $isPdfDownload) {
                                $photoPath = public_path('storage/' . $resume->data->personal_info['photo']);
                                if (file_exists($photoPath)) {
                                    $imageData = base64_encode(file_get_contents($photoPath));
                                    $imageMime = mime_content_type($photoPath);
                                    $photoUrl = 'data:' . $imageMime . ';base64,' . $imageData;
                                } else {
                                    $photoUrl = null;
                                }
                            } else {
                                $photoUrl = asset('storage/' . $resume->data->personal_info['photo']);
                            }
                        @endphp
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="Profile" class="profile-photo">
                        @endif
                    @endif
                    <div class="name-sidebar">{{ $resume->data->personal_info['name'] ?? 'Your Name' }}</div>
                    <div class="job-title-sidebar">{{ $resume->data->personal_info['job_title'] ?? '' }}</div>
                </div>

                <!-- Contact -->
                <div class="sidebar-section">
                    <div class="sidebar-title">Contact</div>
                    @if(!empty($resume->data->personal_info['phone']))
                    <div class="contact-item">{{ $resume->data->personal_info['phone'] }}</div>
                    @endif
                    @if(!empty($resume->data->personal_info['email']))
                    <div class="contact-item">{{ $resume->data->personal_info['email'] }}</div>
                    @endif
                    @if(!empty($resume->data->personal_info['address']))
                    <div class="contact-item">{{ $resume->data->personal_info['address'] }}</div>
                    @endif
                </div>

                <!-- Skills -->
                @if(!empty($resume->data->skills) && count($resume->data->skills) > 0)
                <div class="sidebar-section">
                    <div class="sidebar-title">Skills</div>
                    @foreach(array_slice($resume->data->skills, 0, 6) as $skill)
                        @php
                            $skillName = is_array($skill) ? ($skill['name'] ?? $skill) : $skill;
                            $skillLevel = is_array($skill) ? ($skill['level'] ?? 80) : 80;
                        @endphp
                        <div class="skill-item">
                            <div class="skill-name">{{ $skillName }}</div>
                            <div class="skill-bar">
                                <div class="skill-fill" style="width: {{ $skillLevel }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif

                <!-- Languages -->
                @if(!empty($resume->data->languages) && count($resume->data->languages) > 0)
                <div class="sidebar-section">
                    <div class="sidebar-title">Languages</div>
                    @foreach(array_slice($resume->data->languages, 0, 4) as $language)
                        <div class="contact-item">{{ $language }}</div>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="main-cell">
                <!-- About Me -->
                @if(!empty($resume->data->professional_summary))
                <div class="section">
                    <div class="section-title">About Me</div>
                    <p class="about-text">{{ \Illuminate\Support\Str::limit($resume->data->professional_summary, 300) }}</p>
                </div>
                @endif

                <!-- Education -->
                @if(!empty($resume->data->education) && count($resume->data->education) > 0)
                <div class="section">
                    <div class="section-title">Education</div>
                    @foreach(array_slice($resume->data->education, 0, 2) as $edu)
                    <div class="timeline-item">
                        <div class="timeline-title">{{ $edu['degree'] ?? '' }}</div>
                        <div class="timeline-subtitle">{{ $edu['institution'] ?? '' }}</div>
                        @if(!empty($edu['graduation_date']))
                        <div class="timeline-date">{{ date('M Y', strtotime($edu['graduation_date'] . '-01')) }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Work Experience -->
                @if(!empty($resume->data->work_experience) && count($resume->data->work_experience) > 0)
                <div class="section">
                    <div class="section-title">Experience</div>
                    @foreach(array_slice($resume->data->work_experience, 0, 2) as $work)
                    <div class="timeline-item">
                        <div class="timeline-title">{{ $work['title'] ?? '' }}</div>
                        <div class="timeline-subtitle">{{ $work['company'] ?? '' }}</div>
                        @if(!empty($work['start_date']))
                        <div class="timeline-date">
                            {{ date('M Y', strtotime($work['start_date'] . '-01')) }} -
                            {{ ($work['current'] ?? false) ? 'Present' : date('M Y', strtotime(($work['end_date'] ?? $work['start_date']) . '-01')) }}
                        </div>
                        @endif
                        @if(!empty($work['description']))
                        <div class="timeline-description">{{ \Illuminate\Support\Str::limit($work['description'], 150) }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Certifications -->
                @if(!empty($resume->data->certifications) && count($resume->data->certifications) > 0)
                <div class="section">
                    <div class="section-title">Certifications</div>
                    @foreach(array_slice($resume->data->certifications, 0, 2) as $cert)
                    <div class="timeline-item">
                        <div class="timeline-title">{{ $cert['name'] ?? '' }}</div>
                        <div class="timeline-subtitle">{{ $cert['issuer'] ?? '' }}</div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    @if(!isset($isPdfDownload) || !$isPdfDownload)
    <div class="no-print">
        <a href="{{ route('account.resume-builder.index') }}" style="padding: 10px 30px; font-size: 16px; margin: 0 10px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">Back</a>
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 16px; margin: 0 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Print</button>
    </div>
    @endif
</body>
</html>
