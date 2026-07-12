<?php
// Universal Receipt Generator
// This file contains the universal receipt generation function used across the system

function generateUniversalReceipt($donation, $options = []) {
    $siteConfig = getSiteConfig();
    $receipt_number = 'NDF-DON-' . str_pad($donation['id'], 6, '0', STR_PAD_LEFT);
    $date = date('d/m/Y', strtotime($donation['created_at']));
    $amount_in_words = numberToHindiWords((int)$donation['amount']);
    
    // Check if logo and signature files exist
    $logo_path = 'img/logo.png';
    $signature_path = 'img/signature.png';
    
    // Handle different path contexts (admin vs public)
    if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
        $logo_path = '../img/logo.png';
        $signature_path = '../img/signature.png';
        $logo_url = SITE_URL . '/img/logo.png';
        $signature_url = SITE_URL . '/img/signature.png';
    } else {
        $logo_url = SITE_URL . '/img/logo.png';
        $signature_url = SITE_URL . '/img/signature.png';
    }
    
    $logo_exists = file_exists($logo_path);
    $signature_exists = file_exists($signature_path);
    
    // Options for customization
    $auto_print = $options['auto_print'] ?? true;
    $show_buttons = $options['show_buttons'] ?? true;
    $download_mode = $options['download'] ?? false;
    
    return '
    <!DOCTYPE html>
    <html lang="hi">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>दान रसीद - ' . $receipt_number . '</title>
        <style>
            @page {
                size: A4;
                margin: 15mm;
            }
            
            body {
                font-family: "Noto Sans Devanagari", Arial, sans-serif;
                margin: 0;
                padding: 0;
                background: white;
                color: #333;
                line-height: 1.6;
            }
            
            .receipt-container {
                max-width: 800px;
                margin: 0 auto;
                border: 3px solid #2c5aa0;
                border-radius: 15px;
                padding: 30px;
                background: white;
                position: relative;
                box-shadow: 0 0 30px rgba(0,0,0,0.1);
            }
            
            .watermark {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                font-size: 120px;
                color: rgba(44, 90, 160, 0.08);
                font-weight: bold;
                z-index: 1;
                pointer-events: none;
            }
            
            .content {
                position: relative;
                z-index: 2;
            }
            
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 3px solid #2c5aa0;
                padding-bottom: 25px;
            }
            
            .logo-section {
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 20px;
                gap: 20px;
            }
            
            .logo {
                ' . ($logo_exists ? 'width: 100px; height: 100px; border-radius: 50%; border: 3px solid #2c5aa0; padding: 5px; background: white;' : 'display: none;') . '
            }
            
            .org-details {
                text-align: center;
            }
            
            .org-name {
                font-size: 32px;
                font-weight: bold;
                color: #2c5aa0;
                margin: 10px 0;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            }
            
            .org-subtitle {
                font-size: 20px;
                color: #666;
                margin: 8px 0;
            }
            
            .reg-info {
                font-size: 14px;
                color: #888;
                margin-top: 15px;
                padding: 10px;
                background: rgba(44, 90, 160, 0.1);
                border-radius: 8px;
            }
            
            .receipt-title {
                font-size: 28px;
                font-weight: bold;
                background: linear-gradient(135deg, #2c5aa0, #1e3d6f);
                color: white;
                padding: 20px;
                text-align: center;
                margin: 25px 0;
                border-radius: 10px;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            }
            
            .receipt-info {
                display: flex;
                justify-content: space-between;
                margin: 30px 0;
                gap: 30px;
            }
            
            .donor-details {
                flex: 1;
            }
            
            .amount-section {
                flex: 1;
                text-align: center;
                border: 3px solid #2c5aa0;
                padding: 25px;
                border-radius: 15px;
                background: linear-gradient(135deg, #f8f9ff, #e8f0ff);
            }
            
            .detail-table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            .detail-table th,
            .detail-table td {
                border: 2px solid #2c5aa0;
                padding: 12px;
                text-align: left;
            }
            
            .detail-table th {
                background: linear-gradient(135deg, #2c5aa0, #1e3d6f);
                color: white;
                font-weight: bold;
                text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
            }
            
            .detail-table td {
                background: #fafafa;
            }
            
            .amount-large {
                font-size: 42px;
                font-weight: bold;
                color: #2c5aa0;
                margin: 15px 0;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            }
            
            .amount-words {
                font-size: 16px;
                color: #555;
                font-style: italic;
                margin: 15px 0;
                padding: 15px;
                background: rgba(44, 90, 160, 0.1);
                border-radius: 8px;
                border-left: 4px solid #2c5aa0;
            }
            
            .receipt-meta {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
                margin: 25px 0;
            }
            
            .meta-item {
                background: #2c5aa0;
                color: white;
                padding: 15px;
                border-radius: 8px;
                text-align: center;
                font-weight: bold;
            }
            
            .footer {
                margin-top: 40px;
                display: flex;
                justify-content: space-between;
                align-items: end;
                padding-top: 30px;
                border-top: 2px solid #2c5aa0;
            }
            
            .signature-section {
                text-align: center;
                min-width: 250px;
            }
            
            .signature-image {
                ' . ($signature_exists ? 'width: 200px; height: 80px; margin-bottom: 15px; border: 1px solid #ddd; background: white; padding: 5px; border-radius: 5px;' : 'display: none;') . '
            }
            
            .signature-line {
                border-top: 2px solid #333;
                margin: 25px 0 10px;
                width: 250px;
                ' . ($signature_exists ? 'display: none;' : 'display: block;') . '
            }
            
            .signature-label {
                font-weight: bold;
                color: #2c5aa0;
                font-size: 16px;
            }
            
            .thank-you {
                background: linear-gradient(135deg, #2c5aa0, #1e3d6f);
                color: white;
                padding: 25px;
                border-radius: 15px;
                margin: 30px 0;
                text-align: center;
                font-size: 20px;
                font-weight: bold;
                text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            }
            
            .contact-info {
                font-size: 12px;
                color: #666;
                text-align: center;
                margin-top: 30px;
                padding: 20px;
                background: #f8f9fa;
                border-radius: 10px;
                border-top: 3px solid #2c5aa0;
            }
            
            .payment-method-badge {
                display: inline-block;
                padding: 8px 16px;
                border-radius: 25px;
                font-size: 14px;
                font-weight: bold;
                text-transform: uppercase;
                margin: 5px;
            }
            
            .online { background: #28a745; color: white; }
            .offline { background: #6c757d; color: white; }
            
            @media print {
                body { 
                    margin: 0; 
                    background: white; 
                    -webkit-print-color-adjust: exact;
                    color-adjust: exact;
                }
                .receipt-container { 
                    box-shadow: none; 
                    border: 2px solid #000;
                    page-break-inside: avoid;
                }
                .no-print { 
                    display: none !important; 
                }
            }
            
            @media (max-width: 768px) {
                .receipt-info {
                    flex-direction: column;
                    gap: 20px;
                }
                
                .receipt-container {
                    padding: 15px;
                    margin: 10px;
                }
                
                .org-name {
                    font-size: 24px;
                }
                
                .amount-large {
                    font-size: 32px;
                }
            }
        </style>
    </head>
    <body>
        <div class="receipt-container">
            <div class="watermark">NDF</div>
            
            <div class="content">
                <div class="header">
                    <div class="logo-section">
                        ' . ($logo_exists ? '<img src="' . $logo_url . '" alt="Logo" class="logo">' : '') . '
                        <div class="org-details">
                            <div class="org-name">' . htmlspecialchars($siteConfig['site_title']) . '</div>
                            <div class="org-subtitle">' . htmlspecialchars($siteConfig['site_subtitle']) . '</div>
                            <div class="reg-info">
                                <strong>पंजीकरण संख्या:</strong> 202400777016680<br>
                                <strong>ईमेल:</strong> ' . htmlspecialchars($siteConfig['email']) . '<br>
                                <strong>फोन:</strong> ' . htmlspecialchars($siteConfig['phone1']) . ' | <strong>वेबसाइट:</strong> ndfoundation.in
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="receipt-title">दान रसीद / DONATION RECEIPT</div>
                
                <div class="receipt-info">
                    <div class="donor-details">
                        <h3 style="color: #2c5aa0; border-bottom: 2px solid #2c5aa0; padding-bottom: 10px; margin-bottom: 20px;">
                            <i class="fas fa-user"></i> दाता विवरण / Donor Details
                        </h3>
                        <table class="detail-table">
                            <tr><th>नाम / Name:</th><td>' . htmlspecialchars($donation['name']) . '</td></tr>
                            <tr><th>पिता का नाम / Father Name:</th><td>' . htmlspecialchars($donation['father_name']) . '</td></tr>
                            <tr><th>मोबाइल / Mobile:</th><td>' . htmlspecialchars($donation['mobile']) . '</td></tr>
                            <tr><th>ईमेल / Email:</th><td>' . htmlspecialchars($donation['email'] ?? 'N/A') . '</td></tr>
                            <tr><th>पता / Address:</th><td>' . htmlspecialchars($donation['address']) . '</td></tr>
                            ' . (!empty($donation['pan_card']) ? '<tr><th>PAN:</th><td>' . htmlspecialchars($donation['pan_card']) . '</td></tr>' : '') . '
                        </table>
                    </div>
                    
                    <div class="amount-section">
                        <div style="font-size: 20px; margin-bottom: 15px; color: #2c5aa0; font-weight: bold;">
                            <i class="fas fa-rupee-sign"></i> दान राशि / Donation Amount
                        </div>
                        <div class="amount-large">₹ ' . number_format($donation['amount'], 2) . '</div>
                        <div class="amount-words">
                            <strong>शब्दों में:</strong><br>' . $amount_in_words . ' रुपये मात्र
                        </div>
                        
                        <div class="receipt-meta">
                            <div class="meta-item">
                                <div>रसीद संख्या</div>
                                <div style="font-size: 18px;">' . $receipt_number . '</div>
                            </div>
                            <div class="meta-item">
                                <div>दिनांक</div>
                                <div style="font-size: 18px;">' . $date . '</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <table class="detail-table" style="margin: 30px 0;">
                    <tr>
                        <th style="width: 30%;">भुगतान विधि / Payment Method:</th>
                        <td>
                            <span class="payment-method-badge ' . ($donation['payment_method'] === 'online' ? 'online' : 'offline') . '">
                                ' . ($donation['payment_method'] === 'online' ? 'ऑनलाइन / Online' : 'ऑफलाइन / Offline') . '
                            </span>
                        </td>
                    </tr>
                    ' . (!empty($donation['payment_id']) ? '<tr><th>भुगतान ID / Payment ID:</th><td><code style="background: #f8f9fa; padding: 5px; border-radius: 3px;">' . htmlspecialchars($donation['payment_id']) . '</code></td></tr>' : '') . '
                    <tr>
                        <th>स्थिति / Status:</th>
                        <td>
                            <span style="color: ' . ($donation['status'] === 'completed' ? '#28a745' : ($donation['status'] === 'pending' ? '#ffc107' : '#dc3545')) . '; font-weight: bold;">
                                ' . ($donation['status'] === 'completed' ? '✓ पूर्ण / Completed' : ($donation['status'] === 'pending' ? '⏳ लंबित / Pending' : '✗ असफल / Failed')) . '
                            </span>
                        </td>
                    </tr>
                </table>
                
                <div class="thank-you">
                    <div style="font-size: 24px; margin-bottom: 10px;">🙏 धन्यवाद! 🙏</div>
                    <div>आपका दान समाज सेवा के लिए उपयोग किया जाएगा।</div>
                    <div style="font-size: 16px; margin-top: 10px; opacity: 0.9;">
                        <em>Thank you! Your donation will be used for social service.</em>
                    </div>
                </div>
                
                <div class="footer">
                    <div style="flex: 1;">
                        <div style="margin-bottom: 20px;">
                            <strong style="color: #2c5aa0;">नोट / Note:</strong><br>
                            यह रसीद आपके दान का प्रमाण है।<br>
                            <em>This receipt serves as proof of your donation.</em>
                        </div>
                        <div style="font-size: 12px; color: #666;">
                            <strong>कर छूट:</strong> धारा 80G के तहत कर छूट उपलब्ध है।<br>
                            <em>Tax exemption available under Section 80G.</em>
                        </div>
                    </div>
                    
                    <div class="signature-section">
                        ' . ($signature_exists ? '<img src="' . $signature_url . '" alt="Signature" class="signature-image">' : '') . '
                        <div class="signature-line"></div>
                        <div class="signature-label">
                            अधिकृत हस्ताक्षर<br>
                            <em>Authorized Signature</em>
                        </div>
                    </div>
                </div>
                
                <div class="contact-info">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px;">
                        <div><strong>पता / Address:</strong><br>' . htmlspecialchars($siteConfig['address']) . '</div>
                        <div><strong>संपर्क / Contact:</strong><br>📞 ' . htmlspecialchars($siteConfig['phone1']) . '</div>
                        <div><strong>ईमेल / Email:</strong><br>✉️ ' . htmlspecialchars($siteConfig['email']) . '</div>
                        <div><strong>वेबसाइट / Website:</strong><br>🌐 ndfoundation.in</div>
                    </div>
                    <div style="border-top: 1px solid #ddd; padding-top: 10px; font-size: 11px;">
                        यह कंप्यूटर जनरेटेड रसीद है। / This is a computer generated receipt.<br>
                        <strong>राष्ट्रीय विकास फाउंडेशन</strong> - समुदायों को सशक्त बनाने के लिए
                    </div>
                </div>
            </div>
        </div>
        
        ' . ($show_buttons ? '
        <div class="no-print" style="text-align: center; margin-top: 30px; padding: 20px;">
            <button onclick="window.print()" style="padding: 15px 30px; font-size: 16px; background: #2c5aa0; color: white; border: none; border-radius: 8px; cursor: pointer; margin: 0 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                🖨️ प्रिंट करें / Print
            </button>
            <button onclick="window.close()" style="padding: 15px 30px; font-size: 16px; background: #666; color: white; border: none; border-radius: 8px; cursor: pointer; margin: 0 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2);">
                ❌ बंद करें / Close
            </button>
        </div>
        ' : '') . '
        
        <script>
            // Auto print functionality
            ' . ($auto_print && $download_mode ? 'window.onload = function() { setTimeout(function() { window.print(); }, 1000); };' : '') . '
        </script>
    </body>
    </html>';
}
?>
