<?php
require_once '../config/config.php';
require_once 'includes/auth_check.php';
require_once 'includes/receipt-generator.php';

// Check user permissions
if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/admin/login.php");
    exit;
}

$pageTitle = 'Donation Management';
$db = getDbConnection();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? 0;

$message = '';
$error = '';

// Handle email resend action (Admin only)
if (isset($_GET['resend_email']) && $_GET['resend_email'] > 0 && isAdmin()) {
    $donation_id = intval($_GET['resend_email']);
    $new_email = $_GET['email'] ?? null;
    
    $result = sendReceiptEmail($donation_id, $new_email);
    if ($result['success']) {
        $message = $result['message'];
    } else {
        $error = $result['message'];
    }
    
    // Redirect to avoid multiple submissions
    header("Location: " . SITE_URL . "/admin/donations.php?action=view&id=" . $donation_id);
    exit;
}

// Handle form submissions (Admin only for create/update/delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                if (!isAdmin()) {
                    $error = 'You do not have permission to perform this action.';
                    break;
                }
                
                try {
                    // Validate required fields
                    $name = trim(sanitizeInput($_POST['name'] ?? ''));
                    $father_name = trim(sanitizeInput($_POST['father_name'] ?? ''));
                    $mobile = trim(sanitizeInput($_POST['mobile'] ?? ''));
                    $email = !empty($_POST['email']) ? trim(sanitizeInput($_POST['email'])) : null;
                    $address = trim(sanitizeInput($_POST['address'] ?? ''));
                    $pan_card = !empty($_POST['pan_card']) ? trim(sanitizeInput($_POST['pan_card'])) : null;
                    $amount = floatval($_POST['amount'] ?? 0);
                    $payment_method = trim(sanitizeInput($_POST['payment_method'] ?? ''));
                    $payment_id = !empty($_POST['payment_id']) ? trim(sanitizeInput($_POST['payment_id'])) : null;
                    $order_id = !empty($_POST['order_id']) ? trim(sanitizeInput($_POST['order_id'])) : null;
                    $status = trim(sanitizeInput($_POST['status'] ?? ''));
                    $user_id = !empty($_POST['user_id']) ? intval($_POST['user_id']) : 0;

                    // Validate inputs
                    $errors = [];
                    if (empty($name)) $errors[] = 'Name is required.';
                    if (empty($father_name)) $errors[] = 'Father\'s name is required.';
                    if (empty($mobile) || !preg_match('/^[0-9]{10,15}$/', $mobile)) $errors[] = 'Valid mobile number is required (10-15 digits).';
                    if (empty($address)) $errors[] = 'Address is required.';
                    if ($amount <= 0) $errors[] = 'Valid amount is required.';
                    if (!in_array($payment_method, ['online', 'offline'])) $errors[] = 'Select valid payment method (online or offline).';
                    if (!in_array($status, ['pending', 'completed', 'failed'])) $errors[] = 'Select valid status (pending, completed, failed).';
                    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter valid email address.';
                    if ($pan_card && !preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan_card)) $errors[] = 'Enter valid PAN card number.';
                    if ($user_id > 0) {
                        $stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        if (!$stmt->fetch()) {
                            $errors[] = 'Invalid user ID.';
                        }
                    }
                    if ($order_id) {
                        $stmt = $db->prepare("SELECT amount, status FROM razorpay_orders WHERE order_id = ?");
                        $stmt->execute([$order_id]);
                        $razorpay_order = $stmt->fetch(PDO::FETCH_ASSOC);
                        if (!$razorpay_order) {
                            $errors[] = 'Invalid order ID.';
                        } elseif ($razorpay_order['amount'] / 100 != $amount) {
                            $errors[] = 'Amount does not match Razorpay order.';
                        } elseif ($payment_id && $razorpay_order['status'] !== 'paid') {
                            $errors[] = 'Razorpay order has not been paid.';
                        }
                    }
                    if ($payment_id && $payment_method === 'offline') {
                        $errors[] = 'Payment ID is not applicable for offline payment.';
                    }
                    if ($payment_method === 'online' && !$order_id && !$payment_id) {
                        $errors[] = 'Order ID or Payment ID is required for online payment.';
                    }

                    if (!empty($errors)) {
                        throw new Exception(implode('<br>', $errors));
                    }

                    // Handle photo upload
                    $photo = null;
                    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = uploadFile($_FILES['photo'], 'img/users');
                        if ($uploadResult['success']) {
                            $photo = $uploadResult['filename'];
                        } else {
                            throw new Exception('Photo upload error: ' . $uploadResult['error']);
                        }
                    }

                    // Handle payment proof upload
                    $payment_proof = null;
                    if (!empty($_FILES['payment_proof']['name']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = uploadFile($_FILES['payment_proof'], 'img/payments');
                        if ($uploadResult['success']) {
                            $payment_proof = $uploadResult['filename'];
                        } else {
                            throw new Exception('Payment proof upload error: ' . $uploadResult['error']);
                        }
                    }

                    // Insert into database
                    $stmt = $db->prepare("
                        INSERT INTO donations (
                            user_id, name, father_name, mobile, email, address, pan_card, amount, 
                            photo, payment_id, order_id, payment_proof, payment_method, status, created_at
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $user_id, $name, $father_name, $mobile, $email, $address, $pan_card, 
                        $amount, $photo, $payment_id, $order_id, $payment_proof, $payment_method, $status
                    ]);
                    
                    $donation_id = $db->lastInsertId();
                    
                    if ($status === 'completed' && $email) {
                        $donationData = [
                            'id' => $donation_id,
                            'name' => $name,
                            'father_name' => $father_name,
                            'mobile' => $mobile,
                            'email' => $email,
                            'address' => $address,
                            'pan_card' => $pan_card,
                            'amount' => $amount,
                            'payment_method' => $payment_method,
                            'payment_id' => $payment_id,
                            'status' => $status,
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                        
                        if (sendDonationConfirmationEmail($donationData)) {
                            $message = 'Donation record added successfully and email sent!';
                        } else {
                            $message = 'Donation record added successfully! (Problem sending email)';
                        }
                    } else {
                        $message = 'Donation record added successfully!';
                    }
                    
                    header("Location: " . SITE_URL . "/admin/donations.php");
                    exit;
                } catch (Exception $e) {
                    $error = 'Error adding donation: ' . $e->getMessage();
                }
                break;

            case 'update':
                if (!isAdmin()) {
                    $error = 'You do not have permission to perform this action.';
                    break;
                }
                
                try {
                    $donation_id = intval($_POST['id']);
                    $name = trim(sanitizeInput($_POST['name']));
                    $father_name = trim(sanitizeInput($_POST['father_name']));
                    $mobile = trim(sanitizeInput($_POST['mobile']));
                    $email = !empty($_POST['email']) ? trim(sanitizeInput($_POST['email'])) : null;
                    $address = trim(sanitizeInput($_POST['address']));
                    $pan_card = !empty($_POST['pan_card']) ? trim(sanitizeInput($_POST['pan_card'])) : null;
                    $amount = floatval($_POST['amount']);
                    $payment_method = trim(sanitizeInput($_POST['payment_method']));
                    $payment_id = !empty($_POST['payment_id']) ? trim(sanitizeInput($_POST['payment_id'])) : null;
                    $order_id = !empty($_POST['order_id']) ? trim(sanitizeInput($_POST['order_id'])) : null;
                    $status = trim(sanitizeInput($_POST['status']));
                    $user_id = !empty($_POST['user_id']) ? intval($_POST['user_id']) : 0;

                    // Validate required fields
                    $errors = [];
                    if (empty($name)) $errors[] = 'Name is required.';
                    if (empty($father_name)) $errors[] = 'Father\'s name is required.';
                    if (empty($mobile) || !preg_match('/^[0-9]{10,15}$/', $mobile)) $errors[] = 'Valid mobile number is required.';
                    if (empty($address)) $errors[] = 'Address is required.';
                    if ($amount <= 0) $errors[] = 'Valid amount is required.';
                    if (!in_array($payment_method, ['online', 'offline'])) $errors[] = 'Select valid payment method.';
                    if (!in_array($status, ['pending', 'completed', 'failed'])) $errors[] = 'Select valid status.';
                    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter valid email address.';
                    if ($pan_card && !preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $pan_card)) $errors[] = 'Enter valid PAN card number.';
                    if ($user_id > 0) {
                        $stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
                        $stmt->execute([$user_id]);
                        if (!$stmt->fetch()) {
                            $errors[] = 'Invalid user ID.';
                        }
                    }
                    if ($order_id) {
                        $stmt = $db->prepare("SELECT amount, status FROM razorpay_orders WHERE order_id = ?");
                        $stmt->execute([$order_id]);
                        $razorpay_order = $stmt->fetch(PDO::FETCH_ASSOC);
                        if (!$razorpay_order) {
                            $errors[] = 'Invalid order ID.';
                        } elseif ($razorpay_order['amount'] / 100 != $amount) {
                            $errors[] = 'Amount does not match Razorpay order.';
                        } elseif ($payment_id && $razorpay_order['status'] !== 'paid') {
                            $errors[] = 'Razorpay order has not been paid.';
                        }
                    }
                    if ($payment_id && $payment_method === 'offline') {
                        $errors[] = 'Payment ID is not applicable for offline payment.';
                    }
                    if ($payment_method === 'online' && !$order_id && !$payment_id) {
                        $errors[] = 'Order ID or Payment ID is required for online payment.';
                    }

                    if (!empty($errors)) {
                        throw new Exception(implode('<br>', $errors));
                    }

                    // Get current data
                    $stmt = $db->prepare("SELECT photo, payment_proof FROM donations WHERE id = ?");
                    $stmt->execute([$donation_id]);
                    $current_data = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$current_data) {
                        throw new Exception('Donation record not found.');
                    }
                    $photo = $current_data['photo'];
                    $payment_proof = $current_data['payment_proof'];

                    // Handle photo upload
                    if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = uploadFile($_FILES['photo'], 'img/users');
                        if ($uploadResult['success']) {
                            // Delete old photo
                            if (!empty($photo) && file_exists('../img/users/' . $photo)) {
                                unlink('../img/users/' . $photo);
                            }
                            $photo = $uploadResult['filename'];
                        } else {
                            throw new Exception('Photo upload error: ' . $uploadResult['error']);
                        }
                    }

                    // Handle payment proof upload
                    if (!empty($_FILES['payment_proof']['name']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
                        $uploadResult = uploadFile($_FILES['payment_proof'], 'img/payments');
                        if ($uploadResult['success']) {
                            // Delete old payment proof
                            if (!empty($payment_proof) && file_exists('../img/payments/' . $payment_proof)) {
                                unlink('../img/payments/' . $payment_proof);
                            }
                            $payment_proof = $uploadResult['filename'];
                        } else {
                            throw new Exception('Payment proof upload error: ' . $uploadResult['error']);
                        }
                    }

                    // Update database
                    $stmt = $db->prepare("
                        UPDATE donations SET 
                            user_id = ?, name = ?, father_name = ?, mobile = ?, email = ?, 
                            address = ?, pan_card = ?, amount = ?, photo = ?, payment_id = ?, 
                            order_id = ?, payment_proof = ?, payment_method = ?, status = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $user_id, $name, $father_name, $mobile, $email, $address, $pan_card, 
                        $amount, $photo, $payment_id, $order_id, $payment_proof, $payment_method, 
                        $status, $donation_id
                    ]);
                    
                    $message = 'Donation record updated successfully!';
                    header("Location: " . SITE_URL . "/admin/donations.php");
                    exit;
                } catch (Exception $e) {
                    $error = 'Error updating donation: ' . $e->getMessage();
                }
                break;
                
            case 'update_status':
                if (!isAdmin()) {
                    $error = 'You do not have permission to perform this action.';
                    break;
                }
                
                try {
                    $donation_id = intval($_POST['id']);
                    $status = trim(sanitizeInput($_POST['status']));
                    
                    if (!in_array($status, ['pending', 'completed', 'failed'])) {
                        throw new Exception('Invalid status.');
                    }
                    
                    $stmt = $db->prepare("SELECT * FROM donations WHERE id = ?");
                    $stmt->execute([$donation_id]);
                    $donationData = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$donationData) {
                        throw new Exception('Donation record not found.');
                    }
                    
                    $stmt = $db->prepare("UPDATE donations SET status = ? WHERE id = ?");
                    $stmt->execute([$status, $donation_id]);
                    
                    if ($status === 'completed' && !empty($donationData['email']) && $donationData['status'] !== 'completed') {
                        $donationData['status'] = $status; // Update status for email
                        if (sendDonationConfirmationEmail($donationData)) {
                            $message = 'Donation status updated and email sent!';
                        } else {
                            $message = 'Donation status updated! (Problem sending email)';
                        }
                    } else {
                        $message = 'Donation status updated!';
                    }
                } catch (Exception $e) {
                    $error = 'Error updating status: ' . $e->getMessage();
                }
                break;
                
            case 'delete':
                if (!isAdmin()) {
                    $error = 'You do not have permission to perform this action.';
                    break;
                }
                
                try {
                    $delete_id = intval($_POST['id']);
                    
                    // Get files before deleting
                    $stmt = $db->prepare("SELECT photo, payment_proof FROM donations WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    $donation = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    // Delete donation
                    $stmt = $db->prepare("DELETE FROM donations WHERE id = ?");
                    $stmt->execute([$delete_id]);
                    
                    // Delete files if they exist
                    if (!empty($donation['photo']) && file_exists('../img/users/' . $donation['photo'])) {
                        unlink('../img/users/' . $donation['photo']);
                    }
                    if (!empty($donation['payment_proof']) && file_exists('../img/payments/' . $donation['payment_proof'])) {
                        unlink('../img/payments/' . $donation['payment_proof']);
                    }
                    
                    $message = 'Donation record deleted successfully!';
                } catch (Exception $e) {
                    $error = 'Error deleting: ' . $e->getMessage();
                }
                break;
        }
    }
}

