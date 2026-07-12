<?php
/**
 * Universal Perfect Receipt Generator - Final Combined Version
 * This version uses the black-and-green VISUAL DESIGN while implementing the
 * data LOGIC from the orange-and-Hindi version to match the correct database fields.
 * It removes bank/branch details and correctly places the PAN card.
 */

if (!function_exists('validateReceiptData')) {
    // This is the data validation logic from your preferred (orange) structure.
    function validateReceiptData($data, $type) {
        $requiredFields = [
            'donation' => ['name', 'father_name', 'amount', 'created_at', 'status'],
            'registration' => ['registration_id', 'name', 'sdw_name', 'sdw_type', 'membership_type', 'created_at', 'status', 'address'],
            'camp' => ['camp_id', 'name', 'father_name', 'program', 'class', 'place', 'created_at', 'status', 'address']
        ];
        $defaults = [
            'mobile' => 'N/A', 'email' => 'N/A', 'pan_card' => 'N/A', 'designation' => 'N/A',
            'state' => 'N/A', 'district' => 'N/A', 'pincode' => 'N/A', 'amount' => 0,
            'payment_method' => 'N/A', 'payment_id' => 'N/A'
        ];

        foreach ($requiredFields[$type] ?? [] as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $data[$field] = 'N/A';
            }
        }
        foreach ($defaults as $field => $default) {
            $data[$field] = $data[$field] ?? $default;
        }
        return $data;
    }
}

