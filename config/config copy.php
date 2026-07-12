<?php
// Enhanced Configuration for Chhattisgarh Media Association

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detect environment
$isLocalEnvironment = (
    strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
    strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false
);

// Database and site configuration
if ($isLocalEnvironment) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'aimamedia');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('SITE_URL', 'http://localhost/aimamedia');
} else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'u526627089_aimamedia');
    define('DB_USER', 'u526627089_aimamedia');
    define('DB_PASS', 'G7m$kP9v!nT2qR8x');
    define('SITE_URL', 'https://aimamedia.buildmyngo.space');
}

define('SITE_NAME', 'PRESSCLUB WELFARE WORLDWIDE FOUNDATION');
define('ADMIN_URL', rtrim(SITE_URL, '/') . '/admin/');
define('ORGANIZATION_NAME', 'PRESSCLUB WELFARE WORLDWIDE FOUNDATION');
define('ORGANIZATION_NAME_HINDI', 'PRESSCLUB WELFARE WORLDWIDE FOUNDATION');
define('ORGANIZATION_NAME_SHORT', 'PWWF');

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting based on environment
if ($isLocalEnvironment) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');

// Database connection function
if (!function_exists('getDbConnection')) {
    function getDbConnection() {
        static $connection = null;
        
        if ($connection === null) {
            try {
                $connection = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                logError('Database connection error: ' . $e->getMessage());
                die('Connection failed. Please try again later.');
            }
        }
        
        return $connection;
    }
}

// Error logging function
if (!function_exists('logError')) {
    function logError($message, $file = 'error.log') {
        $logDir = __DIR__ . '/../logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, $logDir . $file);
    }
}

// Get site configuration function
if (!function_exists('getSiteConfig')) {
    function getSiteConfig($forceRefresh = false) {
        static $config = null;
        
        if ($forceRefresh || $config === null) {
            try {
                $db = getDbConnection();
                $stmt = $db->prepare("SELECT * FROM site_config WHERE id = 1 LIMIT 1");
                $stmt->execute();
                $config = $stmt->fetch();
                
                // Minimal fallback if no config found
                if (!$config) {
                    logError('No site configuration found in site_config table');
                    $config = ['website_url' => SITE_URL];
                }
            } catch (Exception $e) {
                logError('Site config error: ' . $e->getMessage());
                $config = ['website_url' => SITE_URL];
            }
        }
        
        return $config;
    }
}

// Load membership pricing from settings table
$membershipPrices = [];
try {
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('membership_price_block', 'membership_price_tehsil', 'membership_price_district', 'membership_price_mandal', 'membership_price_state')");
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $membershipPrices[$row['setting_key']] = (int)$row['setting_value'];
    }
} catch (Exception $e) {
    logError('Membership pricing error: ' . $e->getMessage());
    $membershipPrices = [
        'membership_price_block' => 300,
        'membership_price_tehsil' => 400,
        'membership_price_district' => 500,
        'membership_price_mandal' => 600,
        'membership_price_state' => 800
    ];
}

define('BLOCK_MEMBERSHIP_PRICE', $membershipPrices['membership_price_block'] ?? 300);
define('TEHSIL_MEMBERSHIP_PRICE', $membershipPrices['membership_price_tehsil'] ?? 400);
define('DISTRICT_MEMBERSHIP_PRICE', $membershipPrices['membership_price_district'] ?? 500);
define('MANDAL_MEMBERSHIP_PRICE', $membershipPrices['membership_price_mandal'] ?? 600);
define('STATE_MEMBERSHIP_PRICE', $membershipPrices['membership_price_state'] ?? 800);

// Update membership pricing function
if (!function_exists('updateMembershipPrices')) {
    function updateMembershipPrices($block, $tehsil, $district, $mandal, $state) {
        try {
            $db = getDbConnection();
            
            $prices = [
                'membership_price_block' => $block,
                'membership_price_tehsil' => $tehsil,
                'membership_price_district' => $district,
                'membership_price_mandal' => $mandal,
                'membership_price_state' => $state
            ];
            
            $db->beginTransaction();
            
            foreach ($prices as $key => $value) {
                // Check if setting exists
                $stmt = $db->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
                $stmt->execute([$key]);
                $exists = $stmt->fetchColumn();
                
                if ($exists) {
                    // Update existing setting
                    $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                    $stmt->execute([$value, $key]);
                } else {
                    // Insert new setting
                    $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
                    $stmt->execute([$key, $value]);
                }
            }
            
            $db->commit();
            return true;
            
        } catch (Exception $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            logError('Update membership prices error: ' . $e->getMessage());
            return false;
        }
    }
}

