<!DOCTYPE html>
<html>
<head>
    <title>{{ $resume->title }} - Preview</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; padding: 40px; background: #f5f5f5; }
        .resume-container { max-width: 850px; margin: 0 auto; background: white; padding: 40px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #2c3e50; }
        .header h1 { font-size: 32px; color: #2c3e50; margin-bottom: 10px; }
        .header .contact { font-size: 14px; color: #666; }
        .header .contact span { margin: 0 10px; }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 20px; color: #2c3e50; margin-bottom: 15px; padding-bottom: 5px; border-bottom: 2px solid #3498db; text-transform: uppercase; }
        .summary { font-size: 14px; line-height: 1.8; color: #555; }
        .experience-item, .education-item { margin-bottom: 20px; }
        .experience-item h3, .education-item h3 { font-size: 16px; color: #2c3e50; margin-bottom: 5px; }
        .experience-item .company, .education-item .institution { font-size: 14px; color: #3498db; font-weight: bold; }
        .experience-item .details, .education-item .details { font-size: 13px; color: #777; margin-bottom: 8px; }
        .experience-item .description { font-size: 14px; color: #555; line-height: 1.6; }
        .skills-container { display: flex; flex-wrap: wrap; gap: 10px; }
        .skill-badge { background: #3498db; color: white; padding: 6px 15px; border-radius: 20px; font-size: 13px; }
        .no-print { text-align: center; margin-top: 20px; }
        .no-print button { padding: 10px 30px; font-size: 16px; cursor: pointer; margin: 0 10px; }
        @media print {
            body { padding: 0; background: white; }
            .no-print { display: none; }
            .resume-container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="resume-container">
        <!-- Header -->
        <div class="header">
            <h1>{{ $resume->data->personal_info['name'] ?? 'Your Name' }}</h1>
            <div class="contact">
                <span>{{ $resume->data->personal_info['email'] ?? '' }}</span>
                <span>|</span>
                <span>{{ $resume->data->personal_info['phone'] ?? '' }}</span>
                @if(!empty($resume->data->personal_info['address']))
                <span>|</span>
                <span>{{ $resume->data->personal_info['address'] }}</span>
                @endif
            </div>
        </div>

        <!-- Professional Summary -->
        @if($resume->data->professional_summary)
        <div class="section">
            <div class="section-title">Professional Summary</div>
            <div class="summary">{{ $resume->data->professional_summary }}</div>
        </div>
        @endif

        <!-- Work Experience -->
        @if($resume->data->work_experience && count($resume->data->work_experience) > 0)
        <div class="section">
            <div class="section-title">Work Experience</div>
            @foreach($resume->data->work_experience as $exp)
            <div class="experience-item">
                <h3>{{ $exp['title'] ?? '' }}</h3>
                <div class="company">{{ $exp['company'] ?? '' }}</div>
                <div class="details">
                    {{ $exp['location'] ?? '' }}
                    @if(!empty($exp['start_date']))
                        | {{ date('M Y', strtotime($exp['start_date'] . '-01')) }} - 
                        {{ $exp['current'] ?? false ? 'Present' : (date('M Y', strtotime($exp['end_date'] . '-01'))) }}
                    @endif
                </div>
                @if(!empty($exp['description']))
                <div class="description">{{ $exp['description'] }}</div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        <!-- Education -->
        @if($resume->data->education && count($resume->data->education) > 0)
        <div class="section">
            <div class="section-title">Education</div>
            @foreach($resume->data->education as $edu)
            <div class="education-item">
                <h3>{{ $edu['degree'] ?? '' }}</h3>
                <div class="institution">{{ $edu['institution'] ?? '' }}</div>
                <div class="details">
                    {{ $edu['location'] ?? '' }}
                    @if(!empty($edu['graduation_date']))
                        | Graduated: {{ date('M Y', strtotime($edu['graduation_date'] . '-01')) }}
                    @endif
                    @if(!empty($edu['gpa']))
                        | GPA: {{ $edu['gpa'] }}
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Skills -->
        @if($resume->data->skills && count($resume->data->skills) > 0)
        <div class="section">
            <div class="section-title">Skills</div>
            <div class="skills-container">
                @foreach($resume->data->skills as $skill)
                <span class="skill-badge">{{ $skill }}</span>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Certifications -->
        @if($resume->data->certifications && count($resume->data->certifications) > 0)
        <div class="section">
            <div class="section-title">Certifications</div>
            @foreach($resume->data->certifications as $cert)
            <div class="experience-item">
                <h3>{{ $cert['name'] ?? '' }}</h3>
                <div class="company">{{ $cert['issuer'] ?? '' }}</div>
                <div class="details">
                    @if(!empty($cert['date']))
                        {{ date('M Y', strtotime($cert['date'] . '-01')) }}
                    @endif
                    @if(!empty($cert['credential_id']))
                        | Credential ID: {{ $cert['credential_id'] }}
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Languages -->
        @if($resume->data->languages && count($resume->data->languages) > 0)
        <div class="section">
            <div class="section-title">Languages</div>
            <div class="skills-container">
                @foreach($resume->data->languages as $language)
                <span class="skill-badge">{{ $language }}</span>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Projects -->
        @if($resume->data->projects && count($resume->data->projects) > 0)
        <div class="section">
            <div class="section-title">Projects</div>
            @foreach($resume->data->projects as $project)
            <div class="experience-item">
                <h3>{{ $project['name'] ?? '' }}</h3>
                @if(!empty($project['technologies']))
                <div class="company">Technologies: {{ $project['technologies'] }}</div>
                @endif
                @if(!empty($project['description']))
                <div class="description">{{ $project['description'] }}</div>
                @endif
                @if(!empty($project['link']))
                <div class="details">
                    <a href="{{ $project['link'] }}" target="_blank" style="color: #3498db;">{{ $project['link'] }}</a>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <div class="no-print">
        <button onclick="window.print()">Print / Save as PDF</button>
        <button onclick="window.close()">Close</button>
    </div>
</body>
</html>
