<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>New Job Application</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        .detail-box {
            background: white;
            padding: 15px;
            margin: 15px 0;
            border-left: 4px solid #3b82f6;
            border-radius: 4px;
        }
        .detail-label {
            font-weight: bold;
            color: #6b7280;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .detail-value {
            color: #111827;
            font-size: 16px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0; font-size: 24px;">🎉 New Job Application Received!</h1>
    </div>
    
    <div class="content">
        <p>Hello <strong><?php echo e($mailData['employer_name']); ?></strong>,</p>
        
        <p>Great news! You have received a new application for your job posting.</p>
        
        <div class="detail-box">
            <div class="detail-label">Job Position</div>
            <div class="detail-value"><?php echo e($mailData['job_title']); ?></div>
        </div>
        
        <div class="detail-box">
            <div class="detail-label">Applicant Name</div>
            <div class="detail-value"><?php echo e($mailData['applicant_name']); ?></div>
        </div>
        
        <div class="detail-box">
            <div class="detail-label">Applicant Email</div>
            <div class="detail-value"><?php echo e($mailData['applicant_email']); ?></div>
        </div>
        
        <p style="margin-top: 30px;">
            <strong>Next Steps:</strong><br>
            Log in to your employer dashboard to review the application, view the candidate's resume, and take action.
        </p>
        
        <div class="footer">
            <p>This is an automated notification from your Job Portal.</p>
            <p>Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
<?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/email/job-notification-email.blade.php ENDPATH**/ ?>