// Fetch payment configuration
if (!function_exists('getPaymentConfig')) {
    function getPaymentConfig() {
        try {
            $db = getDbConnection();
            $environment = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false) ? 'local' : 'live';
            
            // Fetch Razorpay config
            $stmt = $db->prepare("SELECT * FROM razorpay_config WHERE environment = ? AND is_active = 1");
            $stmt->execute([$environment]);
            $razorpayConfig = $stmt->fetch();
            
            // Fetch bank details
            $stmt = $db->prepare("SELECT * FROM bank_details WHERE environment = ?");
            $stmt->execute([$environment]);
            $bankDetails = $stmt->fetch();
            
            return array_merge($razorpayConfig ?: [], $bankDetails ?: []);
        } catch (Exception $e) {
            logError('Payment config error: ' . $e->getMessage());
            return [
                'razorpay_key_id' => 'rzp_test_lIpago1lHOcDRz',
                'razorpay_key_secret' => '0QLbpU2SMACvSMLiN005y5bv',
                'bank_name' => 'Bank OF India',
                'account_name' => 'Uma Foundation Charitable Trust',
                'account_number' => '771520110000273',
                'ifsc_code' => 'BKID0007715',
                'qr_code_image' => null
            ];
        }
    }
}

// Load payment configuration
$paymentConfig = getPaymentConfig();
define('RAZORPAY_KEY_ID', $paymentConfig['razorpay_key_id'] ?? 'rzp_test_lIpago1lHOcDRz');
define('RAZORPAY_KEY_SECRET', $paymentConfig['razorpay_key_secret'] ?? '0QLbpU2SMACvSMLiN005y5bv');
define('BANK_NAME', $paymentConfig['bank_name'] ?? 'Bank OF India');
define('ACCOUNT_NAME', $paymentConfig['account_name'] ?? 'Uma Foundation Charitable Trust');
define('ACCOUNT_NUMBER', $paymentConfig['account_number'] ?? '771520110000273');
define('IFSC_CODE', $paymentConfig['ifsc_code'] ?? 'BKID0007715');
define('QR_CODE_IMAGE', !empty($paymentConfig['qr_code_image']) ? SITE_URL . '/img/' . $paymentConfig['qr_code_image'] : '');

// Fetch SMTP configuration
if (!function_exists('getSMTPConfig')) {
    function getSMTPConfig() {
        static $smtpConfig = null;
        
        if ($smtpConfig === null) {
            try {
                $db = getDbConnection();
                $stmt = $db->prepare("SELECT * FROM smtp_settings WHERE id = 1 LIMIT 1");
                $stmt->execute();
                $smtpConfig = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Fallback if no config found
                if (!$smtpConfig) {
                    logError('No SMTP configuration found in smtp_settings table');
                    $smtpConfig = [
                        'host' => 'smtp.hostinger.com',
                        'port' => 465,
                        'username' => 'help@kisanex.com',
                        'password' => 'Facility@123#',
                        'encryption' => 'ssl'
                    ];
                }
            } catch (Exception $e) {
                logError('SMTP config error: ' . $e->getMessage());
                $smtpConfig = [
                    'host' => 'smtp.hostinger.com',
                    'port' => 465,
                    'username' => 'help@kisanex.com',
                    'password' => 'Facility@123#',
                    'encryption' => 'ssl'
                ];
            }
        }
        
        return $smtpConfig;
    }
}

