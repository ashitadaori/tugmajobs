<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($resume->title); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', 'Helvetica', sans-serif; font-size: 10pt; line-height: 1.5; color: #333; background: #f0f0f0 !important; }
        
        .professional-template {
            width: 210mm;
            margin: 0 auto;
            background: white;
            overflow: hidden;
        }
        
        .professional-template table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        
        .professional-template td {
            vertical-align: top;
            padding: 0;
        }
        
        /* Sidebar Styles */
        .sidebar {
            background: #2c3e50;
            color: white;
            padding: 25px 20px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
        }
        
        .profile-section {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .profile-photo {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 15px;
            display: block;
            border: 4px solid #34495e;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        
        .sidebar-section {
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .sidebar-title {
            font-size: 11pt;
            font-weight: 600;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 2px solid #34495e;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            display: flex;
            align-items: center;
            color: white;
        }
        
        .sidebar-title svg {
            width: 14px;
            height: 14px;
            margin-right: 8px;
            fill: white;
        }
        
        .contact-item {
            margin-bottom: 8px;
            font-size: 8.5pt;
            display: flex;
            align-items: flex-start;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .contact-item span {
            word-wrap: break-word;
            overflow-wrap: break-word;
            max-width: 100%;
        }
        
        .contact-item svg {
            width: 12px;
            height: 12px;
            margin-right: 8px;
            flex-shrink: 0;
            margin-top: 2px;
            fill: white;
        }
        
        .skill-item, .language-item {
            margin-bottom: 7px;
            font-size: 8.5pt;
            padding-left: 13px;
            position: relative;
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
        }
        
        .skill-item:before, .language-item:before {
            content: '▪';
            position: absolute;
            left: 0;
            color: #3498db;
        }
        
        /* Main Content Styles */
        .main-content {
            padding: 25px 28px;
            background: white;
        }
        
        .header-section {
            margin-bottom: 18px;
        }
        
        .name {
            font-size: 26pt;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }
        
        .job-title {
            font-size: 12pt;
            color: #7f8c8d;
            font-weight: 300;
            margin-bottom: 15px;
        }
        
        .section {
            margin-bottom: 18px;
        }
        
        .section-title {
            font-size: 13pt;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #2c3e50;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            display: flex;
            align-items: center;
        }
        
        .section-title svg {
            width: 16px;
            height: 16px;
            margin-right: 10px;
        }
        
        .about-text {
            font-size: 9.5pt;
            line-height: 1.5;
            color: #555;
            text-align: justify;
        }
        
        /* Timeline Styles */
        .timeline {
            position: relative;
            padding-left: 20px;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 14px;
            padding-bottom: 10px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -16px;
            top: 5px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #2c3e50;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #2c3e50;
        }
        
        .timeline-header {
            margin-bottom: 4px;
        }
        
        .timeline-title {
            font-size: 10pt;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .timeline-company {
            font-size: 9.5pt;
            color: #7f8c8d;
            font-weight: 600;
        }
        
        .timeline-date {
            font-size: 8.5pt;
            color: #95a5a6;
            font-style: italic;
            margin-bottom: 5px;
        }
        
        .timeline-description {
            font-size: 9pt;
            color: #555;
            line-height: 1.4;
        }
        
        .timeline-description ul {
            margin-left: 15px;
            margin-top: 5px;
        }
        
        .timeline-description li {
            margin-bottom: 3px;
        }
        
        @media print {
            body { background: white; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="professional-template">
        <table>
            <tr>
                <td style="width: 35%; background: #2c3e50;">
                    <div class="sidebar">
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
                                    <img src="<?php echo e($photoUrl); ?>" alt="<?php echo e($resume->data->personal_info['name'] ?? 'Profile'); ?>" class="profile-photo">
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        
                        <!-- About Me -->
                        <?php if(!empty($resume->data->professional_summary)): ?>
                        <div class="sidebar-section">
                            <div class="sidebar-title">
                                <svg fill="white" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                                About Me
                            </div>
                            <p style="font-size: 9pt; line-height: 1.6; color: white;"><?php echo e($resume->data->professional_summary); ?></p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Contact -->
                        <div class="sidebar-section">
                            <div class="sidebar-title">
                                <svg fill="white" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                                Contact
                            </div>
                            
                            <?php if(!empty($resume->data->personal_info['phone'])): ?>
                            <div class="contact-item">
                                <svg fill="white" viewBox="0 0 24 24"><path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                                <span><?php echo e($resume->data->personal_info['phone']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($resume->data->personal_info['email'])): ?>
                            <div class="contact-item">
                                <svg fill="white" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                                <span><?php echo e($resume->data->personal_info['email']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($resume->data->personal_info['address'])): ?>
                            <div class="contact-item">
                                <svg fill="white" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                <span><?php echo e($resume->data->personal_info['address']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if(!empty($resume->data->personal_info['website'])): ?>
                            <div class="contact-item">
                                <svg fill="white" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm6.93 6h-2.95c-.32-1.25-.78-2.45-1.38-3.56 1.84.63 3.37 1.91 4.33 3.56zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14C4.1 13.36 4 12.69 4 12s.1-1.36.26-2h3.38c-.08.66-.14 1.32-.14 2 0 .68.06 1.34.14 2H4.26zm.82 2h2.95c.32 1.25.78 2.45 1.38 3.56-1.84-.63-3.37-1.9-4.33-3.56zm2.95-8H5.08c.96-1.66 2.49-2.93 4.33-3.56C8.81 5.55 8.35 6.75 8.03 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82c-.43 1.43-1.08 2.76-1.91 3.96zM14.34 14H9.66c-.09-.66-.16-1.32-.16-2 0-.68.07-1.35.16-2h4.68c.09.65.16 1.32.16 2 0 .68-.07 1.34-.16 2zm.25 5.56c.6-1.11 1.06-2.31 1.38-3.56h2.95c-.96 1.65-2.49 2.93-4.33 3.56zM16.36 14c.08-.66.14-1.32.14-2 0-.68-.06-1.34-.14-2h3.38c.16.64.26 1.31.26 2s-.1 1.36-.26 2h-3.38z"/></svg>
                                <span><?php echo e($resume->data->personal_info['website']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Skills -->
                        <?php if(!empty($resume->data->skills) && count($resume->data->skills) > 0): ?>
                        <div class="sidebar-section">
                            <div class="sidebar-title">
                                <svg fill="white" viewBox="0 0 24 24"><path d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/></svg>
                                Skills
                            </div>
                            <?php $__currentLoopData = $resume->data->skills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="skill-item"><?php echo e(is_array($skill) ? ($skill['name'] ?? $skill) : $skill); ?></div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Languages -->
                        <?php if(!empty($resume->data->languages) && count($resume->data->languages) > 0): ?>
                        <div class="sidebar-section">
                            <div class="sidebar-title">
                                <svg fill="white" viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zm6.93 6h-2.95c-.32-1.25-.78-2.45-1.38-3.56 1.84.63 3.37 1.91 4.33 3.56zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14C4.1 13.36 4 12.69 4 12s.1-1.36.26-2h3.38c-.08.66-.14 1.32-.14 2 0 .68.06 1.34.14 2H4.26zm.82 2h2.95c.32 1.25.78 2.45 1.38 3.56-1.84-.63-3.37-1.9-4.33-3.56zm2.95-8H5.08c.96-1.66 2.49-2.93 4.33-3.56C8.81 5.55 8.35 6.75 8.03 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82c-.43 1.43-1.08 2.76-1.91 3.96zM14.34 14H9.66c-.09-.66-.16-1.32-.16-2 0-.68.07-1.35.16-2h4.68c.09.65.16 1.32.16 2 0 .68-.07 1.34-.16 2zm.25 5.56c.6-1.11 1.06-2.31 1.38-3.56h2.95c-.96 1.65-2.49 2.93-4.33 3.56zM16.36 14c.08-.66.14-1.32.14-2 0-.68-.06-1.34-.14-2h3.38c.16.64.26 1.31.26 2s-.1 1.36-.26 2h-3.38z"/></svg>
                                Language
                            </div>
                            <?php $__currentLoopData = $resume->data->languages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $language): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="language-item"><?php echo e($language); ?></div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </td>
                
                <td style="width: 65%; background: white;">
                    <div class="main-content">
                        <!-- Header -->
                        <div class="header-section">
                            <div class="name"><?php echo e($resume->data->personal_info['name'] ?? 'Your Name'); ?></div>
                            <div class="job-title"><?php echo e($resume->data->personal_info['job_title'] ?? 'Professional Title'); ?></div>
                        </div>
                        
                        <!-- Education -->
                        <?php if(!empty($resume->data->education) && count($resume->data->education) > 0): ?>
                        <div class="section">
                            <div class="section-title">
                                <svg fill="#2c3e50" viewBox="0 0 24 24"><path d="M5 13.18v4L12 21l7-3.82v-4L12 17l-7-3.82zM12 3L1 9l11 6 9-4.91V17h2V9L12 3z"/></svg>
                                Education
                            </div>
                            <div class="timeline">
                                <?php $__currentLoopData = $resume->data->education; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $edu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="timeline-item">
                                    <div class="timeline-header">
                                        <div class="timeline-date">
                                            <?php if(!empty($edu['graduation_date'])): ?>
                                                (<?php echo e(date('Y', strtotime($edu['graduation_date'] . '-01'))); ?>)
                                            <?php endif; ?>
                                        </div>
                                        <div class="timeline-title"><?php echo e($edu['degree'] ?? ''); ?></div>
                                        <div class="timeline-company"><?php echo e($edu['institution'] ?? ''); ?><?php if(!empty($edu['location'])): ?>, <?php echo e($edu['location']); ?><?php endif; ?></div>
                                    </div>
                                    <?php if(!empty($edu['gpa'])): ?>
                                        <div style="font-size: 9pt; color: #7f8c8d; margin-top: 3px;">GPA: <?php echo e($edu['gpa']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Experience -->
                        <?php if(!empty($resume->data->work_experience) && count($resume->data->work_experience) > 0): ?>
                        <div class="section">
                            <div class="section-title">
                                <svg fill="#2c3e50" viewBox="0 0 24 24"><path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/></svg>
                                Experience
                            </div>
                            <div class="timeline">
                                <?php $__currentLoopData = $resume->data->work_experience; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $work): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="timeline-item">
                                    <div class="timeline-header">
                                        <div class="timeline-date">
                                            <?php if(!empty($work['start_date'])): ?>
                                                (<?php echo e(date('Y', strtotime($work['start_date'] . '-01'))); ?> - <?php echo e(($work['current'] ?? false) ? 'Present' : date('Y', strtotime(($work['end_date'] ?? $work['start_date']) . '-01'))); ?>)
                                            <?php endif; ?>
                                        </div>
                                        <div class="timeline-title"><?php echo e($work['title'] ?? ''); ?></div>
                                        <div class="timeline-company"><?php echo e($work['company'] ?? ''); ?><?php if(!empty($work['location'])): ?>, <?php echo e($work['location']); ?><?php endif; ?></div>
                                    </div>
                                    <?php if(!empty($work['description'])): ?>
                                        <div class="timeline-description">
                                            <?php if(str_contains($work['description'], "\n")): ?>
                                                <ul>
                                                    <?php $__currentLoopData = explode("\n", $work['description']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php if(trim($line)): ?>
                                                            <li><?php echo e(trim($line)); ?></li>
                                                        <?php endif; ?>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </ul>
                                            <?php else: ?>
                                                <?php echo e($work['description']); ?>

                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Projects -->
                        <?php if(!empty($resume->data->projects) && count($resume->data->projects) > 0): ?>
                        <div class="section">
                            <div class="section-title">
                                <svg fill="#2c3e50" viewBox="0 0 24 24"><path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                                Projects
                            </div>
                            <div class="timeline">
                                <?php $__currentLoopData = $resume->data->projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="timeline-item">
                                    <div class="timeline-header">
                                        <div class="timeline-title"><?php echo e($project['name'] ?? ''); ?></div>
                                        <?php if(!empty($project['technologies'])): ?>
                                            <div class="timeline-company"><?php echo e($project['technologies']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if(!empty($project['description'])): ?>
                                        <div class="timeline-description"><?php echo e($project['description']); ?></div>
                                    <?php endif; ?>
                                    <?php if(!empty($project['link'])): ?>
                                        <div style="font-size: 9pt; color: #3498db; margin-top: 5px;"><?php echo e($project['link']); ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Certifications -->
                        <?php if(!empty($resume->data->certifications) && count($resume->data->certifications) > 0): ?>
                        <div class="section">
                            <div class="section-title">
                                <svg fill="#2c3e50" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                                Certifications
                            </div>
                            <div class="timeline">
                                <?php $__currentLoopData = $resume->data->certifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="timeline-item">
                                    <div class="timeline-header">
                                        <div class="timeline-title"><?php echo e($cert['name'] ?? ''); ?></div>
                                        <div class="timeline-company"><?php echo e($cert['issuer'] ?? ''); ?></div>
                                        <?php if(!empty($cert['date'])): ?>
                                            <div class="timeline-date"><?php echo e(date('M Y', strtotime($cert['date'] . '-01'))); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
    <?php if(!isset($isPdfDownload) || !$isPdfDownload): ?>
    <div class="no-print" style="text-align: center; margin-top: 20px; padding: 20px; background: white;">
        <a href="<?php echo e(route('account.resume-builder.index')); ?>" style="padding: 10px 30px; font-size: 16px; cursor: pointer; margin: 0 10px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">Back to Resumes</a>
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 16px; cursor: pointer; margin: 0 10px; background: #007bff; color: white; border: none; border-radius: 4px;">Print / Save as PDF</button>
    </div>
    <?php endif; ?>
</body>
</html>
<?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/front/account/resume-builder/templates/professional.blade.php ENDPATH**/ ?>