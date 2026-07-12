<?php
/**
 * Enhanced Universal ID Card Generator for World Hindu Samaj Kalayan Trust Committee
 * FULLY FUNCTIONAL barcode/QR code data population for proper scanning
 * Works exclusively with USERS table for all user types (member, coordinator, admin)
 * Configured for president authority only
 * Updated to remove validity dates from back side
 */

require_once __DIR__ . '/../../config/config.php';

class UniversalIdCardGenerator {
    private $db;
    private $config;
    
    public function __construct($database = null) {
        $this->db = $database ?: getDbConnection();
        
        $this->config = [
            'template_path' => SITE_URL . '/templates/id_card.png',
            'organization_name' => ORGANIZATION_NAME_HINDI,
            'organization_name_en' => ORGANIZATION_NAME,
            'registration_info' => 'UID : U88900AS2025NPLO28744 | NITI AAYOG : AS/2025/0787391',
            'email' => 'janmanascharitabletrust@gmail.com',
            'canvas_width' => 1011,
            'canvas_height' => 1378,
            'front_start_y' => 172.4,
            'front_end_y' => 607.3,
            'back_start_y' => 913.2,
            'back_end_y' => 1346.5,
            'upload_dir' => __DIR__ . '/../../uploads/profiles/',
            'upload_url' => SITE_URL . '/uploads/profiles/',
            'helpline_no' => '9919426516',
            'website_qr' => SITE_URL,
            'default_photo' => SITE_URL . '/uploads/profiles/default.png',
            'seal_path' => SITE_URL . '/img/seal.png',
            'front_padding' => 15,
            'back_padding' => 15,
            'chairman_name' => CERTIFICATE_CHAIRMAN_NAME,
            'chairman_title' => CERTIFICATE_CHAIRMAN_TITLE,
            'signature_path' => SITE_URL . '/img/signature.png',
            'secretary_name' => CERTIFICATE_SECRETARY_NAME,
            'secretary_title' => CERTIFICATE_SECRETARY_TITLE,
            'secretary_signature_path' => SITE_URL . '/img/signature1.png',
        ];
        
        // Load authority config from database
        try {
            $db = getDbConnection();
            $settings = ['chairman_name', 'chairman_title', 'secretary_name', 'secretary_title'];
            foreach ($settings as $setting) {
                $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
                $stmt->execute([$setting]);
                $result = $stmt->fetch();
                if ($result && !empty($result['setting_value'])) {
                    $this->config[$setting] = $result['setting_value'];
                }
            }
        } catch (Exception $e) {
            error_log('Failed to load authority config from DB: ' . $e->getMessage());
        }
    }
    
    // Convert DD-MM-YYYY to YYYY-MM-DD for database
    private function convertDateToDBFormat($date) {
        if (empty($date)) {
            return date('Y-m-d'); // Default to current date
        }
        $parts = explode('-', $date);
        if (count($parts) !== 3 || !checkdate($parts[1], $parts[0], $parts[2])) {
            return date('Y-m-d'); // Fallback to current date if invalid
        }
        return $parts[2] . '-' . $parts[1] . '-' . $parts[0]; // Convert to YYYY-MM-DD
    }

    // Convert YYYY-MM-DD to DD-MM-YYYY for display
    private function convertDateToDisplayFormat($date) {
        if (empty($date)) {
            return date('d-m-Y');
        }
        $parts = explode('-', $date);
        if (count($parts) !== 3) {
            return date('d-m-Y');
        }
        return $parts[2] . '-' . $parts[1] . '-' . $parts[0]; // Convert to DD-MM-YYYY
    }
    