// Email sending function using PHPMailer
if (!function_exists('sendEmail')) {
    function sendEmail($to, $subject, $body, $isHTML = true, $attachments = []) {
        // Check if PHPMailer is available
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            // Try to include PHPMailer manually if not using Composer
            $phpmailerPaths = [
                __DIR__ . '/../vendor/autoload.php',
                __DIR__ . '/../PHPMailer/src/PHPMailer.php',
                __DIR__ . '/../PHPMailer/src/SMTP.php',
                __DIR__ . '/../PHPMailer/src/Exception.php'
            ];
            
            foreach ($phpmailerPaths as $path) {
                if (file_exists($path)) {
                    if (strpos($path, 'autoload.php') !== false) {
                        require_once $path;
                        break;
                    } else {
                        require_once str_replace('PHPMailer.php', 'Exception.php', $path);
                        require_once str_replace('PHPMailer.php', 'PHPMailer.php', $path);
                        require_once str_replace('PHPMailer.php', 'SMTP.php', $path);
                        break;
                    }
                }
            }
        }
        
        if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            logError('PHPMailer not found. Please install PHPMailer.');
            return false;
        }

        try {
            $smtpConfig = getSMTPConfig();
            
            $mail = new PHPMailer(true);

            // Server settings
            $mail->isSMTP();
            $mail->Host = $smtpConfig['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtpConfig['username'];
            $mail->Password = $smtpConfig['password'];
            $mail->SMTPSecure = $smtpConfig['encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $smtpConfig['port'];

            // Recipients
            $mail->setFrom($smtpConfig['username'], ORGANIZATION_NAME);
            
            if (is_array($to)) {
                foreach ($to as $email => $name) {
                    if (is_numeric($email)) {
                        $mail->addAddress($name);
                    } else {
                        $mail->addAddress($email, $name);
                    }
                }
            } else {
                $mail->addAddress($to);
            }

            // Attachments
            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    if (is_array($attachment)) {
                        $mail->addAttachment($attachment['path'], $attachment['name'] ?? '');
                    } else {
                        $mail->addAttachment($attachment);
                    }
                }
            }

            // Content
            $mail->isHTML($isHTML);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body = $body;

            if ($isHTML) {
                $mail->AltBody = strip_tags($body);
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            logError('Email sending failed: ' . $mail->ErrorInfo);
            return false;
        }
    }
}

// Dynamic Authority Configuration
if (!function_exists('getAuthorityConfig')) {
    function getAuthorityConfig($type = null) {
        if ($type === null) {
            $type = getCurrentAuthority();
        }
        try {
            $db = getDbConnection();
            $names = [];
            
            $settings = ['chairman_name', 'chairman_title', 'secretary_name', 'secretary_title'];
            foreach ($settings as $setting) {
                $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
                $stmt->execute([$setting]);
                $result = $stmt->fetch();
                $names[$setting] = $result ? $result['setting_value'] : '';
            }
            
            // Use defaults if not set
            if (empty($names['chairman_name'])) $names['chairman_name'] = 'रौशन सिंह "चंदन"';
            if (empty($names['chairman_title'])) $names['chairman_title'] = 'संस्थापक /अध्यक्ष';
            if (empty($names['secretary_name'])) $names['secretary_name'] = 'रौशन सिंह "चंदन"';
            if (empty($names['secretary_title'])) $names['secretary_title'] = 'संस्थापक /अध्यक्ष';
            
            $authorities = [
                'president' => [
                    'chairman_name' => $names['chairman_name'],
                    'chairman_title' => $names['chairman_title'],
                    'signature_path' => SITE_URL . '/img/signature.png',
                    'secretary_name' => $names['secretary_name'],
                    'secretary_title' => $names['secretary_title'],
                    'secretary_signature_path' => SITE_URL . '/img/signature1.png'
                ],
                'secretary' => [
                    'chairman_name' => $names['secretary_name'],
                    'chairman_title' => $names['secretary_title'],
                    'signature_path' => SITE_URL . '/img/signature1.png',
                    'secretary_name' => $names['chairman_name'],
                    'secretary_title' => $names['chairman_title'],
                    'secretary_signature_path' => SITE_URL . '/img/signature.png'
                ]
            ];
            
            return $authorities[$type] ?? $authorities['president'];
        } catch (Exception $e) {
            logError('Authority config error: ' . $e->getMessage());
            $authorities = [
                'president' => [
                    'chairman_name' => 'रौशन सिंह "चंदन"',
                    'chairman_title' => 'संस्थापक /अध्यक्ष',
                    'signature_path' => SITE_URL . '/img/signature.png',
                    'secretary_name' => 'रौशन सिंह "चंदन"',
                    'secretary_title' => 'संस्थापक /अध्यक्ष',
                    'secretary_signature_path' => SITE_URL . '/img/signature1.png'
                ],
                'secretary' => [
                    'chairman_name' => 'रौशन सिंह "चंदन"',
                    'chairman_title' => 'संस्थापक /अध्यक्ष',
                    'signature_path' => SITE_URL . '/img/signature1.png',
                    'secretary_name' => 'रौशन सिंह "चंदन"',
                    'secretary_title' => 'संस्थापक /अध्यक्ष',
                    'secretary_signature_path' => SITE_URL . '/img/signature.png'
                ]
            ];
            
            return $authorities[$type] ?? $authorities['president'];
        }
    }
}

