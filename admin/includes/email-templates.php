<?php
/**
 * Dynamic Email Templates for NGO/Trust Organizations
 * Professional HTML email templates with dynamic organization details
 */

if (!function_exists('getRegistrationEmailTemplate')) {
    function getRegistrationEmailTemplate($userData) {
        $siteConfig = getSiteConfig();
        $logoUrl = SITE_URL . '/img/logo.png';
        $organizationName = defined('ORGANIZATION_NAME') ? ORGANIZATION_NAME : $siteConfig['organization_name'];
        $organizationNameHindi = defined('ORGANIZATION_NAME_HINDI') ? ORGANIZATION_NAME_HINDI : $siteConfig['organization_name_hindi'];
        
        $registrationId = $userData['registration_id'] ?? 'N/A';
        $name = htmlspecialchars($userData['name'] ?? '');
        $email = htmlspecialchars($userData['email'] ?? '');
        $membershipType = htmlspecialchars(ucfirst(str_replace('_', ' ', $userData['membership_type'] ?? '')));
        $status = htmlspecialchars(ucfirst($userData['status'] ?? ''));
        $createdDate = date('d M Y, h:i A', strtotime($userData['created_at'] ?? 'now'));
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Confirmation - $organizationName</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #25313e 0%, #7acf6e 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header img {
            max-width: 80px;
            margin-bottom: 15px;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .greeting strong {
            color: #25313e;
        }
        .info-box {
            background-color: #f9f9f9;
            border-left: 4px solid #7acf6e;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            color: #25313e;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #555;
            min-width: 150px;
        }
        .info-value {
            color: #333;
            text-align: right;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-approved {
            background-color: #d4edda;
            color: #155724;
        }
        .next-steps {
            background-color: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .next-steps h3 {
            margin: 0 0 10px 0;
            color: #1565c0;
            font-size: 14px;
            text-transform: uppercase;
        }
        .next-steps ul {
            margin: 0;
            padding-left: 20px;
            font-size: 14px;
        }
        .next-steps li {
            margin: 8px 0;
            color: #333;
        }
        .email-footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
        }
        .footer-links {
            margin: 10px 0;
        }
        .footer-links a {
            color: #7acf6e;
            text-decoration: none;
            margin: 0 10px;
        }
        .divider {
            height: 1px;
            background-color: #eee;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="$logoUrl" alt="$organizationName Logo">
            <h1>$organizationName</h1>
            <p>Registration Confirmation</p>
        </div>
        
        <div class="email-body">
            <div class="greeting">
                Dear <strong>$name</strong>,
            </div>
            
            <p>Thank you for registering with <strong>$organizationName</strong>. We are delighted to have you as a member of our community.</p>
            
            <div class="info-box">
                <h3>Registration Details</h3>
                <div class="info-row">
                    <span class="info-label">Registration ID:</span>
                    <span class="info-value"><strong>$registrationId</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">$name</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">$email</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Membership Type:</span>
                    <span class="info-value">$membershipType</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <span class="status-badge status-$status">$status</span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Registration Date:</span>
                    <span class="info-value">$createdDate</span>
                </div>
            </div>
            
            <div class="next-steps">
                <h3>Next Steps</h3>
                <ul>
                    <li>Your registration has been received and is being processed</li>
                    <li>Our team will review your application within 24-48 hours</li>
                    <li>You will receive a confirmation email once your registration is approved</li>
                    <li>After approval, you can download your ID Card using your Registration ID</li>
                </ul>
            </div>
            
            <p>We believe that your participation will not only benefit yourself but also contribute towards the larger goal of our organization's mission. We look forward to working with you in achieving our shared vision.</p>
            
            <div class="divider"></div>
            
            <p style="font-size: 14px; color: #666;">
                If you have any questions or concerns, please don't hesitate to reach out to us at 
                <strong>{$siteConfig['email']}</strong> or call us at <strong>{$siteConfig['phone1']}</strong>.
            </p>
            
            <p style="margin-top: 30px; font-size: 14px;">
                Best regards,<br>
                <strong>$organizationName Team</strong>
            </p>
        </div>
        
        <div class="email-footer">
            <p style="margin: 0 0 10px 0;">
                <strong>$organizationName</strong><br>
                {$siteConfig['address']}
            </p>
            <div class="footer-links">
                <a href="{$siteConfig['website_url']}">Website</a>
                <a href="mailto:{$siteConfig['email']}">Email</a>
                <a href="tel:{$siteConfig['phone1']}">Phone</a>
            </div>
            <p style="margin: 10px 0 0 0; font-size: 11px; color: #999;">
                © 2025 $organizationName. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
HTML;
        return $html;
    }
}

if (!function_exists('getApprovalEmailTemplate')) {
    function getApprovalEmailTemplate($userData) {
        $siteConfig = getSiteConfig();
        $logoUrl = SITE_URL . '/img/logo.png';
        $organizationName = defined('ORGANIZATION_NAME') ? ORGANIZATION_NAME : $siteConfig['organization_name'];
        
        $registrationId = $userData['registration_id'] ?? 'N/A';
        $name = htmlspecialchars($userData['name'] ?? '');
        $email = htmlspecialchars($userData['email'] ?? '');
        $membershipType = htmlspecialchars(ucfirst(str_replace('_', ' ', $userData['membership_type'] ?? '')));
        $approvalDate = date('d M Y, h:i A');
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Approved - $organizationName</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #25313e 0%, #7acf6e 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header img {
            max-width: 80px;
            margin-bottom: 15px;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .greeting strong {
            color: #25313e;
        }
        .success-banner {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            text-align: center;
        }
        .success-banner h2 {
            margin: 0;
            color: #155724;
            font-size: 18px;
        }
        .info-box {
            background-color: #f9f9f9;
            border-left: 4px solid #7acf6e;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box h3 {
            margin: 0 0 10px 0;
            color: #25313e;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #555;
            min-width: 150px;
        }
        .info-value {
            color: #333;
            text-align: right;
        }
        .next-steps {
            background-color: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .next-steps h3 {
            margin: 0 0 10px 0;
            color: #1565c0;
            font-size: 14px;
            text-transform: uppercase;
        }
        .next-steps ul {
            margin: 0;
            padding-left: 20px;
            font-size: 14px;
        }
        .next-steps li {
            margin: 8px 0;
            color: #333;
        }
        .email-footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
        }
        .footer-links {
            margin: 10px 0;
        }
        .footer-links a {
            color: #7acf6e;
            text-decoration: none;
            margin: 0 10px;
        }
        .divider {
            height: 1px;
            background-color: #eee;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="$logoUrl" alt="$organizationName Logo">
            <h1>$organizationName</h1>
            <p>Registration Approved!</p>
        </div>
        
        <div class="email-body">
            <div class="greeting">
                Dear <strong>$name</strong>,
            </div>
            
            <div class="success-banner">
                <h2>✓ Your Registration Has Been Approved!</h2>
            </div>
            
            <p>Congratulations! Your registration with <strong>$organizationName</strong> has been successfully approved. We are excited to have you as an active member of our community.</p>
            
            <div class="info-box">
                <h3>Approval Details</h3>
                <div class="info-row">
                    <span class="info-label">Registration ID:</span>
                    <span class="info-value"><strong>$registrationId</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">$name</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Membership Type:</span>
                    <span class="info-value">$membershipType</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Approval Date:</span>
                    <span class="info-value">$approvalDate</span>
                </div>
            </div>
            
            <div class="next-steps">
                <h3>What's Next?</h3>
                <ul>
                    <li>Log in to your account using your email and password</li>
                    <li>Download your ID Card using your Registration ID: <strong>$registrationId</strong></li>
                    <li>Access member-only resources and benefits</li>
                    <li>Connect with other members in our community</li>
                </ul>
            </div>
            
            <p>Your membership is now active and you can start enjoying all the benefits of being part of <strong>$organizationName</strong>. We are committed to supporting our members and working towards our shared goals.</p>
            
            <div class="divider"></div>
            
            <p style="font-size: 14px; color: #666;">
                If you have any questions or need assistance, please don't hesitate to reach out to us at 
                <strong>{$siteConfig['email']}</strong> or call us at <strong>{$siteConfig['phone1']}</strong>.
            </p>
            
            <p style="margin-top: 30px; font-size: 14px;">
                Best regards,<br>
                <strong>$organizationName Team</strong>
            </p>
        </div>
        
        <div class="email-footer">
            <p style="margin: 0 0 10px 0;">
                <strong>$organizationName</strong><br>
                {$siteConfig['address']}
            </p>
            <div class="footer-links">
                <a href="{$siteConfig['website_url']}">Website</a>
                <a href="mailto:{$siteConfig['email']}">Email</a>
                <a href="tel:{$siteConfig['phone1']}">Phone</a>
            </div>
            <p style="margin: 10px 0 0 0; font-size: 11px; color: #999;">
                © 2025 $organizationName. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
HTML;
        return $html;
    }
}

if (!function_exists('getRejectionEmailTemplate')) {
    function getRejectionEmailTemplate($userData, $reason = '') {
        $siteConfig = getSiteConfig();
        $logoUrl = SITE_URL . '/img/logo.png';
        $organizationName = defined('ORGANIZATION_NAME') ? ORGANIZATION_NAME : $siteConfig['organization_name'];
        
        $registrationId = $userData['registration_id'] ?? 'N/A';
        $name = htmlspecialchars($userData['name'] ?? '');
        $rejectionDate = date('d M Y, h:i A');
        $reasonText = !empty($reason) ? htmlspecialchars($reason) : 'Your application did not meet our current membership criteria.';
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Status Update - $organizationName</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #25313e 0%, #7acf6e 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header img {
            max-width: 80px;
            margin-bottom: 15px;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-body {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .info-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .email-footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
        }
        .footer-links {
            margin: 10px 0;
        }
        .footer-links a {
            color: #7acf6e;
            text-decoration: none;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="$logoUrl" alt="$organizationName Logo">
            <h1>$organizationName</h1>
        </div>
        
        <div class="email-body">
            <div class="greeting">
                Dear <strong>$name</strong>,
            </div>
            
            <p>Thank you for your interest in <strong>$organizationName</strong>.</p>
            
            <div class="info-box">
                <p>After careful review, we regret to inform you that we are unable to approve your membership application at this time.</p>
                <p><strong>Reason:</strong> $reasonText</p>
            </div>
            
            <p>We appreciate your interest and encourage you to reapply in the future. If you have any questions or would like more information, please feel free to contact us.</p>
            
            <p style="font-size: 14px; color: #666; margin-top: 30px;">
                For any queries, please contact us at 
                <strong>{$siteConfig['email']}</strong> or call us at <strong>{$siteConfig['phone1']}</strong>.
            </p>
            
            <p style="margin-top: 30px; font-size: 14px;">
                Best regards,<br>
                <strong>$organizationName Team</strong>
            </p>
        </div>
        
        <div class="email-footer">
            <p style="margin: 0 0 10px 0;">
                <strong>$organizationName</strong><br>
                {$siteConfig['address']}
            </p>
            <div class="footer-links">
                <a href="{$siteConfig['website_url']}">Website</a>
                <a href="mailto:{$siteConfig['email']}">Email</a>
                <a href="tel:{$siteConfig['phone1']}">Phone</a>
            </div>
            <p style="margin: 10px 0 0 0; font-size: 11px; color: #999;">
                © 2025 $organizationName. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
HTML;
        return $html;
    }
}

if (!function_exists('getBulkEmailTemplate')) {
    function getBulkEmailTemplate($subject, $body, $recipientName = '') {
        $siteConfig = getSiteConfig();
        $logoUrl = SITE_URL . '/img/logo.png';
        $organizationName = defined('ORGANIZATION_NAME') ? ORGANIZATION_NAME : $siteConfig['organization_name'];
        
        $greeting = !empty($recipientName) ? "Dear <strong>" . htmlspecialchars($recipientName) . "</strong>," : "Dear Member,";
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>$subject</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #25313e 0%, #7acf6e 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header img {
            max-width: 80px;
            margin-bottom: 15px;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-body {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .email-footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #eee;
        }
        .footer-links {
            margin: 10px 0;
        }
        .footer-links a {
            color: #7acf6e;
            text-decoration: none;
            margin: 0 10px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="$logoUrl" alt="$organizationName Logo">
            <h1>$organizationName</h1>
        </div>
        
        <div class="email-body">
            <div class="greeting">$greeting</div>
            $body
        </div>
        
        <div class="email-footer">
            <p style="margin: 0 0 10px 0;">
                <strong>$organizationName</strong><br>
                {$siteConfig['address']}
            </p>
            <div class="footer-links">
                <a href="{$siteConfig['website_url']}">Website</a>
                <a href="mailto:{$siteConfig['email']}">Email</a>
                <a href="tel:{$siteConfig['phone1']}">Phone</a>
            </div>
            <p style="margin: 10px 0 0 0; font-size: 11px; color: #999;">
                © 2025 $organizationName. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
HTML;
        return $html;
    }
}
?>