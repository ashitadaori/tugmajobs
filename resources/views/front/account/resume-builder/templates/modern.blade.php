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

        html,
        body {
            width: 100%;
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 9pt;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 0;
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
            width: 35%;
            height: 297mm;
            background: #F5DAA7;
            color: #2d3436;
            padding: 25px 18px;
            overflow: hidden;
        }

        .main-cell {
            position: absolute;
            top: 0;
            left: 35%;
            width: 65%;
            height: 297mm;
            background: white;
            padding: 25px 22px;
            overflow: hidden;
        }

        /* Sidebar Styles */
        .profile-section {
            text-align: center;
            margin-bottom: 20px;
            padding-top: 10px;
        }

        .profile-photo {
            width: 95px;
            height: 95px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 12px;
            display: block;
            border: 3px solid #d4a574;
        }

        .sidebar-section {
            margin-bottom: 18px;
        }

        .sidebar-title {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #d4a574;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #2d3436;
        }

        .contact-item {
            margin-bottom: 8px;
            font-size: 9pt;
            word-wrap: break-word;
            overflow-wrap: break-word;
            color: #2d3436;
            line-height: 1.5;
        }

        .skill-item,
        .language-item {
            margin-bottom: 7px;
            font-size: 9pt;
            padding-left: 14px;
            position: relative;
            color: #2d3436;
            line-height: 1.4;
        }

        .skill-item:before,
        .language-item:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #d4a574;
            font-size: 11pt;
        }

        .about-sidebar {
            font-size: 9pt;
            line-height: 1.6;
            color: #2d3436;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Main Content Styles */
        .header-section {
            margin-bottom: 22px;
            padding-top: 10px;
        }

        .name {
            font-size: 22pt;
            font-weight: bold;
            color: #2d3436;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        .job-title {
            font-size: 12pt;
            color: #636e72;
            font-weight: 300;
        }

        .section {
            margin-bottom: 18px;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #2d3436;
            margin-bottom: 10px;
            padding-bottom: 4px;
            border-bottom: 2px solid #2d3436;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .timeline-item {
            margin-bottom: 14px;
            padding-left: 12px;
            border-left: 2px solid #d4a574;
        }

        .timeline-title {
            font-size: 10pt;
            font-weight: bold;
            color: #2d3436;
            margin-bottom: 3px;
        }

        .timeline-company {
            font-size: 9pt;
            color: #636e72;
            margin-bottom: 2px;
        }

        .timeline-date {
            font-size: 8pt;
            color: #b2bec3;
            font-style: italic;
            margin-bottom: 4px;
        }

        .timeline-description {
            font-size: 9pt;
            color: #555;
            line-height: 1.5;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: pre-line;
        }

        .no-print {
            display: none;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        @media screen {
            body {
                background: white;
                display: flex;
                justify-content: center;
                overflow-y: auto;
            }

            .resume-page {
                max-width: 210mm;
                /* box-shadow: 0 0 20px rgba(0,0,0,0.1); */
                margin: 0 auto;
            }

            .no-print {
                display: none;
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
                </div>

                <!-- About Me -->
                @if(!empty($resume->data->professional_summary))
                    <div class="sidebar-section">
                        <div class="sidebar-title">About Me</div>
                        <p class="about-sidebar">
                            {{ \Illuminate\Support\Str::limit($resume->data->professional_summary, 250) }}</p>
                    </div>
                @endif

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
                        @foreach(array_slice($resume->data->skills, 0, 8) as $skill)
                            <div class="skill-item">{{ is_array($skill) ? ($skill['name'] ?? $skill) : $skill }}</div>
                        @endforeach
                    </div>
                @endif

                <!-- Languages -->
                @if(!empty($resume->data->languages) && count($resume->data->languages) > 0)
                    <div class="sidebar-section">
                        <div class="sidebar-title">Languages</div>
                        @foreach(array_slice($resume->data->languages, 0, 4) as $language)
                            <div class="language-item">{{ $language }}</div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="main-cell">
                <!-- Header -->
                <div class="header-section">
                    <div class="name">{{ $resume->data->personal_info['name'] ?? 'Your Name' }}</div>
                    <div class="job-title">{{ $resume->data->personal_info['job_title'] ?? '' }}</div>
                </div>

                <!-- Education -->
                @if(!empty($resume->data->education) && count($resume->data->education) > 0)
                    <div class="section">
                        <div class="section-title">Education</div>
                        @foreach(array_slice($resume->data->education, 0, 2) as $edu)
                            <div class="timeline-item">
                                <div class="timeline-title">{{ $edu['degree'] ?? '' }}</div>
                                <div class="timeline-company">{{ $edu['institution'] ?? '' }}</div>
                                @if(!empty($edu['graduation_date']))
                                    <div class="timeline-date">{{ date('M Y', strtotime($edu['graduation_date'] . '-01')) }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Experience -->
                @if(!empty($resume->data->work_experience) && count($resume->data->work_experience) > 0)
                    <div class="section">
                        <div class="section-title">Experience</div>
                        @foreach(array_slice($resume->data->work_experience, 0, 2) as $work)
                            <div class="timeline-item">
                                <div class="timeline-title">{{ $work['title'] ?? '' }}</div>
                                <div class="timeline-company">{{ $work['company'] ?? '' }}</div>
                                @if(!empty($work['start_date']))
                                    <div class="timeline-date">
                                        {{ date('M Y', strtotime($work['start_date'] . '-01')) }} -
                                        {{ ($work['current'] ?? false) ? 'Present' : date('M Y', strtotime(($work['end_date'] ?? $work['start_date']) . '-01')) }}
                                    </div>
                                @endif
                                @if(!empty($work['description']))
                                    <div class="timeline-description">
                                        {{ \Illuminate\Support\Str::limit($work['description'], 150) }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Projects -->
                @if(!empty($resume->data->projects) && count($resume->data->projects) > 0)
                    <div class="section">
                        <div class="section-title">Projects</div>
                        @foreach(array_slice($resume->data->projects, 0, 2) as $project)
                            <div class="timeline-item">
                                <div class="timeline-title">{{ $project['name'] ?? '' }}</div>
                                @if(!empty($project['technologies']))
                                    <div class="timeline-company">{{ $project['technologies'] }}</div>
                                @endif
                                @if(!empty($project['description']))
                                    <div class="timeline-description">
                                        {{ \Illuminate\Support\Str::limit($project['description'], 100) }}</div>
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
                                <div class="timeline-company">{{ $cert['issuer'] ?? '' }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if(!isset($isPdfDownload) || !$isPdfDownload)
        <div class="no-print">
            <a href="{{ route('account.resume-builder.index') }}"
                style="padding: 10px 30px; font-size: 16px; margin: 0 10px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">Back</a>
            <button onclick="window.print()"
                style="padding: 10px 30px; font-size: 16px; margin: 0 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Print</button>
        </div>
    @endif
</body>

</html>