// Certificate Authority Configuration
define('CERTIFICATE_CHAIRMAN_NAME', 'रौशन सिंह "चंदन"');
define('CERTIFICATE_CHAIRMAN_TITLE', 'संस्थापक /अध्यक्ष');
define('CERTIFICATE_SECRETARY_NAME', 'रौशन सिंह "चंदन"');
define('CERTIFICATE_SECRETARY_TITLE', 'संस्थापक /अध्यक्ष');

// Get current authority setting from session
if (!function_exists('getCurrentAuthority')) {
    function getCurrentAuthority() {
        return $_SESSION['current_authority'] ?? 'president';
    }
}

// Set current authority
if (!function_exists('setCurrentAuthority')) {
    function setCurrentAuthority($type) {
        if (in_array($type, ['president', 'secretary'])) {
            $_SESSION['current_authority'] = $type;
            return true;
        }
        logError('Invalid authority type: ' . $type);
        return false;
    }
}

// Clear site config cache
if (!function_exists('clearSiteConfigCache')) {
    function clearSiteConfigCache() {
        // Clear static cache
        $reflection = new ReflectionFunction('getSiteConfig');
        $statics = $reflection->getStaticVariables();
        if (isset($statics['config'])) {
            $statics['config'] = null;
        }
        
        // Clear session cache
        if (isset($_SESSION['site_config'])) {
            unset($_SESSION['site_config']);
        }
        
        // Clear file-based cache if exists
        $cacheFile = __DIR__ . '/../cache/site_config.json';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }
        
        return true;
    }
}

// Sanitization function
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data, $encodeSpecialChars = true) {
        $data = trim($data);
        if ($encodeSpecialChars) {
            $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        return $data;
    }
}

// File upload function
if (!function_exists('uploadFile')) {
    function uploadFile($file, $uploadDir) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/x-icon', 'application/pdf'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, ICO, and PDF allowed.'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'File size too large. Maximum 5MB allowed.'];
        }
        
        $uploadPath = __DIR__ . '/../' . $uploadDir . '/';
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                return ['success' => false, 'message' => 'Failed to create upload directory.'];
            }
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $fullPath = $uploadPath . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            chmod($fullPath, 0644);
            return ['success' => true, 'filename' => $filename];
        } else {
            return ['success' => false, 'message' => 'Failed to upload file.'];
        }
    }
}

// Authentication helper functions
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin() {
        return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
    }
}

if (!function_exists('isCoordinator')) {
    function isCoordinator() {
        return isLoggedIn() && isset($_SESSION['user_type']) && in_array($_SESSION['user_type'], ['block_coordinator', 'district_coordinator', 'division_coordinator', 'admin']);
    }
}