if (!function_exists('generateUniversalReceipt')) {
    function generateUniversalReceipt($data, $options = []) {
        $defaults = [
            'type' => 'donation', 'auto_print' => false, 'show_buttons' => true,
            'download' => false, 'is_test' => false
        ];
        $options = array_merge($defaults, $options);
        
        $siteConfig = getSiteConfig();         
        $orgDetails = [             
            'organization_name' => ORGANIZATION_NAME_HINDI,             
            'organization_name_en' => ORGANIZATION_NAME,             
            'registration_info' => defined('REGISTRATION_INFO') ? REGISTRATION_INFO : null,             
            'pan_card' => defined('ORGANIZATION_PAN') ? ORGANIZATION_PAN : null,             
            'email' => $siteConfig['email'] ?? null,             
            'helpline_no' => $siteConfig['phone1'] ?? null,             
            'chairman_name' => CERTIFICATE_CHAIRMAN_NAME,             
            'chairman_title' => CERTIFICATE_CHAIRMAN_TITLE,             
            'address' => $siteConfig['address'] ?? null,             
            'logo_path' => SITE_URL . '/img/logo.png',
            'signature_path' => SITE_URL . '/img/signature.png',
            'seal_path' => SITE_URL . '/img/seal.png',                 
            'watermark_path' => SITE_URL . '/img/logo.png',
            'website_url' => str_replace(['http://', 'https://'], '', SITE_URL)     
        ];
        
        $isRegistration = ($options['type'] === 'registration');
        $data = validateReceiptData($data, $options['type']);
        
        $receiptNumber = 'GHDAF-' . date('Y') . '-' . str_pad($data['id'], 6, '0', STR_PAD_LEFT);
        $amount = $isRegistration ? getMembershipPrice($data['membership_type']) : $data['amount'];

        $qrCodeDataString = "Receipt No: " . $receiptNumber . "\n" .
                            "Name: " . htmlspecialchars($data['name']) . "\n" .
                            "Amount: Rs. " . number_format($amount, 2) . "\n" .
                            "Date: " . date('d-m-Y', strtotime($data['created_at']));
        
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo ucfirst($options['type']); ?> Receipt</title>
            
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
            <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
            
            <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>

            <style>
                :root {
                    --gradient-start: #25313e; --gradient-end: #7acf6e;
                    --text-dark: #333; --text-light: #fff; --border-color: #d1d1d1;
                }
                html, body { margin: 0; padding: 0; }
                body {
                    font-family: 'Poppins', sans-serif; background-color: #f0f2f5; display: flex; flex-direction: column;
                    align-items: center; padding: 20px 0; color: var(--text-dark);
                    -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;
                }
                .receipt-wrapper {
                    width: 210mm; min-height: 297mm;
                    background-color: #fff; border: 1px solid var(--border-color);
                    box-shadow: 0 0 15px rgba(0,0,0,0.1); padding: 20px; box-sizing: border-box; 
                    display: flex; flex-direction: column;
                }
                .receipt-body { flex-grow: 1; }
                .receipt-header {
                    display: flex; justify-content: space-between; align-items: center;
                    border-bottom: 2px solid #333; padding-bottom: 15px; flex-shrink: 0;
                }
                .header-logo { flex: 1; }
                .header-logo img { max-width: 80px; }
                .header-center { flex: 3; text-align: center; }
                .header-center h2 { margin: 0; font-size: 28px; font-weight: 700; }
                .header-center h5 { margin: 5px 0; font-size: 16px; font-weight: 500; color: #444; text-transform: uppercase; letter-spacing: 0.5px; }
                .header-center ul { list-style: none; padding: 0; margin: 8px 0 0 0; font-size: 13px; }
                .header-qr { flex: 1; display: flex; justify-content: flex-end; }
                #qrcode { border: 2px solid var(--border-color); padding: 5px; }
                .receipt-title { text-align: center; margin: 15px 0; font-size: 24px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; }
                .details-table { width: 100%; border-collapse: collapse; border: 1px solid var(--border-color); font-size: 14px; }
                .details-table td { border: 1px solid var(--border-color); padding: 8px 12px; vertical-align: middle; }
                .details-table .gradient-bg { background: linear-gradient(45deg, var(--gradient-start), var(--gradient-end)); color: var(--text-light); font-weight: 600; }
                .details-table-primary th { background: linear-gradient(45deg, var(--gradient-start), var(--gradient-end)); color: var(--text-light); padding: 10px; border-right: 1px solid #fff; }
                .details-table-primary th:last-child { border-right: none; }
                .details-table-primary td { text-align: center; }
                .status-success { color: #28a745; font-weight: 600; }
                .details-table-secondary { margin-top: 15px; }
                .details-table-secondary .label-cell { width: 25%; }
                .receipt-footer-content { display: flex; justify-content: space-between; margin-top: 30px; }
                .footer-left { width: 65%; }
                .footer-right { width: 30%; text-align: center; }
                .thank-you-msg { text-align: center; font-style: italic; font-size: 18px; margin-top: 20px; }
                .signature-img { max-height: 50px; }
                .signature-line { padding-top: 5px; margin-top: 50px; }
                .signature-line p { margin: 2px 0; font-size: 13px; font-weight: 500; }
                .signature-container { position: relative; display: inline-block; margin-bottom: 5px; }
                .seal-img { position: absolute; bottom: 5px; right: -20px; max-height: 80px; opacity: 0.9; }
                .tax-info { font-size: 13px; margin-top: 30px; line-height: 1.6; }
                .tax-info a { color: var(--gradient-end); text-decoration: none; font-weight: 600; }
                .main-footer {
                    flex-shrink: 0; background: linear-gradient(45deg, var(--gradient-start), var(--gradient-end));
                    color: var(--text-light); padding: 10px 20px; display: flex;
                    justify-content: space-around; font-size: 13px;
                }
                .main-footer a { color: var(--text-light); text-decoration: none; }
                .logo-watermark {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  opacity: 0.05;
  width: 20%;
  pointer-events: none;
}

                .action-buttons { margin-top: 20px; display: flex; gap: 15px; justify-content: center; }
                .action-btn { background: linear-gradient(45deg, var(--gradient-start), var(--gradient-end)); color: white; border: none; padding: 12px 25px; border-radius: 5px; cursor: pointer; font-size: 16px; text-decoration: none; }
                
                @media print {
                    @page { size: A4; margin: 0; }
                    html, body { height: 100%; padding: 0; background-color: #fff; }
                    .receipt-wrapper {
                        box-shadow: none; border: none; width: 100%; height: 100%;
                        min-height: 0; padding: 15mm; box-sizing: border-box;
                    }
                    .main-footer { margin: 20px -15mm -15mm -15mm; }
                    .action-buttons { display: none !important; }
                }
            </style>
        </head>
        <body>
            <div class="receipt-wrapper" id="receipt-to-pdf">
                <div class="receipt-body">
                    <img src="<?php echo htmlspecialchars($orgDetails['watermark_path']); ?>" alt="Watermark" class="logo-watermark">
                    <div class="receipt-header">
                        <div class="header-logo"><img src="<?php echo htmlspecialchars($orgDetails['logo_path']); ?>" alt="Logo"></div>
                        <div class="header-center">
                            <h2><?php echo htmlspecialchars($orgDetails['organization_name']); ?></h2>
                            <h5><?php echo htmlspecialchars($orgDetails['organization_name_en']); ?></h5>
                            <ul>
                                <li><?php echo htmlspecialchars($orgDetails['registration_info']); ?></li>
                                <li><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($orgDetails['address']); ?></li>
                            </ul>
                        </div>
                        <div class="header-qr"><div id="qrcode"></div></div>
                    </div>

                    <h1 class="receipt-title">
                        <?php echo ($isRegistration) ? 'Registration Receipt' : 'Donation Receipt'; ?>
                    </h1>
                    
                    <table class="details-table details-table-primary">
                        <thead><tr><th>Receipt No</th><th>Amount</th><th>Mode</th><th>Payment Status</th><th>Date</th></tr></thead>
                        <tbody><tr>
                            <td><?php echo htmlspecialchars($receiptNumber); ?></td>
                            <td>₹<?php echo number_format($amount, 2); ?></td>
                            <td><?php echo htmlspecialchars($data['payment_method']); ?></td>
                            <td><span class="status-success"><?php echo ucfirst(htmlspecialchars($data['status'])); ?></span></td>
                            <td><?php echo date('d-m-Y', strtotime($data['created_at'])); ?></td>
                        </tr></tbody>
                    </table>
                    
                    <table class="details-table details-table-secondary">
                        <tbody>
                            <tr><td class="gradient-bg label-cell">Received From</td><td><?php echo htmlspecialchars($data['name']); ?></td></tr>
                            <tr><td class="gradient-bg label-cell">Rupees (in words)</td><td><?php echo numberToHindiWords($amount); ?> Rupees Only</td></tr>
                            <tr><td class="gradient-bg label-cell">Address</td><td><?php echo htmlspecialchars($data['address'] ?? 'N/A'); ?></td></tr>
                            <?php // Display PAN card only if it exists ?>
                            <?php if (isset($data['pan_card']) && $data['pan_card'] !== 'N/A'): ?>
                            <tr>
                                <td class="gradient-bg label-cell">PAN Card</td>
                                <td><?php echo htmlspecialchars($data['pan_card']); ?></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div class="receipt-footer-content">
                        <div class="footer-left">
                            <?php // The bank and branch table has been completely removed. ?>
                            <h2 class="thank-you-msg">Thank You For Your Generous Contribution</h2>
                        </div>
                        <div class="footer-right">
                            <div class="signature-line">
                                <div class="signature-container">
                                    <img src="<?php echo htmlspecialchars($orgDetails['signature_path']); ?>" alt="Signature" class="signature-img">
                                    <img src="<?php echo htmlspecialchars($orgDetails['seal_path']); ?>" alt="Seal" class="seal-img">
                                </div>
                                <p><?php echo htmlspecialchars($orgDetails['chairman_name']); ?></p>
                                <p>(<?php echo htmlspecialchars($orgDetails['chairman_title']); ?>)</p>
                                <p>Authorised Signatory</p>
                            </div>
                        </div>
                    </div>

                    <div class="tax-info">
                        Donations made to <a href="#"><?php echo htmlspecialchars($orgDetails['organization_name_en']); ?></a>
                        are eligible for the benefit of deduction under Section 80G of the Income Tax Act, 1961.
                    </div>
                </div> 

                <div class="main-footer">
                    <span><a href="tel:<?php echo htmlspecialchars($orgDetails['helpline_no']); ?>"><i class="fa fa-phone-square"></i> <?php echo htmlspecialchars($orgDetails['helpline_no']); ?></a></span>
                    <span><a href="mailto:<?php echo htmlspecialchars($orgDetails['email']); ?>"><i class="fa fa-envelope"></i> <?php echo htmlspecialchars($orgDetails['email']); ?></a></span>
                    <span><a href="https://<?php echo htmlspecialchars($orgDetails['website_url']); ?>" target="_blank"><i class="fa fa-globe"></i> <?php echo htmlspecialchars($orgDetails['website_url']); ?></a></span>
                </div>
            </div>

            <?php if ($options['show_buttons']): ?>
            <div class="action-buttons">
                <button onclick="generatePDF()" class="action-btn">Download PDF</button>
                <button onclick="window.print()" class="action-btn">Print</button>
            </div>
            <?php endif; ?>

            <script>
                new QRCode(document.getElementById("qrcode"), { text: <?php echo json_encode($qrCodeDataString); ?>, width: 90, height: 90 });
                function generatePDF() {
                    const element = document.getElementById('receipt-to-pdf');
                    const donorName = "<?php echo htmlspecialchars(addslashes($data['name'])); ?>";
                    const receiptNum = "<?php echo htmlspecialchars($receiptNumber); ?>";
                    const options = { margin: 0, filename: `${donorName}_${receiptNum}.pdf`, image: { type: 'jpeg', quality: 0.98 }, html2canvas: { scale: 2, useCORS: true }, jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' } };
                    html2pdf().from(element).set(options).save();
                }
                <?php if ($options['auto_print']): ?>
                window.onload = function() { setTimeout(() => window.print(), 1000); };
                <?php endif; ?>
            </script>
        </body>
        </html>
        <?php
        
        return ob_get_clean();
    }
}

// Wrapper functions
if (!function_exists('generateRegistrationReceipt')) {
    function generateRegistrationReceipt($userData, $options = []) {
        $options['type'] = 'registration';
        return generateUniversalReceipt($userData, $options);
    }
}
if (!function_exists('generateDonationReceipt')) {
    function generateDonationReceipt($donationData, $options = []) {
        $options['type'] = 'donation';
        return generateUniversalReceipt($donationData, $options);
    }
}
if (!function_exists('generateCampReceipt')) {
    function generateCampReceipt($campData, $options = []) {
        $options['type'] = 'camp';
        return generateUniversalReceipt($campData, $options);
    }
}
?>