    public function getMemberData($identifier, $type = 'id') {
        try {
            $memberData = null;
            
            if ($type === 'registration_id') {
                $stmt = $this->db->prepare("
                    SELECT 
                        id, name, mobile AS phone, profile_image AS photo, designation, 
                        registration_id AS member_id, registration_id, email, dob, blood_group,
                        CASE 
                            WHEN sdw_type = 'S/O' THEN 'Father'
                            WHEN sdw_type = 'D/O' THEN 'Father'
                            WHEN sdw_type = 'W/O' THEN 'Husband'
                            ELSE 'Father'
                        END AS sdw_type,
                        sdw_name,
                        CONCAT(
                            COALESCE(address, ''), 
                            CASE WHEN district IS NOT NULL AND district != '' THEN CONCAT(', ', district) ELSE '' END,
                            CASE WHEN state IS NOT NULL AND state != '' THEN CONCAT(', ', state) ELSE '' END,
                            CASE WHEN pincode IS NOT NULL AND pincode != '' THEN CONCAT(' - ', pincode) ELSE '' END
                        ) AS address,
                        working_area,
                        COALESCE(valid_from, DATE_FORMAT(NOW(), '%Y-%m-%d')) AS valid_from,
                        COALESCE(valid_until, DATE_FORMAT(DATE_ADD(COALESCE(valid_from, NOW()), INTERVAL 1 YEAR), '%Y-%m-%d')) AS valid_until,
                        status, membership_type, user_type, 'users' AS source_table
                    FROM users 
                    WHERE registration_id = ? AND status = 'approved'
                    LIMIT 1
                ");
                $stmt->execute([$identifier]);
                $memberData = $stmt->fetch(PDO::FETCH_ASSOC);
                
            } elseif ($type === 'id') {
                $stmt = $this->db->prepare("
                    SELECT 
                        id, name, mobile AS phone, profile_image AS photo, designation,
                        registration_id AS member_id, registration_id, email, dob, blood_group, 
                        CASE 
                            WHEN sdw_type = 'S/O' THEN 'Father'
                            WHEN sdw_type = 'D/O' THEN 'Father'
                            WHEN sdw_type = 'W/O' THEN 'Husband'
                            ELSE 'Father'
                        END AS sdw_type,
                        sdw_name,
                        CONCAT(
                            COALESCE(address, ''), 
                            CASE WHEN district IS NOT NULL AND district != '' THEN CONCAT(', ', district) ELSE '' END,
                            CASE WHEN state IS NOT NULL AND state != '' THEN CONCAT(', ', state) ELSE '' END,
                            CASE WHEN pincode IS NOT NULL AND pincode != '' THEN CONCAT(' - ', pincode) ELSE '' END
                        ) AS address,
                        working_area,
                        COALESCE(valid_from, DATE_FORMAT(NOW(), '%Y-%m-%d')) AS valid_from,
                        COALESCE(valid_until, DATE_FORMAT(DATE_ADD(COALESCE(valid_from, NOW()), INTERVAL 1 YEAR), '%Y-%m-%d')) AS valid_until,
                        status, membership_type, user_type, 'users' AS source_table
                    FROM users 
                    WHERE id = ?
                    LIMIT 1
                ");
                $stmt->execute([$identifier]);
                $memberData = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            if ($memberData) {
                return $this->formatMemberData($memberData);
            }
            
            return null;
            
        } catch (PDOException $e) {
            error_log('Database error in getMemberData: ' . $e->getMessage());
            return null;
        }
    }
    
    public function formatMemberData($data) {
        $designation = $data['designation'] ?? '';
        $membership_type = $data['membership_type'] ?? 'active';
        $user_type = $data['user_type'] ?? 'member';
        
        // Handle admin user type first
        if ($user_type === 'admin') {
            $designation = 'Administrator';
        } else {
            // For all other user types (including coordinator), try to find the Hindi designation from membership_designations table
            if (!empty($designation)) {
                try {
                    // First try to match the exact designation with membership type
                    $stmt = $this->db->prepare("
                        SELECT designation_hindi
                        FROM membership_designations
                        WHERE designation = ? AND membership_type = ? AND status = 'active'
                        LIMIT 1
                    ");
                    $stmt->execute([$designation, $membership_type]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($result) {
                        $designation = $result['designation_hindi'];
                    } else {
                        // If exact match not found, try case-insensitive search
                        $stmt = $this->db->prepare("
                            SELECT designation_hindi
                            FROM membership_designations
                            WHERE LOWER(designation) = LOWER(?) AND membership_type = ? AND status = 'active'
                            LIMIT 1
                        ");
                        $stmt->execute([$designation, $membership_type]);
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($result) {
                            $designation = $result['designation_hindi'];
                        } else {
                            // If still not found, try without membership type constraint
                            $stmt = $this->db->prepare("
                                SELECT designation_hindi
                                FROM membership_designations
                                WHERE LOWER(designation) = LOWER(?) AND status = 'active'
                                LIMIT 1
                            ");
                            $stmt->execute([$designation]);
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);
                            
                            if ($result) {
                                $designation = $result['designation_hindi'];
                            } else {
                                // If still not found, get the first designation for this membership type
                                $stmt = $this->db->prepare("
                                    SELECT designation_hindi
                                    FROM membership_designations
                                    WHERE membership_type = ? AND status = 'active'
                                    ORDER BY sort_order ASC
                                    LIMIT 1
                                ");
                                $stmt->execute([$membership_type]);
                                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                                $designation = $result['designation_hindi'] ?? 'Member';
                            }
                        }
                    }
                } catch (PDOException $e) {
                    error_log('Error fetching designation: ' . $e->getMessage());
                    $designation = 'Member';
                }
            } else {
                // If no designation provided, get the first one for the membership type
                try {
                    $stmt = $this->db->prepare("
                        SELECT designation_hindi
                        FROM membership_designations
                        WHERE membership_type = ? AND status = 'active'
                        ORDER BY sort_order ASC
                        LIMIT 1
                    ");
                    $stmt->execute([$membership_type]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $designation = $result['designation_hindi'] ?? 'Member';
                } catch (PDOException $e) {
                    error_log('Error fetching default designation: ' . $e->getMessage());
                    $designation = 'Member';
                }
            }
        }
        
        $data['designation'] = $designation;

        if (!empty($data['photo'])) {
            $imagePath = $this->config['upload_dir'] . basename($data['photo']);
            if (file_exists($imagePath) && is_readable($imagePath)) {
                $data['photo_url'] = $this->config['upload_url'] . basename($data['photo']) . '?t=' . time();
            } else {
                error_log('Photo not found or not readable: ' . $imagePath);
                $data['photo_url'] = $this->config['default_photo'];
            }
        } else {
            error_log('No photo provided for user: ' . ($data['name'] ?? 'Unknown'));
            $data['photo_url'] = $this->config['default_photo'];
        }
        
        $data['address'] = trim($data['address'] ?? '', ', -') ?: 'Address not available';
        $data['working_area'] = trim($data['working_area'] ?? '') ?: $data['address'];
        $data['name'] = $data['name'] ?? 'N/A';
        $data['phone'] = $data['phone'] ?? 'N/A';
        $data['email'] = $data['email'] ?? 'N/A';
        $data['member_id'] = $data['member_id'] ?? $data['registration_id'] ?? 'N/A';
        $data['registration_id'] = $data['registration_id'] ?? $data['member_id'] ?? 'N/A';
        $data['blood_group'] = $data['blood_group'] ?? 'N/A';
        $data['dob'] = $data['dob'] ?? '';

        // --- IMPORTANT: keep ISO dates for JS and add display strings (no layout change) ---
        $rawFrom  = $data['valid_from']  ?? date('Y-m-d');
        $rawUntil = $data['valid_until'] ?? date('Y-m-d', strtotime('+1 year', strtotime($rawFrom)));
        $data['valid_from']          = $rawFrom;  // ISO for JS parsing
        $data['valid_until']         = $rawUntil; // ISO for JS parsing
        $data['valid_from_display']  = $this->convertDateToDisplayFormat($rawFrom);
        $data['valid_until_display'] = $this->convertDateToDisplayFormat($rawUntil);
        // -----------------------------------------------------------------------------------

        $data['user_type'] = $user_type;
        
        return $data;
    }
    
    public function generateScannableData($memberData, $format = 'text') {
        $user_type = $memberData['user_type'] ?? 'member';
        $user_type_label = [
            'member' => 'Member',
            'coordinator' => 'Coordinator', 
            'admin' => 'Administrator'
        ][$user_type] ?? 'Member';
        
        $data = [
            'type' => 'UFCT_ID_CARD',
            'version' => '2.0',
            'member_id' => $memberData['member_id'] ?? '',
            'registration_id' => $memberData['registration_id'] ?? '',
            'name' => $memberData['name'] ?? '',
            'designation' => $memberData['designation'] ?? '',
            'user_type' => $user_type_label,
            'phone' => $memberData['phone'] ?? '',
            'email' => $memberData['email'] ?? '',
            'blood_group' => $memberData['blood_group'] ?? '',
            'dob' => $memberData['dob'] ?? '',
            // Prefer display strings in payload; fall back to ISO
            'valid_from' => $memberData['valid_from_display'] ?? $this->convertDateToDisplayFormat($memberData['valid_from'] ?? ''),
            'valid_until' => $memberData['valid_until_display'] ?? $this->convertDateToDisplayFormat($memberData['valid_until'] ?? ''),
            'address' => $memberData['address'] ?? '',
            'working_area' => $memberData['working_area'] ?? '',
            'organization' => 'Sevarthi Human Rights Association',
            'website' => $this->config['website_qr'],
            'helpline' => $this->config['helpline_no'],
            'issued_by' => $this->config['chairman_name'] . ', ' . $this->config['chairman_title'],
            'generated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($format === 'json') {
            return json_encode($data, JSON_UNESCAPED_UNICODE);
        } elseif ($format === 'csv') {
            return implode('|', array_values($data));
        } else {
            return "UFCT ID Card\n" .
                   "ID: " . $data['member_id'] . "\n" .
                   "Name: " . $data['name'] . "\n" .
                   "Type: " . $data['user_type'] . "\n" .
                   "Designation: " . $data['designation'] . "\n" .
                   "Phone: " . $data['phone'] . "\n" .
                   "Email: " . $data['email'] . "\n" .
                   "Blood: " . $data['blood_group'] . "\n" .
                   "Valid From: " . $data['valid_from'] . "\n" .
                   "Valid Until: " . $data['valid_until'] . "\n" .
                   "Organization: " . $data['organization'] . "\n" .
                   "Website: " . $data['website'] . "\n" .
                   "Helpline: " . $data['helpline'] . "\n" .
                   "Verified by: " . $data['issued_by'];
        }
    }
    
    public function generateJavaScript($memberData, $containerId = 'idCardCanvas') {
        $jsData = json_encode($memberData, JSON_UNESCAPED_UNICODE);
        $config = json_encode($this->config, JSON_UNESCAPED_UNICODE);
        
        $barcodeData = '';
        $qrData = '';
        
        if (!empty($memberData) && isset($memberData['name'])) {
            $barcodeData = $this->generateScannableData($memberData, 'csv');
            $qrData = $this->generateScannableData($memberData, 'text');
        }
        
        return "
        <script src='https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js'></script>
        <script src='https://cdn.jsdelivr.net/npm/qrcode@1.4.4/build/qrcode.min.js'></script>
        
        <script>
        const UniversalIdCardGenerator = {
            memberData: {$jsData},
            config: {$config},
            barcodeData: `{$barcodeData}`,
            qrData: `{$qrData}`,
            canvas: null,
            ctx: null,
            
            updateScannableData: function(memberData) {
                if (!memberData || !memberData.name) {
                    console.warn('No valid member data provided for scannable codes');
                    return;
                }
                
                this.memberData = memberData;
                this.barcodeData = this.generateScannableData(memberData, 'csv');
                this.qrData = this.generateScannableData(memberData, 'text');
            },
            
            generateScannableData: function(memberData, format = 'text') {
                const user_type = memberData.user_type || 'member';
                const user_type_labels = {
                    'member': 'Member',
                    'coordinator': 'Coordinator', 
                    'admin': 'Administrator'
                };
                const user_type_label = user_type_labels[user_type] || 'Member';
                
                const data = {
                    type: 'UFCT_ID_CARD',
                    version: '2.0',
                    member_id: memberData.member_id || '',
                    registration_id: memberData.registration_id || '',
                    name: memberData.name || '',
                    designation: memberData.designation || '',
                    user_type: user_type_label,
                    phone: memberData.phone || '',
                    email: memberData.email || '',
                    blood_group: memberData.blood_group || '',
                    dob: memberData.dob || '',
                    valid_from: memberData.valid_from || '',
                    valid_until: memberData.valid_until || '',
                    address: memberData.address || '',
                    working_area: memberData.working_area || '',
                    organization: 'Mind Care Foundation',
                    website: this.config.website_qr || '',
                    helpline: this.config.helpline_no || '9023211131',
                    issued_by: (this.config.chairman_name || 'Authority') + ', ' + (this.config.chairman_title || 'Title'),
                    generated_at: new Date().toISOString()
                };
                
                if (format === 'json') {
                    return JSON.stringify(data);
                } else if (format === 'csv') {
                    return Object.values(data).join('|');
                } else {
                    return 'UFCT ID Card\\n' +
                           'ID: ' + data.member_id + '\\n' +
                           'Name: ' + data.name + '\\n' +
                           'Type: ' + data.user_type + '\\n' +
                           'Designation: ' + data.designation + '\\n' +
                           'Phone: ' + data.phone + '\\n' +
                           'Email: ' + data.email + '\\n' +
                           'Blood: ' + data.blood_group + '\\n' +
                           'Valid From: ' + data.valid_from + '\\n' +
                           'Valid Until: ' + data.valid_until + '\\n' +
                           'Organization: ' + data.organization + '\\n' +
                           'Website: ' + data.website + '\\n' +
                           'Helpline: ' + data.helpline + '\\n' +
                           'Verified by: ' + data.issued_by;
                }
            },
            
            init: function(canvasId = '{$containerId}') {
                this.canvas = document.getElementById(canvasId);
                if (!this.canvas) {
                    console.error('Canvas element not found:', canvasId);
                    return false;
                }
                this.ctx = this.canvas.getContext('2d');
                this.canvas.width = this.config.canvas_width;
                this.canvas.height = this.config.canvas_height;
                return true;
            },
            
            generate: function(callback) {
                if (!this.init()) {
                    if (callback) callback(false, 'Canvas initialization failed');
                    return;
                }
                
                if (this.memberData && this.memberData.name) {
                    this.updateScannableData(this.memberData);
                }
                
                this.loadBackgroundImage((success, error) => {
                    if (!success) {
                        if (callback) callback(false, error);
                        return;
                    }
                    
                    this.drawFrontSide(() => {
                        this.drawBackSide(() => {
                            if (callback) callback(true, this.canvas.toDataURL('image/png'));
                        });
                    });
                });
            },
            
            loadBackgroundImage: function(callback) {
                const backgroundImage = new Image();
                backgroundImage.crossOrigin = 'Anonymous';
                backgroundImage.src = this.config.template_path + '?t=' + new Date().getTime();
                
                backgroundImage.onload = () => {
                    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                    this.ctx.drawImage(backgroundImage, 0, 0, this.canvas.width, this.canvas.height);
                    callback(true);
                };
                
                backgroundImage.onerror = () => {
                    console.error('Failed to load background image');
                    callback(false, 'Failed to load ID card template');
                };
            },
            
            drawFrontSide: function(callback) {
                const frontStartY = this.config.front_start_y;
                const frontEndY = this.config.front_end_y;
                const frontHeight = frontEndY - frontStartY;
                
                this.drawMemberPhoto(frontStartY, frontHeight, () => {
                    this.drawFrontDetails(frontStartY, frontHeight);
                    this.drawSignatureAndSeal(frontStartY, frontHeight, () => {
                        callback();
                    });
                });
            },
            
            drawMemberPhoto: function(startY, height, callback) {
                if (!this.memberData.photo_url) {
                    this.memberData.photo_url = this.config.default_photo;
                }
                
                const photo = new Image();
                photo.crossOrigin = 'Anonymous';
                photo.src = this.memberData.photo_url;
                
                photo.onload = () => {
                    const photoWidth = 200;
                    const photoHeight = 250;
                    const photoX = 35;
                    const photoY = startY + 60;
                    
                    this.ctx.save();
                    this.ctx.shadowColor = 'rgba(0, 0, 0, 0.3)';
                    this.ctx.shadowBlur = 5;
                    this.ctx.shadowOffsetX = 3;
                    this.ctx.shadowOffsetY = 3;
                    
                    this.drawRoundedRect(photoX, photoY, photoWidth, photoHeight, 12);
                    this.ctx.clip();
                    
                    const photoAspectRatio = photo.width / photo.height;
                    const boxAspectRatio = photoWidth / photoHeight;
                    let drawWidth, drawHeight, offsetX, offsetY;
                    
                    if (photoAspectRatio > boxAspectRatio) {
                        drawHeight = photoHeight;
                        drawWidth = photo.width * (photoHeight / photo.height);
                    } else {
                        drawWidth = photoWidth;
                        drawHeight = photo.height * (photoWidth / photo.width);
                    }
                    
                    offsetX = photoX + (photoWidth - drawWidth) / 2;
                    offsetY = photoY + (photoHeight - drawHeight) / 2;
                    this.ctx.drawImage(photo, offsetX, offsetY, drawWidth, drawHeight);
                    this.ctx.restore();
                    
                    this.ctx.beginPath();
                    this.drawRoundedRect(photoX, photoY, photoWidth, photoHeight, 12);
                    const gradient = this.ctx.createLinearGradient(photoX, photoY, photoX + photoWidth, photoY + photoHeight);
                    gradient.addColorStop(0, '#2c3e50');
                    gradient.addColorStop(1, '#34495e');
                    this.ctx.strokeStyle = gradient;
                    this.ctx.lineWidth = 3;
                    this.ctx.stroke();
                    
                    callback();
                };
                
                photo.onerror = () => {
                    this.memberData.photo_url = this.config.default_photo;
                    const defaultPhoto = new Image();
                    defaultPhoto.crossOrigin = 'Anonymous';
                    defaultPhoto.src = this.config.default_photo;
                    
                    defaultPhoto.onload = () => {
                        const photoWidth = 200;
                        const photoHeight = 250;
                        const photoX = 35;
                        const photoY = startY + 60;
                        
                        this.ctx.save();
                        this.drawRoundedRect(photoX, photoY, photoWidth, photoHeight, 12);
                        this.ctx.clip();
                        this.ctx.drawImage(defaultPhoto, photoX, photoY, photoWidth, photoHeight);
                        this.ctx.restore();
                        callback();
                    };
                    
                    defaultPhoto.onerror = () => {
                        console.error('Failed to load default photo');
                        callback();
                    };
                };
            },
            
            drawFrontDetails: function(startY, height) {
                const data = this.memberData;
                const detailsStartX = 280;
                const detailsStartY = startY + 80;
                const lineHeight = 32;
                const maxWidth = this.canvas.width - detailsStartX - 20;
                
                this.ctx.textAlign = 'left';
                let currentY = detailsStartY;
                
                const labelStyle = { font: 'bold 24.1px Arial', color: '#2c3e50' };
                const valueStyle = { font: '24px Arial', color: '#34495e' };
                
                this.ctx.fillStyle = labelStyle.color;
                this.ctx.font = labelStyle.font;
                this.ctx.fillText('NAME:', detailsStartX, currentY);
                this.ctx.fillStyle = valueStyle.color;
                this.ctx.font = valueStyle.font;
                const nameText = this.truncateText(data.name, maxWidth - 80);
                this.ctx.fillText(nameText, detailsStartX + 250, currentY);
                currentY += lineHeight;
                
                this.ctx.fillStyle = labelStyle.color;
                this.ctx.font = labelStyle.font;
                this.ctx.fillText('SR NO:', detailsStartX, currentY);
                this.ctx.fillStyle = valueStyle.color;
                this.ctx.font = valueStyle.font;
                this.ctx.fillText(data.member_id, detailsStartX + 250, currentY);
                currentY += lineHeight;
                
                this.ctx.fillStyle = labelStyle.color;
                this.ctx.font = labelStyle.font;
                this.ctx.fillText('DESIGNATION:', detailsStartX, currentY);
                this.ctx.fillStyle = valueStyle.color;
                this.ctx.font = valueStyle.font;
                const designationText = this.truncateText(data.designation, maxWidth - 140);
                this.ctx.fillText(designationText, detailsStartX + 250, currentY);
                currentY += lineHeight;
                
                if (data.user_type && data.user_type !== 'member') {
                    this.ctx.fillStyle = labelStyle.color;
                    this.ctx.font = labelStyle.font;
                    this.ctx.fillText('TYPE:', detailsStartX, currentY);
                    this.ctx.fillStyle = '#e74c3c';
                    this.ctx.font = 'bold 24px Arial';
                    const typeLabels = { 'admin': 'ADMINISTRATOR', 'coordinator': 'COORDINATOR' };
                    this.ctx.fillText(typeLabels[data.user_type] || data.user_type.toUpperCase(), detailsStartX + 250, currentY);
                    currentY += lineHeight;
                }
                
                this.ctx.fillStyle = labelStyle.color;
                this.ctx.font = labelStyle.font;
                this.ctx.fillText('BLOOD GROUP:', detailsStartX, currentY);
                this.ctx.fillStyle = valueStyle.color;
                this.ctx.font = valueStyle.font;
                this.ctx.fillText(data.blood_group, detailsStartX + 250, currentY);
                currentY += lineHeight;
                
                if (data.dob && data.dob !== '') {
                    this.ctx.fillStyle = labelStyle.color;
                    this.ctx.font = labelStyle.font;
                    this.ctx.fillText('DOB:', detailsStartX, currentY);
                    this.ctx.fillStyle = valueStyle.color;
                    this.ctx.font = valueStyle.font;
                    this.ctx.fillText(this.formatDate(data.dob), detailsStartX + 250, currentY);
                    currentY += lineHeight;
                }
                
                this.ctx.fillStyle = labelStyle.color;
                this.ctx.font = labelStyle.font;
                this.ctx.fillText('WORKING AREA:', detailsStartX, currentY);
                this.ctx.fillStyle = valueStyle.color;
                this.ctx.font = valueStyle.font;
                const workingAreaText = data.working_area || data.address;
                const workingAreaLines = this.wrapTextToLines(workingAreaText, maxWidth - 160, 2);
                workingAreaLines.forEach((line, index) => {
                    this.ctx.fillText(line, detailsStartX + 250, currentY + (index * 20));
                });
                currentY += lineHeight + (workingAreaLines.length > 1 ? 20 : 0);
                
                this.ctx.fillStyle = labelStyle.color;
                this.ctx.font = labelStyle.font;
                this.ctx.fillText('VALID FROM:', detailsStartX, currentY);
                this.ctx.fillStyle = '#e74c3c';
                this.ctx.font = 'bold 24px Arial';
                this.ctx.fillText(this.formatDate(data.valid_from), detailsStartX + 250, currentY);
                currentY += lineHeight;
                
                this.ctx.fillStyle = labelStyle.color;
                this.ctx.font = labelStyle.font;
                this.ctx.fillText('VALID UP TO:', detailsStartX, currentY);
                this.ctx.fillStyle = '#e74c3c';
                this.ctx.font = 'bold 24px Arial';
                this.ctx.fillText(this.formatDate(data.valid_until), detailsStartX + 250, currentY);
                currentY += lineHeight + 10;
                
                const photoX = 35;
                const photoWidth = 200;
                const photoHeight = 250;
                const photoY = startY + 60;
                const barcodeY = photoY + photoHeight + 20;
                this.drawEnhancedBarcodeWithData(photoX, barcodeY, photoWidth);
                
                this.ctx.fillStyle = '#2c3e50';
                this.ctx.textAlign = 'center';
                const authorityY = startY + height - 40;
                this.ctx.font = 'bold 15px Arial';
                this.ctx.fillText(this.config.chairman_name, this.canvas.width - 170, authorityY - 20);
                this.ctx.font = '15px Arial';
                this.ctx.fillText(this.config.chairman_title, this.canvas.width - 170, authorityY);
                this.ctx.fillText('Issuing Authority:', this.canvas.width - 170, authorityY + 20);
            },
            
            drawSignatureAndSeal: function(startY, height, callback) {
                const signatureX = this.canvas.width - 220;
                const signatureY = startY + height - 140;
                const signatureWidth = 140;
                const signatureHeight = 60;
                
                const sealX = this.canvas.width - 220;
                const sealY = startY + height - 150;
                const sealWidth = 70;
                const sealHeight = 70;
                
                let imagesLoaded = 0;
                const totalImages = 2;
                
                const checkCompletion = () => {
                    if (++imagesLoaded === totalImages) {
                        callback();
                    }
                };
                
                const signature = new Image();
                signature.crossOrigin = 'Anonymous';
                signature.src = this.config.signature_path + '?t=' + new Date().getTime();
                
                signature.onload = () => {
                    this.ctx.save();
                    this.ctx.shadowColor = 'rgba(0, 0, 0, 0.2)';
                    this.ctx.shadowBlur = 3;
                    this.ctx.drawImage(signature, signatureX, signatureY, signatureWidth, signatureHeight);
                    this.ctx.restore();
                    checkCompletion();
                };
                
                signature.onerror = () => {
                    console.error('Failed to load signature');
                    checkCompletion();
                };
                
                const seal = new Image();
                seal.crossOrigin = 'Anonymous';
                seal.src = this.config.seal_path + '?t=' + new Date().getTime();
                
                seal.onload = () => {
                    this.ctx.save();
                    this.ctx.shadowColor = 'rgba(0, 0, 0, 0.2)';
                    this.ctx.shadowBlur = 3;
                    this.ctx.drawImage(seal, sealX, sealY, sealWidth, sealHeight);
                    this.ctx.restore();
                    checkCompletion();
                };
                
                seal.onerror = () => {
                    console.error('Failed to load seal');
                    checkCompletion();
                };
            },
            
            drawEnhancedBarcodeWithData: function(x, y, maxWidth) {
                const barcodeWidth = Math.min(maxWidth, 200);
                const barcodeHeight = 60;
                
                if (typeof JsBarcode !== 'undefined') {
                    try {
                        const barcodeCanvas = document.createElement('canvas');
                        barcodeCanvas.width = barcodeWidth;
                        barcodeCanvas.height = barcodeHeight;
                        
                        JsBarcode(barcodeCanvas, this.barcodeData, {
                            format: 'CODE128',
                            width: 1.5,
                            height: barcodeHeight - 20,
                            displayValue: false,
                            background: '#ffffff',
                            lineColor: '#000000',
                            margin: 2
                        });
                        
                        this.ctx.save();
                        this.ctx.fillStyle = '#ffffff';
                        this.ctx.fillRect(x - 5, y - 5, barcodeWidth + 10, barcodeHeight + 10);
                        this.ctx.strokeStyle = '#2c3e50';
                        this.ctx.lineWidth = 1;
                        this.ctx.strokeRect(x - 5, y - 5, barcodeWidth + 10, barcodeHeight + 10);
                        
                        this.ctx.drawImage(barcodeCanvas, x, y, barcodeWidth, barcodeHeight - 20);
                        this.ctx.restore();
                        
                        this.ctx.fillStyle = '#2c3e50';
                        this.ctx.font = 'bold 12px monospace';
                        this.ctx.textAlign = 'center';
                        this.ctx.fillText(this.memberData.member_id, x + barcodeWidth / 2, y + barcodeHeight - 5);
                        
                        return;
                    } catch (error) {
                        console.error('JsBarcode generation error:', error);
                    }
                }
                
                this.drawPerfectBarcodeWithData(x, y, barcodeWidth, barcodeHeight);
            },
            
            drawPerfectBarcodeWithData: function(x, y, width, height) {
                this.ctx.save();
                
                this.ctx.fillStyle = '#ffffff';
                this.ctx.fillRect(x - 5, y - 5, width + 10, height + 10);
                this.ctx.strokeStyle = '#2c3e50';
                this.ctx.lineWidth = 1;
                this.ctx.strokeRect(x - 5, y - 5, width + 10, height + 10);
                
                this.ctx.fillStyle = '#000000';
                const barcodePattern = this.generatePerfectBarcodePattern(this.barcodeData, width);
                const barHeight = height - 25;
                
                for (let i = 0; i < barcodePattern.length; i++) {
                    if (barcodePattern[i].draw) {
                        this.ctx.fillRect(
                            x + barcodePattern[i].x, 
                            y + 5, 
                            barcodePattern[i].width, 
                            barHeight
                        );
                    }
                }
                
                this.ctx.fillStyle = '#2c3e50';
                this.ctx.font = 'bold 12px monospace';
                this.ctx.textAlign = 'center';
                this.ctx.fillText(this.memberData.member_id, x + width / 2, y + height - 8);
                
                this.ctx.restore();
            },
            
            generatePerfectBarcodePattern: function(data, totalWidth) {
                const pattern = [];
                const availableWidth = totalWidth - 10;
                const moduleCount = 120;
                const moduleWidth = availableWidth / moduleCount;
                
                const startPattern = [1,1,0,1,0,1,1];
                const endPattern = [1,1,0,1,1,1,0,1,0];
                
                let currentX = 5;
                
                startPattern.forEach(bit => {
                    pattern.push({
                        x: currentX,
                        width: moduleWidth,
                        draw: bit === 1
                    });
                    currentX += moduleWidth;
                });
                
                const dataBytes = this.stringToBytes(data);
                const encodedData = this.encodeDataToPattern(dataBytes, moduleCount - startPattern.length - endPattern.length);
                
                encodedData.forEach(bit => {
                    pattern.push({
                        x: currentX,
                        width: moduleWidth,
                        draw: bit === 1
                    });
                    currentX += moduleWidth;
                });
                
                endPattern.forEach(bit => {
                    pattern.push({
                        x: currentX,
                        width: moduleWidth,
                        draw: bit === 1
                    });
                    currentX += moduleWidth;
                });
                
                return pattern;
            },
            
            stringToBytes: function(str) {
                const bytes = [];
                for (let i = 0; i < str.length; i++) {
                    bytes.push(str.charCodeAt(i) & 0xFF);
                }
                return bytes;
            },
            
            encodeDataToPattern: function(bytes, targetLength) {
                const pattern = [];
                
                for (let i = 0; i < targetLength; i++) {
                    const byteIndex = i % bytes.length;
                    const byte = bytes[byteIndex];
                    const bitIndex = i % 8;
                    const bit = (byte >> (7 - bitIndex)) & 1;
                    
                    const position = i + byteIndex;
                    const enhanced = (bit ^ (position % 2)) & 1;
                    
                    pattern.push(enhanced);
                }
                
                return pattern;
            },
            
            drawBackSide: function(callback) {
                const backStartY = this.config.back_start_y;
                const backEndY = this.config.back_end_y;
                const backHeight = backEndY - backStartY;
                
                this.drawBackDetails(backStartY, backHeight);
                this.drawEnhancedQRCodeWithData(backStartY + 30, () => {
                    callback();
                });
            },
            
            drawBackDetails: function(startY, height) {
                const data = this.memberData;
                const detailsX = 40;
                let currentY = startY + 60;
                const lineHeight = 28;
                const maxWidth = this.canvas.width - 180;
                
                this.ctx.textAlign = 'left';
                
                const labelStyle = { font: 'bold 24px Arial', color: '#2c3e50' };
                const valueStyle = { font: '24px Arial', color: '#34495e' };
                
                this.ctx.fillStyle = labelStyle.color;
                this.ctx.font = labelStyle.font;
                this.ctx.fillText('Address:', detailsX, currentY);
                this.ctx.fillStyle = valueStyle.color;
                this.ctx.font = valueStyle.font;
                const addressLines = this.wrapTextToLines(data.address, maxWidth - 100, 3);
                addressLines.forEach((line, index) => {
                    this.ctx.fillText(line, detailsX + 240, currentY + (index * 22));
                });
                currentY += lineHeight + (addressLines.length > 1 ? (addressLines.length - 1) * 22 : 0);
                
                this.ctx.fillStyle = labelStyle.color;
                this.ctx.font = labelStyle.font;
                this.ctx.fillText('Mobile:', detailsX, currentY);
                this.ctx.fillStyle = valueStyle.color;
                this.ctx.font = valueStyle.font;
                this.ctx.fillText(data.phone, detailsX + 240, currentY);
                currentY += lineHeight;
                
                this.ctx.fillStyle = labelStyle.color;
                this.ctx.font = labelStyle.font;
                this.ctx.fillText('Email:', detailsX, currentY);
                this.ctx.fillStyle = valueStyle.color;
                this.ctx.font = valueStyle.font;
                const emailText = this.truncateText(data.email, maxWidth - 100);
                this.ctx.fillText(emailText, detailsX + 240, currentY);
                currentY += lineHeight;
                
                this.ctx.fillStyle = labelStyle.color;
                this.ctx.font = labelStyle.font;
                this.ctx.fillText('Foundation Email:', detailsX, currentY);
                this.ctx.fillStyle = valueStyle.color;
                this.ctx.font = valueStyle.font;
                this.ctx.fillText(this.config.email, detailsX + 240, currentY);
                currentY += lineHeight;
                
                this.ctx.fillStyle = labelStyle.color;
                this.ctx.font = labelStyle.font;
                this.ctx.fillText('Foundation Helpline:', detailsX, currentY);
                this.ctx.fillStyle = '#e74c3c';
                this.ctx.font = 'bold 24px Arial';
                this.ctx.fillText(this.config.helpline_no, detailsX + 240, currentY);
                currentY += lineHeight + 15;
                
                this.ctx.fillStyle = '#000000ff';
                this.ctx.font = '24px Arial';
                const disclaimer = 'This identity card is the property of 9919426516. It should be used only subject to the rules/terms of the organization. Action may be taken against the cardholder for misuse. In case of loss, contact the organization immediately.';
                const disclaimerLines = this.wrapTextToLines(disclaimer, maxWidth, 4);
                const disclaimerXOffset = 0;
                const disclaimerYOffset = +50;
                const disclaimerLineSpacing = 30;
                this.ctx.textAlign = 'center';
                disclaimerLines.forEach((line, index) => {
                    this.ctx.fillText(line, this.canvas.width / 2 + disclaimerXOffset, currentY + disclaimerYOffset + (index * disclaimerLineSpacing));
                });
                
                this.ctx.fillStyle = '#0D3559';
                this.ctx.font = 'bold 14px Arial';
                this.ctx.textAlign = 'center';
                this.ctx.fillText('Visit: ' + this.config.website_qr, this.canvas.width / 2, startY + height - 20);
            },
            
            drawEnhancedQRCodeWithData: function(startY, callback) {
                const qrSize = 140;
                const qrX = this.canvas.width - qrSize - 35;
                const qrY = startY + 50;
                
                const gradient = this.ctx.createRadialGradient(qrX + qrSize/2, qrY + qrSize/2, 0, qrX + qrSize/2, qrY + qrSize/2, qrSize/2);
                gradient.addColorStop(0, '#ffffff');
                gradient.addColorStop(1, '#f8f9fa');
                
                this.ctx.fillStyle = gradient;
                this.ctx.fillRect(qrX - 10, qrY - 10, qrSize + 20, qrSize + 20);
                
                this.ctx.strokeStyle = '#2c3e50';
                this.ctx.lineWidth = 2;
                this.ctx.strokeRect(qrX - 10, qrY - 10, qrSize + 20, qrSize + 20);
                
                if (typeof QRCode !== 'undefined') {
                    QRCode.toCanvas(document.createElement('canvas'), this.qrData, { 
                        width: qrSize,
                        height: qrSize,
                        margin: 2,
                        errorCorrectionLevel: 'M',
                        type: 'image/png',
                        quality: 0.92,
                        color: {
                            dark: '#2c3e50',
                            light: '#ffffff'
                        }
                    }, (error, canvas) => {
                        if (!error && canvas) {
                            this.ctx.drawImage(canvas, qrX, qrY, qrSize, qrSize);
                        } else {
                            console.error('QR Code generation failed:', error);
                            this.drawEnhancedQRPlaceholder(qrX, qrY, qrSize);
                        }
                        callback();
                    });
                } else {
                    console.warn('QRCode library not available, using placeholder');
                    this.drawEnhancedQRPlaceholder(qrX, qrY, qrSize);
                    callback();
                }
            },
            
            drawEnhancedQRPlaceholder: function(x, y, size) {
                const cellSize = size / 29;
                this.ctx.fillStyle = '#2c3e50';
                
                const dataString = this.qrData;
                const dataHash = this.advancedHash(dataString);
                
                for (let row = 0; row < 29; row++) {
                    for (let col = 0; col < 29; col++) {
                        if (this.isCornerPattern(row, col)) continue;
                        
                        const cellValue = this.getCellValue(dataString, row, col, dataHash);
                        if (cellValue) {
                            this.ctx.fillRect(x + col * cellSize, y + row * cellSize, cellSize, cellSize);
                        }
                    }
                }
                
                this.drawQRCorners(x, y, cellSize);
            },
            
            isCornerPattern: function(row, col) {
                return (row < 9 && col < 9) || 
                       (row < 9 && col > 19) || 
                       (row > 19 && col < 9);
            },
            
            getCellValue: function(dataString, row, col, hash) {
                const charIndex = (row * 29 + col) % dataString.length;
                const charCode = dataString.charCodeAt(charIndex);
                const position = row * 29 + col;
                
                return ((charCode + hash + position) % 3) === 0;
            },
            
            drawQRCorners: function(x, y, cellSize) {
                const cornerSize = cellSize * 7;
                const innerSize = cellSize * 3;
                const innerOffset = cellSize * 2;
                
                const corners = [[0, 0], [22, 0], [0, 22]];
                
                corners.forEach(([cornerX, cornerY]) => {
                    const startX = x + cornerX * cellSize;
                    const startY = y + cornerY * cellSize;
                    
                    this.ctx.fillStyle = '#2c3e50';
                    this.ctx.fillRect(startX, startY, cornerSize, cornerSize);
                    
                    this.ctx.fillStyle = '#ffffff';
                    this.ctx.fillRect(startX + cellSize, startY + cellSize, cornerSize - 2*cellSize, cornerSize - 2*cellSize);
                    
                    this.ctx.fillStyle = '#2c3e50';
                    this.ctx.fillRect(startX + innerOffset, startY + innerOffset, innerSize, innerSize);
                });
            },
            
            advancedHash: function(str) {
                let hash = 0;
                for (let i = 0; i < str.length; i++) {
                    const char = str.charCodeAt(i);
                    hash = ((hash << 5) - hash) + char;
                    hash = hash & hash;
                    hash = ((hash << 3) ^ (hash >> 11)) + ((hash << 15) | (hash >> 17));
                }
                return Math.abs(hash);
            },
            
            drawRoundedRect: function(x, y, width, height, radius) {
                this.ctx.beginPath();
                this.ctx.moveTo(x + radius, y);
                this.ctx.lineTo(x + width - radius, y);
                this.ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
                this.ctx.lineTo(x + width, y + height - radius);
                this.ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
                this.ctx.lineTo(x + radius, y + height);
                this.ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
                this.ctx.lineTo(x, y + radius);
                this.ctx.quadraticCurveTo(x, y, x + radius, y);
                this.ctx.closePath();
            },
            
            wrapTextToLines: function(text, maxWidth, maxLines = 3) {
                const words = text.split(' ');
                const lines = [];
                let currentLine = '';
                
                for (let i = 0; i < words.length && lines.length < maxLines; i++) {
                    const testLine = currentLine + (currentLine ? ' ' : '') + words[i];
                    const metrics = this.ctx.measureText(testLine);
                    
                    if (metrics.width > maxWidth && currentLine) {
                        lines.push(currentLine);
                        currentLine = words[i];
                    } else {
                        currentLine = testLine;
                    }
                }
                
                if (currentLine && lines.length < maxLines) {
                    lines.push(currentLine);
                } else if (lines.length === maxLines && currentLine) {
                    lines[maxLines - 1] = lines[maxLines - 1] + '...';
                }
                
                return lines;
            },
            
            truncateText: function(text, maxWidth) {
                if (this.ctx.measureText(text).width <= maxWidth) {
                    return text;
                }
                
                let truncated = text;
                while (this.ctx.measureText(truncated + '...').width > maxWidth && truncated.length > 0) {
                    truncated = truncated.slice(0, -1);
                }
                
                return truncated + '...';
            },
            
            formatDate: function(dateString) {
                if (!dateString) return 'N/A';

                let d = null;
                // ISO: YYYY-MM-DD
                if (/^\\d{4}-\\d{2}-\\d{2}$/.test(dateString)) {
                    const [y, m, dd] = dateString.split('-').map(Number);
                    d = new Date(Date.UTC(y, m - 1, dd));
                }
                // Display: DD-MM-YYYY
                else if (/^\\d{2}-\\d{2}-\\d{4}$/.test(dateString)) {
                    const [dd, m, y] = dateString.split('-').map(Number);
                    d = new Date(Date.UTC(y, m - 1, dd));
                }
                // Fallback
                else if (!isNaN(Date.parse(dateString))) {
                    d = new Date(dateString);
                }

                if (!d || isNaN(d.getTime())) return 'N/A';
                return d.toLocaleDateString('en-IN', { year: 'numeric', month: '2-digit', day: '2-digit' });
            }
        };
        
        window.UniversalIdCardGenerator = UniversalIdCardGenerator;
        </script>";
    }
    
    public function saveIdCardImage($memberId, $imageData, $sourceTable = 'users') {
        try {
            $imageData = str_replace(['data:image/png;base64,', ' '], ['', '+'], $imageData);
            $data = base64_decode($imageData);
            
            if ($data === false) {
                throw new Exception('Invalid base64 image data');
            }
            
            $imageInfo = getimagesizefromstring($data);
            if ($imageInfo === false) {
                throw new Exception('Invalid image data');
            }
            
            $filename = 'id_card_' . $memberId . '_' . time() . '.png';
            $uploadDir = $this->config['upload_dir'];
            
            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    throw new Exception('Failed to create upload directory');
                }
                chmod($uploadDir, 0755);
            }
            
            $filePath = $uploadDir . $filename;
            
            if (file_put_contents($filePath, $data) === false) {
                throw new Exception('Failed to save ID card image to filesystem');
            }
            
            chmod($filePath, 0644);
            
            $stmt = $this->db->prepare("UPDATE users SET id_card_photo = ?, updated_at = NOW() WHERE id = ?");
            if (!$stmt->execute([$filename, $memberId])) {
                unlink($filePath);
                throw new Exception('Failed to update database record');
            }
            
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filePath,
                'url' => $this->config['upload_url'] . $filename
            ];
            
        } catch (Exception $e) {
            error_log('Error saving ID card image: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    public function handleFormSubmission($postData, $files) {
        try {
            $requiredFields = ['name', 'mobile', 'gender', 'dob', 'sdw_name', 'designation', 'aadhar', 'state', 'district', 'address', 'pincode', 'membership_type', 'status', 'valid_from'];
            
            foreach ($requiredFields as $field) {
                if (empty($postData[$field])) {
                    throw new Exception("Required field missing: $field");
                }
            }
            
            // Validate date formats
            if (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $postData['valid_from'])) {
                throw new Exception('Valid from date must be in DD-MM-YYYY format (example: 10-09-2025).');
            }
            if (!empty($postData['valid_until']) && !preg_match('/^\d{2}-\d{2}-\d{4}$/', $postData['valid_until'])) {
                throw new Exception('Valid until date must be in DD-MM-YYYY format (example: 10-09-2025).');
            }
            
            $photo = '';
            if (!empty($files['photo']['name']) && $files['photo']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->handleFileUpload($files['photo'], 'photo');
                if (!$uploadResult['success']) {
                    throw new Exception($uploadResult['message']);
                }
                $photo = $uploadResult['filename'];
            }
            
            $action = $postData['action'];
            
            if ($action === 'add') {
                return $this->addUser($postData, $photo);
            } elseif ($action === 'edit') {
                return $this->updateUser($postData, $photo);
            }
            
            throw new Exception('Invalid action');
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    private function handleFileUpload($file, $type) {
        $uploadDir = $this->config['upload_dir'];
        
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                return ['success' => false, 'message' => 'Failed to create upload directory'];
            }
            chmod($uploadDir, 0755);
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024;
        
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF allowed.'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'File size too large. Maximum 5MB allowed.'];
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid($type . '_') . '.' . $extension;
        $filePath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            chmod($filePath, 0644);
            return ['success' => true, 'filename' => $filename];
        }
        
        return ['success' => false, 'message' => 'Failed to upload file'];
    }
    
    private function addUser($data, $photo) {
        $registrationId = !empty($data['registration_id']) ? $data['registration_id'] : $this->generateRegistrationId();
        
        $email = !empty($data['email']) ? $data['email'] : '';
        $password = !empty($data['password']) ? password_hash($data['password'], PASSWORD_DEFAULT) : null;
        $sdwType = !empty($data['sdw_type']) ? $data['sdw_type'] : 'S/O';
        $profession = !empty($data['profession']) ? $data['profession'] : '';
        $bloodGroup = !empty($data['blood_group']) ? $data['blood_group'] : '';
        $workingArea = !empty($data['working_area']) ? $data['working_area'] : $data['address'];
        $validFrom = $this->convertDateToDBFormat($data['valid_from']);
        $validUntil = !empty($data['valid_until']) ? $this->convertDateToDBFormat($data['valid_until']) : date('Y-m-d', strtotime('+1 year', strtotime($validFrom)));
        $userType = !empty($data['user_type']) ? $data['user_type'] : 'member';
        
        $stmt = $this->db->prepare("
            INSERT INTO users (
                name, email, password, mobile, gender, dob, sdw_type, sdw_name,
                profession, designation, blood_group, aadhar, state, district, address, working_area,
                pincode, membership_type, profile_image, registration_id, status, valid_from, valid_until, user_type, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $success = $stmt->execute([
            $data['name'], $email, $password, $data['mobile'], 
            $data['gender'], $data['dob'], $sdwType, $data['sdw_name'],
            $profession, $data['designation'], $bloodGroup, 
            $data['aadhar'], $data['state'], $data['district'], $data['address'], 
            $workingArea, $data['pincode'], $data['membership_type'], 
            $photo, $registrationId, $data['status'], $validFrom, $validUntil, $userType
        ]);
        
        if ($success) {
            return ['success' => true, 'message' => 'User added successfully'];
        }
        
        throw new Exception('Failed to add user to database');
    }
    
    private function updateUser($data, $photo) {
        $recordId = $data['record_id'];
        
        if (empty($photo)) {
            $stmt = $this->db->prepare("SELECT profile_image FROM users WHERE id = ?");
            $stmt->execute([$recordId]);
            $existing = $stmt->fetch();
            $photo = $existing['profile_image'] ?? '';
        }
        
        $email = !empty($data['email']) ? $data['email'] : '';
        $sdwType = !empty($data['sdw_type']) ? $data['sdw_type'] : 'S/O';
        $profession = !empty($data['profession']) ? $data['profession'] : '';
        $bloodGroup = !empty($data['blood_group']) ? $data['blood_group'] : '';
        $workingArea = !empty($data['working_area']) ? $data['working_area'] : $data['address'];
        $validFrom = $this->convertDateToDBFormat($data['valid_from']);
        $validUntil = !empty($data['valid_until']) ? $this->convertDateToDBFormat($data['valid_until']) : date('Y-m-d', strtotime('+1 year', strtotime($validFrom)));
        $userType = !empty($data['user_type']) ? $data['user_type'] : 'member';
        
        $stmt = $this->db->prepare("
            UPDATE users SET 
                name = ?, email = ?, mobile = ?, gender = ?, 
                dob = ?, sdw_type = ?, sdw_name = ?, profession = ?, designation = ?, 
                blood_group = ?, aadhar = ?, state = ?, district = ?, address = ?, working_area = ?, 
                pincode = ?, membership_type = ?, profile_image = ?, status = ?,
                valid_from = ?, valid_until = ?, user_type = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        $success = $stmt->execute([
            $data['name'], $email, $data['mobile'], $data['gender'], 
            $data['dob'], $sdwType, $data['sdw_name'], $profession, $data['designation'], 
            $bloodGroup, $data['aadhar'], $data['state'], $data['district'], $data['address'], 
            $workingArea, $data['pincode'], $data['membership_type'], 
            $photo, $data['status'], $validFrom, $validUntil, $userType, $recordId
        ]);
        
        if ($success) {
            return ['success' => true, 'message' => 'User updated successfully'];
        }
        
        throw new Exception('Failed to update user in database');
    }
    
    private function generateRegistrationId() {
        $stmt = $this->db->prepare("SELECT MAX(CAST(SUBSTRING(registration_id, 4) AS UNSIGNED)) as max_num FROM users WHERE registration_id LIKE ?");
        $stmt->execute([ORGANIZATION_NAME_SHORT . "%"]);
        $result = $stmt->fetch();
        $nextNum = ($result['max_num'] ?? 0) + 1;
        return ORGANIZATION_NAME_SHORT . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
    }
    
    public function deleteUser($userId) {
        try {
            $stmt = $this->db->prepare("SELECT profile_image FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $success = $stmt->execute([$userId]);
            
            if (!$success) {
                throw new Exception('Failed to delete user from database');
            }
            
            if (!empty($user['profile_image']) && file_exists($this->config['upload_dir'] . $user['profile_image'])) {
                unlink($this->config['upload_dir'] . $user['profile_image']);
            }
            
            return ['success' => true, 'message' => 'User deleted successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>