function sendDonationConfirmationEmail($donationData) {
    if (empty($donationData['email'])) {
        return false; // No email provided
    }
    
    $subject = "Donation Receipt - " . ORGANIZATION_NAME;
    
    // Get site configuration
    $siteConfig = getSiteConfig();
    $orgDetails = [             
        'organization_name' => ORGANIZATION_NAME_HINDI,             
        'organization_name_en' => ORGANIZATION_NAME,             
        'registration_info' => defined('REGISTRATION_INFO') ? REGISTRATION_INFO : '',             
        'email' => $siteConfig['email'] ?? '',             
        'helpline_no' => $siteConfig['phone1'] ?? '',             
        'chairman_name' => CERTIFICATE_CHAIRMAN_NAME,             
        'chairman_title' => CERTIFICATE_CHAIRMAN_TITLE,             
        'address' => $siteConfig['address'] ?? '',             
        'logo_path' => SITE_URL . '/img/logo.png',
        'signature_path' => SITE_URL . '/img/signature.png',
        'seal_path' => SITE_URL . '/img/seal.png',
        'watermark_path' => SITE_URL . '/img/logo.png',
        'website_url' => str_replace(['http://', 'https://'], '', SITE_URL)     
    ];
    
    $receiptNumber = 'GHDAF-' . date('Y') . '-' . str_pad($donationData['id'], 6, '0', STR_PAD_LEFT);
    $amount = $donationData['amount'];
    $donationDate = date('d-m-Y', strtotime($donationData['created_at']));
    $amountInWords = numberToHindiWords($amount);
    $paymentMethod = ucfirst($donationData['payment_method']);
    $status = ucfirst($donationData['status']);
    
    // Create email-optimized HTML receipt
    $emailBody = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Receipt</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            margin: 0; 
            padding: 20px; 
            background-color: #f0f2f5; 
            font-family: Arial, sans-serif; 
            color: #333;
        }
        .receipt-wrapper {
            max-width: 800px; 
            margin: 0 auto; 
            background-color: #fff; 
            border: 1px solid #d1d1d1; 
            padding: 30px;
        }
        .receipt-header {
            width: 100%; 
            border-bottom: 2px solid #333; 
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-logo { 
            width: 15%; 
            text-align: left; 
            vertical-align: top;
        }
        .header-logo img { 
            max-width: 80px; 
            height: auto;
            display: block;
        }
        .header-center { 
            width: 85%; 
            text-align: center; 
            vertical-align: top;
            padding-left: 20px;
        }
        .header-center h2 { 
            margin: 0 0 5px 0; 
            font-size: 28px; 
            font-weight: 700; 
            color: #333; 
        }
        .header-center h5 { 
            margin: 5px 0; 
            font-size: 16px; 
            font-weight: 500; 
            color: #444; 
            text-transform: uppercase; 
        }
        .header-center p { 
            margin: 3px 0; 
            font-size: 13px; 
        }
        .receipt-title { 
            text-align: center; 
            margin: 20px 0; 
            font-size: 24px; 
            font-weight: 600; 
            text-transform: uppercase;
            color: #333;
        }
        .details-table { 
            width: 100%; 
            border-collapse: collapse; 
            border: 1px solid #d1d1d1; 
            font-size: 14px;
            margin-bottom: 20px;
        }
        .details-table td, .details-table th { 
            border: 1px solid #d1d1d1; 
            padding: 10px 12px; 
        }
        .details-table th {
            background: linear-gradient(45deg, #25313e, #7acf6e); 
            color: #fff; 
            font-weight: 600;
            text-align: center;
        }
        .details-table-primary td { 
            text-align: center; 
        }
        .status-success { 
            color: #28a745; 
            font-weight: 600; 
        }
        .gradient-bg { 
            background: linear-gradient(45deg, #25313e, #7acf6e); 
            color: #fff; 
            font-weight: 600; 
        }
        .footer-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        .footer-left { 
            width: 65%; 
            vertical-align: top;
        }
        .footer-right { 
            width: 35%; 
            text-align: center; 
            vertical-align: top;
        }
        .thank-you-msg { 
            text-align: center; 
            font-style: italic; 
            font-size: 18px; 
            margin-top: 20px;
            color: #7acf6e;
            font-weight: 500;
        }
        
        /* SIGNATURE OVERLAPPING SEAL - STAMP EFFECT */
        .signature-line {
            text-align: center;
        }
        .signature-container {
            position: relative;
            display: inline-block;
            margin-bottom: 10px;
            width: 120px;
            height: 100px;
        }
        .seal-img {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            margin: 0 auto;
            max-height: 90px;
            max-width: 90px;
            opacity: 0.6;
            z-index: 1;
        }
        .signature-img {
            position: absolute;
            top: 15px;
            left: -10px;
            max-height: 60px;
            max-width: 140px;
            z-index: 2;
        }
        .signature-text {
            margin-top: 8px;
        }
        .signature-text p { 
            margin: 3px 0; 
            font-size: 13px; 
            font-weight: 500; 
        }
        .tax-info { 
            font-size: 13px; 
            margin-top: 30px; 
            line-height: 1.6;
            padding: 15px;
            background-color: #f8f9fa;
            border-left: 4px solid #7acf6e;
        }
        .tax-info a { 
            color: #7acf6e; 
            text-decoration: none; 
            font-weight: 600; 
        }
        .main-footer {
            max-width: 800px; 
            margin: 20px auto 0;
            background: linear-gradient(45deg, #25313e, #7acf6e);
            color: #fff; 
            padding: 15px 30px; 
            text-align: center; 
            font-size: 13px;
        }
        .main-footer a { 
            color: #fff !important; 
            text-decoration: none; 
            margin: 0 10px;
        }
        .download-section {
            background: linear-gradient(45deg, #25313e, #7acf6e);
            color: #fff; 
            padding: 20px; 
            text-align: center; 
            margin-top: 20px;
            border-radius: 5px;
        }
        .download-section p {
            margin: 0 0 10px 0;
            font-size: 16px;
            color: #fff;
        }
        .download-button {
            display: inline-block;
            background: white; 
            color: #25313e !important; 
            padding: 12px 30px; 
            border-radius: 5px; 
            text-decoration: none !important; 
            font-weight: 600; 
            margin: 10px 10px 0;
        }
    </style>
</head>
<body>
    <div class="receipt-wrapper">
        <div class="receipt-header">
            <table class="header-table">
                <tr>
                    <td class="header-logo">
                        <img src="' . htmlspecialchars($orgDetails['logo_path']) . '" alt="Logo">
                    </td>
                    <td class="header-center">
                        <h2>' . htmlspecialchars($orgDetails['organization_name']) . '</h2>
                        <h5>' . htmlspecialchars($orgDetails['organization_name_en']) . '</h5>
                        <p>' . htmlspecialchars($orgDetails['registration_info']) . '</p>
                        <p>📍 ' . htmlspecialchars($orgDetails['address']) . '</p>
                    </td>
                </tr>
            </table>
        </div>

        <h1 class="receipt-title">Donation Receipt</h1>
        
        <table class="details-table details-table-primary">
            <thead>
                <tr>
                    <th>Receipt No</th>
                    <th>Amount</th>
                    <th>Mode</th>
                    <th>Payment Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>' . htmlspecialchars($receiptNumber) . '</td>
                    <td>₹' . number_format($amount, 2) . '</td>
                    <td>' . htmlspecialchars($paymentMethod) . '</td>
                    <td><span class="status-success">' . $status . '</span></td>
                    <td>' . $donationDate . '</td>
                </tr>
            </tbody>
        </table>
        
        <table class="details-table">
            <tbody>
                <tr>
                    <td class="gradient-bg" style="width: 30%;">Received From</td>
                    <td>' . htmlspecialchars($donationData['name']) . '</td>
                </tr>
                <tr>
                    <td class="gradient-bg">Rupees (in words)</td>
                    <td>' . $amountInWords . ' Rupees Only</td>
                </tr>
                <tr>
                    <td class="gradient-bg">Address</td>
                    <td>' . htmlspecialchars($donationData['address']) . '</td>
                </tr>
                ' . (!empty($donationData['pan_card']) ? '
                <tr>
                    <td class="gradient-bg">PAN Card</td>
                    <td>' . htmlspecialchars($donationData['pan_card']) . '</td>
                </tr>' : '') . '
            </tbody>
        </table>

        <table class="footer-table">
            <tr>
                <td class="footer-left">
                    <h2 class="thank-you-msg">Thank You For Your Generous Contribution</h2>
                </td>
                <td class="footer-right">
                    <div class="signature-line">
                        <div class="signature-container">
                            <img src="' . htmlspecialchars($orgDetails['seal_path']) . '" alt="Seal" class="seal-img">
                            <img src="' . htmlspecialchars($orgDetails['signature_path']) . '" alt="Signature" class="signature-img">
                        </div>
                        <div class="signature-text">
                            <p>' . htmlspecialchars($orgDetails['chairman_name']) . '</p>
                            <p>(' . htmlspecialchars($orgDetails['chairman_title']) . ')</p>
                            <p>Authorised Signatory</p>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="tax-info">
            Donations made to <a href="https://' . htmlspecialchars($orgDetails['website_url']) . '">' . htmlspecialchars($orgDetails['organization_name_en']) . '</a>
            are eligible for the benefit of deduction under Section 80G of the Income Tax Act, 1961.
        </div>

        <div class="download-section">
            <p>To view and print your receipt, click the button below:</p>
            <a href="' . SITE_URL . '/generate_receipt.php?donation_id=' . $donationData['id'] . '&auto_print=1" class="download-button">View & Print Receipt</a>
        </div>
    </div>

    <div class="main-footer">
        <a href="tel:' . htmlspecialchars($orgDetails['helpline_no']) . '">☎ ' . htmlspecialchars($orgDetails['helpline_no']) . '</a> |
        <a href="mailto:' . htmlspecialchars($orgDetails['email']) . '">✉ ' . htmlspecialchars($orgDetails['email']) . '</a> |
        <a href="https://' . htmlspecialchars($orgDetails['website_url']) . '" target="_blank">🌐 ' . htmlspecialchars($orgDetails['website_url']) . '</a>
    </div>
</body>
</html>';

    // Send email using existing sendEmail function
    return sendEmail($donationData['email'], $subject, $emailBody, true);
}
// Enhanced function to send receipt via email with better error handling
function sendReceiptEmail($donationId, $recipientEmail = null) {
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM donations WHERE id = ?");
        $stmt->execute([$donationId]);
        $donationData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$donationData) {
            throw new Exception('Donation record not found.');
        }
        
        // Use provided email or donation email
        $email = $recipientEmail ?: $donationData['email'];
        if (empty($email)) {
            throw new Exception('Email address not available.');
        }
        
        $donationData['email'] = $email; // Ensure email is set for the function
        
        if (sendDonationConfirmationEmail($donationData)) {
            return ['success' => true, 'message' => 'Receipt sent successfully via email.'];
        } else {
            throw new Exception('Problem sending email.');
        }
        
    } catch (Exception $e) {
        logError('Receipt email error: ' . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Function to handle manual email sending (for admin use)
function resendReceiptEmail($donationId, $newEmail = null) {
    if (!isAdmin()) {
        return ['success' => false, 'message' => 'You do not have permission to perform this action.'];
    }
    
    return sendReceiptEmail($donationId, $newEmail);
}

// Get donation data for editing/viewing (restrict to own data for non-admins)
$donation_data = null;
$razorpay_data = null;
if (($action === 'edit' || $action === 'view') && $id > 0) {
    if ($action === 'edit' && !isAdmin()) {
        $error = 'You do not have permission to perform this action.';
        $action = 'list';
    } else {
        $sql = "SELECT d.*, u.name as user_name, u.designation as user_designation 
                FROM donations d 
                LEFT JOIN users u ON d.user_id = u.id 
                WHERE d.id = ?";
        $params = [$id];
        
        if (!isAdmin()) {
            $sql .= " AND d.user_id = ?";
            $params[] = $_SESSION['user_id'];
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $donation_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($donation_data && $donation_data['order_id']) {
            $stmt = $db->prepare("SELECT order_id, payment_id, amount, status, payment_status, created_at 
                                  FROM razorpay_orders WHERE order_id = ?");
            $stmt->execute([$donation_data['order_id']]);
            $razorpay_data = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        if (!$donation_data) {
            $error = 'Donation record not found or you do not have permission to access this record!';
            $action = 'list';
        }
    }
}

// Get donations list with role-based filtering
$donations_list = [];
$filter_status = $_GET['status'] ?? '';
if ($action === 'list') {
    $sql = "SELECT d.*, u.name as user_name, u.designation as user_designation 
            FROM donations d 
            LEFT JOIN users u ON d.user_id = u.id";
    $params = [];
    
    if (!isAdmin()) {
        $sql .= " WHERE d.user_id = ?";
        $params[] = $_SESSION['user_id'];
    }
    
    if (!empty($filter_status)) {
        $sql .= (isAdmin() ? " WHERE" : " AND") . " d.status = ?";
        $params[] = $filter_status;
    }
    
    $sql .= " ORDER BY d.created_at DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $donations_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get statistics (restrict to own data for non-admins)
$stats = [];
try {
    $sql = "SELECT 
        COUNT(*) as total_donations,
        COALESCE(SUM(amount), 0) as total_amount,
        COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_donations,
        COALESCE(SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END), 0) as completed_amount,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_donations
        FROM donations";
    $params = [];
    
    if (!isAdmin()) {
        $sql .= " WHERE user_id = ?";
        $params[] = $_SESSION['user_id'];
    }
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $stats = ['total_donations' => 0, 'total_amount' => 0, 'completed_donations' => 0, 'completed_amount' => 0, 'pending_donations' => 0];
}

include 'includes/header.php';
?>

<div class="admin-content">
    <?php include 'includes/topbar.php'; ?>
    
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-heart me-3"></i>
                <?php 
                if (isAdmin()) {
                    echo "Donation Management";
                } elseif (isCoordinator()) {
                    echo "Donation List (View Only)";
                } else {
                    echo "My Donations";
                }
                ?>
            </h1>
            <div class="page-actions">
                <?php if ($action === 'list'): ?>
                <div class="btn-group me-3" role="group">
                    <a href="?" class="btn btn-outline-primary <?php echo empty($filter_status) ? 'active' : ''; ?>">All</a>
                    <a href="?status=pending" class="btn btn-outline-warning <?php echo $filter_status === 'pending' ? 'active' : ''; ?>">Pending</a>
                    <a href="?status=completed" class="btn btn-outline-success <?php echo $filter_status === 'completed' ? 'active' : ''; ?>">Completed</a>
                    <a href="?status=failed" class="btn btn-outline-danger <?php echo $filter_status === 'failed' ? 'active' : ''; ?>">Failed</a>
                </div>
                <?php if (isAdmin()): ?>
                <a href="?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Donation
                </a>
                <?php endif; ?>
                <?php else: ?>
                <a href="?" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Go Back
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i>
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i>
            <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($action === 'create' && isAdmin()): ?>
        <!-- Add New Donation Form -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-plus"></i> Add New Donation</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="create">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_id" class="form-label"><strong>User:</strong></label>
                                <select class="form-control" id="user_id" name="user_id">
                                    <option value="0">No User</option>
                                    <?php
                                    $stmt = $db->prepare("SELECT id, name, designation FROM users WHERE status = 'approved' ORDER BY name");
                                    $stmt->execute();
                                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($users as $user) {
                                        echo "<option value='{$user['id']}'>" . htmlspecialchars($user['name']) . " (" . htmlspecialchars($user['designation'] ?? '') . ")</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label"><strong>Name:</strong> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="father_name" class="form-label"><strong>Father's Name:</strong> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="father_name" name="father_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mobile" class="form-label"><strong>Mobile:</strong> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="mobile" name="mobile" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label"><strong>Email:</strong></label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pan_card" class="form-label"><strong>PAN Card:</strong></label>
                                <input type="text" class="form-control" id="pan_card" name="pan_card">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label"><strong>Address:</strong> <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" rows="4" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label"><strong>Amount:</strong> <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label"><strong>Payment Method:</strong> <span class="text-danger">*</span></label>
                                <select class="form-control" id="payment_method" name="payment_method" required>
                                    <option value="online">Online</option>
                                    <option value="offline">Offline</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="payment_id" class="form-label"><strong>Payment ID:</strong></label>
                                <input type="text" class="form-control" id="payment_id" name="payment_id">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="order_id" class="form-label"><strong>Order ID:</strong></label>
                                <input type="text" class="form-control" id="order_id" name="order_id">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="status" class="form-label"><strong>Status:</strong> <span class="text-danger">*</span></label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="pending">Pending</option>
                                    <option value="completed">Completed</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="photo" class="form-label"><strong>Photo:</strong></label>
                                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_proof" class="form-label"><strong>Payment Proof:</strong></label>
                                <input type="file" class="form-control" id="payment_proof" name="payment_proof" accept="image/*,application/pdf">
                            </div>
                        </div>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary me-md-2"><i class="fas fa-save"></i> Save</button>
                        <a href="?" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <?php elseif ($action === 'list'): ?>
        <!-- Donations List -->
        <div class="admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list"></i> Donation List</h5>
                <div class="d-flex align-items-center">
                    <span class="me-2">Total Donations: <strong><?php echo $stats['total_donations']; ?></strong></span>
                    <span class="me-2">Total Amount: <strong>₹<?php echo number_format($stats['total_amount'], 2); ?></strong></span>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($donations_list)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No donations found</h5>
                    <?php if (isAdmin()): ?>
                    <p class="text-muted">Click the button above to add the first donation.</p>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Photo</th>
                                <th>Donor</th>
                                <th>User</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donations_list as $donation): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($donation['photo'])): ?>
                                    <img src="<?php echo SITE_URL . '/img/users/' . $donation['photo']; ?>" 
                                         alt="<?php echo htmlspecialchars($donation['name']); ?>" 
                                         class="img-thumbnail" width="50" height="50">
                                    <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                        <i class="fas fa-user text-muted"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($donation['name']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($donation['mobile']); ?></small>
                                </td>
                                <td>
                                    <?php echo $donation['user_id'] > 0 ? htmlspecialchars($donation['user_name'] . ' (' . ($donation['user_designation'] ?? '') . ')') : 'N/A'; ?>
                                </td>
                                <td>
                                    <strong class="text-success">₹<?php echo number_format($donation['amount'], 2); ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $donation['payment_method'] === 'online' ? 'primary' : 'secondary'; ?>">
                                        <?php echo ucfirst($donation['payment_method']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $donation['status'] === 'completed' ? 'success' : 
                                            ($donation['status'] === 'pending' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($donation['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('d M Y', strtotime($donation['created_at'])); ?>
                                    <br>
                                    <small class="text-muted"><?php echo date('h:i A', strtotime($donation['created_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="?action=view&id=<?php echo $donation['id']; ?>" class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (isAdmin()): ?>
                                        <a href="?action=edit&id=<?php echo $donation['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                        <a href="<?php echo SITE_URL; ?>/generate_receipt.php?donation_id=<?php echo $donation['id']; ?>" class="btn btn-sm btn-outline-success" title="Receipt" target="_blank">
                                            <i class="fas fa-receipt"></i>
                                        </a>
                                        <?php if (isAdmin() && $donation['status'] === 'pending'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="updateStatus(<?php echo $donation['id']; ?>, 'completed')" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <?php endif; ?>
                                        <?php if (isAdmin()): ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteDonation(<?php echo $donation['id']; ?>)" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php elseif ($action === 'edit' && $donation_data && isAdmin()): ?>
        <!-- Edit Donation Form -->
        <div class="admin-card">
            <div class="card-header">
                <h5><i class="fas fa-edit"></i> Edit Donation</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($donation_data['id']); ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="user_id" class="form-label"><strong>User:</strong></label>
                                <select class="form-control" id="user_id" name="user_id">
                                    <option value="0">No User</option>
                                    <?php
                                    $stmt = $db->prepare("SELECT id, name, designation FROM users WHERE status = 'approved' ORDER BY name");
                                    $stmt->execute();
                                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($users as $user) {
                                        $selected = $user['id'] == $donation_data['user_id'] ? 'selected' : '';
                                        echo "<option value='{$user['id']}' $selected>" . htmlspecialchars($user['name']) . " (" . htmlspecialchars($user['designation'] ?? '') . ")</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label"><strong>Name:</strong> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($donation_data['name']); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="father_name" class="form-label"><strong>Father's Name:</strong> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="father_name" name="father_name" value="<?php echo htmlspecialchars($donation_data['father_name']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="mobile" class="form-label"><strong>Mobile:</strong> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="mobile" name="mobile" value="<?php echo htmlspecialchars($donation_data['mobile']); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label"><strong>Email:</strong></label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($donation_data['email'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pan_card" class="form-label"><strong>PAN Card:</strong></label>
                                <input type="text" class="form-control" id="pan_card" name="pan_card" value="<?php echo htmlspecialchars($donation_data['pan_card'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label"><strong>Address:</strong> <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address" name="address" rows="4" required><?php echo htmlspecialchars($donation_data['address']); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="amount" class="form-label"><strong>Amount:</strong> <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="<?php echo htmlspecialchars($donation_data['amount']); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_method" class="form-label"><strong>Payment Method:</strong> <span class="text-danger">*</span></label>
                                <select class="form-control" id="payment_method" name="payment_method" required>
                                    <option value="online" <?php echo $donation_data['payment_method'] === 'online' ? 'selected' : ''; ?>>Online</option>
                                    <option value="offline" <?php echo $donation_data['payment_method'] === 'offline' ? 'selected' : ''; ?>>Offline</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="payment_id" class="form-label"><strong>Payment ID:</strong></label>
                                <input type="text" class="form-control" id="payment_id" name="payment_id" value="<?php echo htmlspecialchars($donation_data['payment_id'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="order_id" class="form-label"><strong>Order ID:</strong></label>
                                <input type="text" class="form-control" id="order_id" name="order_id" value="<?php echo htmlspecialchars($donation_data['order_id'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="status" class="form-label"><strong>Status:</strong> <span class="text-danger">*</span></label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="pending" <?php echo $donation_data['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="completed" <?php echo $donation_data['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="failed" <?php echo $donation_data['status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="photo" class="form-label"><strong>Photo:</strong></label>
                                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                                <?php if (!empty($donation_data['photo'])): ?>
                                <p class="mt-2">
                                    <img src="<?php echo SITE_URL . '/img/users/' . $donation_data['photo']; ?>" 
                                         alt="Current Photo" class="img-thumbnail" width="100">
                                    <small>Current Photo</small>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="payment_proof" class="form-label"><strong>Payment Proof:</strong></label>
                                <input type="file" class="form-control" id="payment_proof" name="payment_proof" accept="image/*,application/pdf">
                                <?php if (!empty($donation_data['payment_proof'])): ?>
                                <p class="mt-2">
                                    <a href="<?php echo SITE_URL . '/img/payments/' . $donation_data['payment_proof']; ?>" target="_blank">View Current Payment Proof</a>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="submit" class="btn btn-primary me-md-2"><i class="fas fa-save"></i> Save</button>
                        <a href="?" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                    </div>
                </form>
            </div>
        </div>

        <?php elseif ($action === 'view' && $donation_data): ?>
        <!-- Donation Details -->
        <div class="row">
            <div class="col-lg-8">
                <div class="admin-card">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle"></i> Donation Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Name:</strong></label>
                                    <p><?php echo htmlspecialchars($donation_data['name']); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Father's Name:</strong></label>
                                    <p><?php echo htmlspecialchars($donation_data['father_name']); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>User:</strong></label>
                                    <p><?php echo $donation_data['user_id'] > 0 ? htmlspecialchars($donation_data['user_name'] . ' (' . ($donation_data['user_designation'] ?? '') . ')') : 'N/A'; ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Mobile:</strong></label>
                                    <p><?php echo htmlspecialchars($donation_data['mobile']); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Email:</strong></label>
                                    <p><?php echo htmlspecialchars($donation_data['email'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>PAN Card:</strong></label>
                                    <p><?php echo htmlspecialchars($donation_data['pan_card'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><strong>Address:</strong></label>
                            <p><?php echo htmlspecialchars($donation_data['address']); ?></p>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Amount:</strong></label>
                                    <p class="text-success"><strong>₹<?php echo number_format($donation_data['amount'], 2); ?></strong></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Payment Method:</strong></label>
                                    <p>
                                        <span class="badge bg-<?php echo $donation_data['payment_method'] === 'online' ? 'primary' : 'secondary'; ?>">
                                            <?php echo ucfirst($donation_data['payment_method']); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label"><strong>Status:</strong></label>
                                    <p>
                                        <span class="badge bg-<?php 
                                            echo $donation_data['status'] === 'completed' ? 'success' : 
                                                ($donation_data['status'] === 'pending' ? 'warning' : 'danger'); 
                                        ?>">
                                            <?php echo ucfirst($donation_data['status']); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <?php if (!empty($donation_data['payment_id'])): ?>
                                <div class="mb-3">
                                    <label class="form-label"><strong>Payment ID:</strong></label>
                                    <p><code><?php echo htmlspecialchars($donation_data['payment_id']); ?></code></p>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($donation_data['order_id'])): ?>
                                <div class="mb-3">
                                    <label class="form-label"><strong>Order ID:</strong></label>
                                    <p><code><?php echo htmlspecialchars($donation_data['order_id']); ?></code></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($razorpay_data): ?>
                        <div class="mb-3">
                            <label class="form-label"><strong>Razorpay Order Details:</strong></label>
                            <p>
                                <strong>Order Status:</strong> 
                                <span class="badge bg-<?php 
                                    echo $razorpay_data['status'] === 'paid' ? 'success' : 
                                        ($razorpay_data['status'] === 'created' ? 'warning' : 'danger'); 
                                ?>">
                                    <?php echo htmlspecialchars($razorpay_data['status']); ?>
                                </span><br>
                                <?php if (!empty($razorpay_data['payment_status'])): ?>
                                <strong>Payment Status:</strong> 
                                <span class="badge bg-<?php 
                                    echo $razorpay_data['payment_status'] === 'captured' ? 'success' : 'danger'; 
                                ?>">
                                    <?php echo htmlspecialchars($razorpay_data['payment_status']); ?>
                                </span><br>
                                <?php endif; ?>
                                <strong>Amount (Razorpay):</strong> ₹<?php echo number_format($razorpay_data['amount'] / 100, 2); ?><br>
                                <strong>Date:</strong> <?php echo date('d M Y, h:i A', strtotime($razorpay_data['created_at'])); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label"><strong>Date:</strong></label>
                            <p><?php echo date('d M Y, h:i A', strtotime($donation_data['created_at'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <?php if (!empty($donation_data['photo'])): ?>
                <div class="admin-card">
                    <div class="card-header">
                        <h5><i class="fas fa-image"></i> Photo</h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="<?php echo SITE_URL . '/img/users/' . $donation_data['photo']; ?>" 
                             alt="<?php echo htmlspecialchars($donation_data['name']); ?>" 
                             class="img-fluid rounded">
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($donation_data['payment_proof'])): ?>
                <div class="admin-card">
                    <div class="card-header">
                        <h5><i class="fas fa-file-alt"></i> Payment Proof</h5>
                    </div>
                    <div class="card-body text-center">
                        <a href="<?php echo SITE_URL . '/img/payments/' . $donation_data['payment_proof']; ?>" target="_blank">
                            View Payment Proof
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="admin-card">
                    <div class="card-header">
                        <h5><i class="fas fa-cog"></i> Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if (isAdmin()): ?>
                            <a href="?action=edit&id=<?php echo $donation_data['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <?php endif; ?>
                            <a href="<?php echo SITE_URL; ?>/generate_receipt.php?donation_id=<?php echo $donation_data['id']; ?>" class="btn btn-success" target="_blank">
                                <i class="fas fa-receipt"></i> Print Receipt
                            </a>
                            <a href="<?php echo SITE_URL; ?>/generate_receipt.php?donation_id=<?php echo $donation_data['id']; ?>&download=1" class="btn btn-outline-success" target="_blank">
                                <i class="fas fa-download"></i> Download Receipt
                            </a>
                            <?php if (isAdmin() && $donation_data['status'] === 'pending'): ?>
                            <button type="button" class="btn btn-success" onclick="updateStatus(<?php echo $donation_data['id']; ?>, 'completed')">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button type="button" class="btn btn-danger" onclick="updateStatus(<?php echo $donation_data['id']; ?>, 'failed')">
                                <i class="fas fa-times"></i> Reject
                            </button>
                            <?php endif; ?>
                            <?php if (isAdmin() && !empty($donation_data['email'])): ?>
                            <button type="button" class="btn btn-info" onclick="resendEmail(<?php echo $donation_data['id']; ?>, '<?php echo htmlspecialchars($donation_data['email']); ?>')">
                                <i class="fas fa-envelope"></i> Resend Receipt
                            </button>
                            <?php endif; ?>

                            <?php if (isAdmin()): ?>
                            <button type="button" class="btn btn-outline-info" onclick="sendToNewEmail(<?php echo $donation_data['id']; ?>)">
                                <i class="fas fa-paper-plane"></i> Send to New Email
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/sidebar.php'; ?>

<!-- Status Update Form (Hidden) -->
<form id="statusForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="update_status">
    <input type="hidden" name="id" id="statusId">
    <input type="hidden" name="status" id="statusValue">
</form>

<!-- Delete Form (Hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId">
</form>

<script>
function resendEmail(donationId, currentEmail) {
    if (confirm('Do you really want to resend the receipt to ' + currentEmail + '?')) {
        window.location.href = '?resend_email=' + donationId + '&email=' + encodeURIComponent(currentEmail);
    }
}

function sendToNewEmail(donationId) {
    const newEmail = prompt('Enter new email address:');
    if (newEmail && newEmail.trim()) {
        if (validateEmail(newEmail.trim())) {
            window.location.href = '?resend_email=' + donationId + '&email=' + encodeURIComponent(newEmail.trim());
        } else {
            alert('Please enter a valid email address.');
        }
    }
}

function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function updateStatus(id, status) {
    const statusText = status === 'completed' ? 'approve' : 'reject';
    if (confirm(`Do you really want to ${statusText} this donation?`)) {
        document.getElementById('statusId').value = id;
        document.getElementById('statusValue').value = status;
        document.getElementById('statusForm').submit();
    }
}

function deleteDonation(id) {
    if (confirm('Do you really want to delete this donation record? This action cannot be undone.')) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