if (!function_exists('isMember')) {
    function isMember($userId = null) {
        if ($userId === null) {
            return isLoggedIn() && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'member' && isset($_SESSION['status']) && $_SESSION['status'] === 'approved';
        }
        
        try {
            $db = getDbConnection();
            $stmt = $db->prepare("SELECT user_type, status FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            return $user && $user['user_type'] === 'member' && $user['status'] === 'approved';
        } catch (Exception $e) {
            logError('Error checking membership status: ' . $e->getMessage());
            return false;
        }
    }
}

// CSRF Token functions
if (!function_exists('generateCSRF')) {
    function generateCSRF() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verifyCSRF')) {
    function verifyCSRF($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Get membership price by type
if (!function_exists('getMembershipPrice')) {
    function getMembershipPrice($membershipType) {
        $prices = [
            'block' => BLOCK_MEMBERSHIP_PRICE,
            'tehsil' => TEHSIL_MEMBERSHIP_PRICE,
            'district' => DISTRICT_MEMBERSHIP_PRICE,
            'mandal' => MANDAL_MEMBERSHIP_PRICE,
            'state' => STATE_MEMBERSHIP_PRICE
        ];
        
        return isset($prices[$membershipType]) ? $prices[$membershipType] : 0;
    }
}

// Get membership type name in Hindi
if (!function_exists('getMembershipTypeName')) {
    function getMembershipTypeName($membershipType) {
        $membershipNames = [
            'block' => 'ब्लॉक सदस्यता',
            'tehsil' => 'तहसील सदस्यता',
            'district' => 'जिला सदस्यता',
            'mandal' => 'संभाग सदस्यता',
            'state' => 'राज्य सदस्यता'
        ];
        return $membershipNames[$membershipType] ?? 'अज्ञात सदस्यता';
    }
}

// Convert number to Hindi words - FIXED VERSION
if (!function_exists('numberToHindiWords')) {
    function numberToHindiWords($number) {
        // Handle decimal numbers by removing decimal part
        $number = (int)floor($number);
        
        // Handle zero and negative numbers
        if ($number <= 0) {
            return 'शून्य';
        }
        
        $ones = array(
            0 => '', 1 => 'एक', 2 => 'दो', 3 => 'तीन', 4 => 'चार', 5 => 'पांच',
            6 => 'छह', 7 => 'सात', 8 => 'आठ', 9 => 'नौ', 10 => 'दस',
            11 => 'ग्यारह', 12 => 'बारह', 13 => 'तेरह', 14 => 'चौदह', 15 => 'पंद्रह',
            16 => 'सोलह', 17 => 'सत्रह', 18 => 'अठारह', 19 => 'उन्नीस'
        );
        
        $tens = array(
            2 => 'बीस', 3 => 'तीस', 4 => 'चालीस', 5 => 'पचास',
            6 => 'साठ', 7 => 'सत्तर', 8 => 'अस्सी', 9 => 'नब्बे'
        );
        
        $specialTwenties = array(
            20 => 'बीस', 21 => 'इक्कीस', 22 => 'बाईस', 23 => 'तेईस', 24 => 'चौबीस',
            25 => 'पच्चीस', 26 => 'छब्बीस', 27 => 'सत्ताईस', 28 => 'अठाईस', 29 => 'उनतीस'
        );
        
        $specialThirties = array(
            30 => 'तीस', 31 => 'इकतीस', 32 => 'बत्तीस', 33 => 'तैंतीस', 34 => 'चौंतीस',
            35 => 'पैंतीस', 36 => 'छत्तीस', 37 => 'सैंतीस', 38 => 'अड़तीस', 39 => 'उनचालीस'
        );
        
        if ($number == 0) {
            return 'शून्य';
        } elseif ($number < 20) {
            return $ones[$number];
        } elseif ($number < 30) {
            return $specialTwenties[$number] ?? ($tens[2] . ' ' . $ones[$number - 20]);
        } elseif ($number < 40) {
            return $specialThirties[$number] ?? ($tens[3] . ' ' . $ones[$number - 30]);
        } elseif ($number < 100) {
            $ten = intval($number / 10);
            $one = $number % 10;
            return $tens[$ten] . ($one > 0 ? ' ' . $ones[$one] : '');
        } elseif ($number < 1000) {
            $hundred = intval($number / 100);
            $remainder = $number % 100;
            return $ones[$hundred] . ' सौ' . ($remainder > 0 ? ' ' . numberToHindiWords($remainder) : '');
        } elseif ($number < 100000) {
            $thousand = intval($number / 1000);
            $remainder = $number % 1000;
            return numberToHindiWords($thousand) . ' हजार' . ($remainder > 0 ? ' ' . numberToHindiWords($remainder) : '');
        } elseif ($number < 10000000) {
            $lakh = intval($number / 100000);
            $remainder = $number % 100000;
            return numberToHindiWords($lakh) . ' लाख' . ($remainder > 0 ? ' ' . numberToHindiWords($remainder) : '');
        } elseif ($number < 1000000000) {
            $crore = intval($number / 10000000);
            $remainder = $number % 10000000;
            return numberToHindiWords($crore) . ' करोड़' . ($remainder > 0 ? ' ' . numberToHindiWords($remainder) : '');
        } else {
            return 'बहुत बड़ी संख्या';
        }
    }
}

// Generate unique ID
if (!function_exists('generateUniqueId')) {
    function generateUniqueId($prefix = 'CGMA') {
        return $prefix . date('Ymd') . rand(1000, 9999);
    }
}

// Format currency
if (!function_exists('formatCurrency')) {
    function formatCurrency($amount) {
        return '₹' . number_format($amount, 2);
    }
}

// Redirect function
if (!function_exists('redirectTo')) {
    function redirectTo($url) {
        header('Location: ' . $url);
        exit;
    }
}

// Number to words in English (helper function)
if (!function_exists('numberToWords')) {
    function numberToWords($number) {
        $ones = array(
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'
        );
        
        $tens = array(
            2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
            6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
        );
        
        $number = (int)floor($number);
        
        if ($number == 0) {
            return 'Zero';
        } elseif ($number < 20) {
            return $ones[$number];
        } elseif ($number < 100) {
            $ten = intval($number / 10);
            $one = $number % 10;
            return $tens[$ten] . ($one > 0 ? ' ' . $ones[$one] : '');
        } elseif ($number < 1000) {
            $hundred = intval($number / 100);
            $remainder = $number % 100;
            return $ones[$hundred] . ' Hundred' . ($remainder > 0 ? ' ' . numberToWords($remainder) : '');
        } elseif ($number < 1000000) {
            $thousand = intval($number / 1000);
            $remainder = $number % 1000;
            return numberToWords($thousand) . ' Thousand' . ($remainder > 0 ? ' ' . numberToWords($remainder) : '');
        } else {
            return 'Very Large Number';
        }
    }
}

// Validate Indian mobile number
if (!function_exists('validateMobileNumber')) {
    function validateMobileNumber($mobile) {
        return preg_match('/^[6-9]\d{9}$/', $mobile);
    }
}

// Validate Aadhar number
if (!function_exists('validateAadharNumber')) {
    function validateAadharNumber($aadhar) {
        return preg_match('/^\d{12}$/', $aadhar);
    }
}

// Validate PAN number
if (!function_exists('validatePanNumber')) {
    function validatePanNumber($pan) {
        return preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', strtoupper($pan));
    }
}

// Get age from date of birth
if (!function_exists('getAge')) {
    function getAge($dob) {
        $birthDate = new DateTime($dob);
        $today = new DateTime('today');
        return $birthDate->diff($today)->y;
    }
}

// Create img directory and subdirectories
$imgDir = __DIR__ . '/../img/';
if (!is_dir($imgDir)) {
    mkdir($imgDir, 0755, true);
}

// Create sub-directories for organized file storage
$subDirs = ['users', 'payments', 'profiles', 'documents', 'site_config'];
foreach ($subDirs as $subDir) {
    $fullPath = $imgDir . $subDir . '/';
    if (!is_dir($fullPath)) {
        mkdir($fullPath, 0755, true);
    }
}

// Create templates directory if it doesn't exist
$templatesDir = __DIR__ . '/../templates/';
if (!is_dir($templatesDir)) {
    mkdir($templatesDir, 0755, true);
}

// Create logs directory if it doesn't exist
$logsDir = __DIR__ . '/../logs/';
if (!is_dir($logsDir)) {
    mkdir($logsDir, 0755, true);
}

// Create cache directory if it doesn't exist
$cacheDir = __DIR__ . '/../cache/';
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0755, true);
}
?>