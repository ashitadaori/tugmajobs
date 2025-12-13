<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($resume->title); ?></title>
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            color: #2d3436;
            line-height: 1.5;
        }

        .skill-item, .language-item {
            margin-bottom: 7px;
            font-size: 9pt;
            padding-left: 14px;
            position: relative;
            color: #2d3436;
            line-height: 1.4;
        }

        .skill-item:before, .language-item:before {
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
        }

        .no-print { display: none; }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

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
                    <?php if(!empty($resume->data->personal_info['photo'])): ?>
                        <?php
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
                        ?>
                        <?php if($photoUrl): ?>
                            <img src="<?php echo e($photoUrl); ?>" alt="Profile" class="profile-photo">
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- About Me -->
                <?php if(!empty($resume->data->professional_summary)): ?>
                <div class="sidebar-section">
                    <div class="sidebar-title">About Me</div>
                    <p class="about-sidebar"><?php echo e(\Illuminate\Support\Str::limit($resume->data->professional_summary, 250)); ?></p>
                </div>
                <?php endif; ?>

                <!-- Contact -->
                <div class="sidebar-section">
                    <div class="sidebar-title">Contact</div>
                    <?php if(!empty($resume->data->personal_info['phone'])): ?>
                    <div class="contact-item"><?php echo e($resume->data->personal_info['phone']); ?></div>
                    <?php endif; ?>
                    <?php if(!empty($resume->data->personal_info['email'])): ?>
                    <div class="contact-item"><?php echo e($resume->data->personal_info['email']); ?></div>
                    <?php endif; ?>
                    <?php if(!empty($resume->data->personal_info['address'])): ?>
                    <div class="contact-item"><?php echo e($resume->data->personal_info['address']); ?></div>
                    <?php endif; ?>
                </div>

                <!-- Skills -->
                <?php if(!empty($resume->data->skills) && count($resume->data->skills) > 0): ?>
                <div class="sidebar-section">
                    <div class="sidebar-title">Skills</div>
                    <?php $__currentLoopData = array_slice($resume->data->skills, 0, 8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="skill-item"><?php echo e(is_array($skill) ? ($skill['name'] ?? $skill) : $skill); ?></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>

                <!-- Languages -->
                <?php if(!empty($resume->data->languages) && count($resume->data->languages) > 0): ?>
                <div class="sidebar-section">
                    <div class="sidebar-title">Languages</div>
                    <?php $__currentLoopData = array_slice($resume->data->languages, 0, 4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="language-item"><?php echo e($language); ?></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="main-cell">
                <!-- Header -->
                <div class="header-section">
                    <div class="name"><?php echo e($resume->data->personal_info['name'] ?? 'Your Name'); ?></div>
                    <div class="job-title"><?php echo e($resume->data->personal_info['job_title'] ?? ''); ?></div>
                </div>

                <!-- Education -->
                <?php if(!empty($resume->data->education) && count($resume->data->education) > 0): ?>
                <div class="section">
                    <div class="section-title">Education</div>
                    <?php $__currentLoopData = array_slice($resume->data->education, 0, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $edu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="timeline-item">
                        <div class="timeline-title"><?php echo e($edu['degree'] ?? ''); ?></div>
                        <div class="timeline-company"><?php echo e($edu['institution'] ?? ''); ?></div>
                        <?php if(!empty($edu['graduation_date'])): ?>
                        <div class="timeline-date"><?php echo e(date('M Y', strtotime($edu['graduation_date'] . '-01'))); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>

                <!-- Experience -->
                <?php if(!empty($resume->data->work_experience) && count($resume->data->work_experience) > 0): ?>
                <div class="section">
                    <div class="section-title">Experience</div>
                    <?php $__currentLoopData = array_slice($resume->data->work_experience, 0, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $work): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="timeline-item">
                        <div class="timeline-title"><?php echo e($work['title'] ?? ''); ?></div>
                        <div class="timeline-company"><?php echo e($work['company'] ?? ''); ?></div>
                        <?php if(!empty($work['start_date'])): ?>
                        <div class="timeline-date">
                            <?php echo e(date('M Y', strtotime($work['start_date'] . '-01'))); ?> -
                            <?php echo e(($work['current'] ?? false) ? 'Present' : date('M Y', strtotime(($work['end_date'] ?? $work['start_date']) . '-01'))); ?>

                        </div>
                        <?php endif; ?>
                        <?php if(!empty($work['description'])): ?>
                        <div class="timeline-description"><?php echo e(\Illuminate\Support\Str::limit($work['description'], 150)); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>

                <!-- Projects -->
                <?php if(!empty($resume->data->projects) && count($resume->data->projects) > 0): ?>
                <div class="section">
                    <div class="section-title">Projects</div>
                    <?php $__currentLoopData = array_slice($resume->data->projects, 0, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="timeline-item">
                        <div class="timeline-title"><?php echo e($project['name'] ?? ''); ?></div>
                        <?php if(!empty($project['technologies'])): ?>
                        <div class="timeline-company"><?php echo e($project['technologies']); ?></div>
                        <?php endif; ?>
                        <?php if(!empty($project['description'])): ?>
                        <div class="timeline-description"><?php echo e(\Illuminate\Support\Str::limit($project['description'], 100)); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>

                <!-- Certifications -->
                <?php if(!empty($resume->data->certifications) && count($resume->data->certifications) > 0): ?>
                <div class="section">
                    <div class="section-title">Certifications</div>
                    <?php $__currentLoopData = array_slice($resume->data->certifications, 0, 2); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="timeline-item">
                        <div class="timeline-title"><?php echo e($cert['name'] ?? ''); ?></div>
                        <div class="timeline-company"><?php echo e($cert['issuer'] ?? ''); ?></div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if(!isset($isPdfDownload) || !$isPdfDownload): ?>
    <div class="no-print">
        <a href="<?php echo e(route('account.resume-builder.index')); ?>" style="padding: 10px 30px; font-size: 16px; margin: 0 10px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">Back</a>
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 16px; margin: 0 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Print</button>
    </div>
    <?php endif; ?>
</body>
</html>
<?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/front/account/resume-builder/templates/modern.blade.php ENDPATH**/ ?>