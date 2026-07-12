<?php
/**
 * Performance-Optimized Configuration for Rawbit Foundation
 * Based on your working config.php with safe performance enhancements
 * All existing features preserved - certificates, site config, SMTP working
 */

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Start session with security settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.gc_maxlifetime', 7200); // 2 hours
    session_start();
}

// Detect environment (cached in static variable for performance)
function isLocalEnvironment() {
    static $isLocal = null;
    if ($isLocal === null) {
        $isLocal = (
            strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
            strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false
        );
    }
    return $isLocal;
}

$isLocalEnvironment = isLocalEnvironment();

// Database and site configuration
if ($isLocalEnvironment) {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'pressclubwelfare');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('SITE_URL', 'http://localhost/pressclubwelfare');
} else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'u126736286_pressclubwell');
    define('DB_USER', 'u126736286_pressclubwell');
    define('DB_PASS', 'G7m$kP9v!nT2qR8x');
    define('SITE_URL', 'https://pressclubwelfareworldwidefoundation.org');
}

define('SITE_NAME', 'Press Club Welfare Worldwide Foundation');
define('ADMIN_URL', rtrim(SITE_URL, '/') . '/admin/');
define('ORGANIZATION_NAME', 'Press Club Welfare Worldwide Foundation');
define('ORGANIZATION_NAME_HINDI', 'Supporting journalists and media professionals globally');
define('ORGANIZATION_NAME_SHORT', 'PCWWF');
define('REGISTRATION_INFO', 'Registration Number: U89000DL2024NPL437018');

// Certificate Authority Configuration
define('CERTIFICATE_CHAIRMAN_NAME', '');
define('CERTIFICATE_CHAIRMAN_TITLE', '');
define('CERTIFICATE_SECRETARY_NAME', '');
define('CERTIFICATE_SECRETARY_TITLE', '');

// Set default timezone
date_default_timezone_set('Asia/Kolkata');

// Error reporting based on environment
if ($isLocalEnvironment) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Performance optimization - Enable output compression
if (!$isLocalEnvironment && extension_loaded('zlib')) {
    ini_set('zlib.output_compression', 'On');
    ini_set('zlib.output_compression_level', '5');
}

// Database connection with persistent connection for performance
if (!function_exists('getDbConnection')) {
    function getDbConnection() {
        static $connection = null;
        
        if ($connection === null) {
            try {
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => true, // Connection pooling for performance
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];
                
                $connection = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    $options
                );
            } catch (PDOException $e) {
                logError('Database connection error: ' . $e->getMessage());
                die('Connection failed. Please try again later.');
            }
        }
        
        return $connection;
    }
}

// Error logging function with log rotation
if (!function_exists('logError')) {
    function logError($message, $file = 'error.log') {
        $logDir = __DIR__ . '/../logs/';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . $file;
        $maxSize = 5 * 1024 * 1024; // 5MB
        
        // Rotate log if too large
        if (file_exists($logFile) && filesize($logFile) > $maxSize) {
            @rename($logFile, $logDir . date('Y-m-d_His_') . $file);
        }
        
        @error_log(date('[Y-m-d H:i:s] ') . $message . PHP_EOL, 3, $logFile);
    }
}

// Clear site config cache function (CRITICAL for site_config.php to work)
if (!function_exists('clearSiteConfigCache')) {
    function clearSiteConfigCache() {
        // Clear static cache by forcing refresh
        getSiteConfig(true);
        
        // Clear session cache
        if (isset($_SESSION['site_config'])) {
            unset($_SESSION['site_config']);
        }
        
        // Clear file-based cache if exists
        $cacheFile = __DIR__ . '/../cache/site_config.json';
        if (file_exists($cacheFile)) {
            @unlink($cacheFile);
        }
        
        return true;
    }
}

// Get site configuration with multi-level caching for performance
if (!function_exists('getSiteConfig')) {
    function getSiteConfig($forceRefresh = false) {
        static $config = null;
        
        // Level 1: Static variable cache (fastest)
        if (!$forceRefresh && $config !== null) {
            return $config;
        }
        
        // Level 2: Session cache
        if (!$forceRefresh && isset($_SESSION['site_config'])) {
            $config = $_SESSION['site_config'];
            return $config;
        }
        
        // Level 3: File cache (faster than database)
        $cacheFile = __DIR__ . '/../cache/site_config.json';
        if (!$forceRefresh && file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if ($cached && is_array($cached)) {
                $config = $cached;
                $_SESSION['site_config'] = $config;
                return $config;
            }
        }
        
        // Level 4: Database (slowest, used when cache is empty)
        try {
            $db = getDbConnection();
            $stmt = $db->prepare("SELECT * FROM site_config WHERE id = 1 LIMIT 1");
            $stmt->execute();
            $config = $stmt->fetch();
            
            if (!$config) {
                logError('No site configuration found in site_config table');
                $config = ['website_url' => SITE_URL];
            }
            
            // Store in all cache levels
            $_SESSION['site_config'] = $config;
            @file_put_contents($cacheFile, json_encode($config));
            
        } catch (Exception $e) {
            logError('Site config error: ' . $e->getMessage());
            $config = ['website_url' => SITE_URL];
        }
        
        return $config;
    }
}

// Load membership pricing with caching
if (!function_exists('getMembershipPrices')) {
    function getMembershipPrices($forceRefresh = false) {
        static $membershipPrices = null;

        if (!$forceRefresh && $membershipPrices !== null) {
            return $membershipPrices;
        }

        $membershipPrices = [];
        try {
            $db = getDbConnection();
            $stmt = $db->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'membership_price_%'");
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $membershipPrices[$row['setting_key']] = (int)$row['setting_value'];
            }
            
            if (empty($membershipPrices)) {
                $membershipPrices = [
                    'membership_price_active' => 101,
                    'membership_price_gram_panchayat' => 151,
                    'membership_price_block' => 251,
                    'membership_price_tehsil' => 350,
                    'membership_price_district' => 501,
                    'membership_price_mandal' => 801,
                    'membership_price_state' => 999,
                    'membership_price_national' => 1201
                ];
            }
        } catch (Exception $e) {
            logError('Membership pricing error: ' . $e->getMessage());
            $membershipPrices = [
                'membership_price_active' => 101,
                'membership_price_gram_panchayat' => 151,
                'membership_price_block' => 251,
                'membership_price_tehsil' => 350,
                'membership_price_district' => 501,
                'membership_price_mandal' => 801,
                'membership_price_state' => 999,
                'membership_price_national' => 1201
            ];
        }

        return $membershipPrices;
    }
}

// Define constants using the fetched prices
$membershipPrices = getMembershipPrices();
define('ACTIVE_MEMBERSHIP_PRICE', $membershipPrices['membership_price_active'] ?? 101);
define('GRAM_PANCHAYAT_MEMBERSHIP_PRICE', $membershipPrices['membership_price_gram_panchayat'] ?? 151);
define('BLOCK_MEMBERSHIP_PRICE', $membershipPrices['membership_price_block'] ?? 251);
define('TEHSIL_MEMBERSHIP_PRICE', $membershipPrices['membership_price_tehsil'] ?? 350);
define('DISTRICT_MEMBERSHIP_PRICE', $membershipPrices['membership_price_district'] ?? 501);
define('MANDAL_MEMBERSHIP_PRICE', $membershipPrices['membership_price_mandal'] ?? 801);
define('STATE_MEMBERSHIP_PRICE', $membershipPrices['membership_price_state'] ?? 999);
define('NATIONAL_MEMBERSHIP_PRICE', $membershipPrices['membership_price_national'] ?? 1201);

// Update membership pricing function with cache clearing
if (!function_exists('updateMembershipPrices')) {
    function updateMembershipPrices($active, $gram_panchayat, $block, $tehsil, $district, $mandal, $state, $national) {
        try {
            $db = getDbConnection();
            
            $prices = [
                'membership_price_active' => $active,
                'membership_price_gram_panchayat' => $gram_panchayat,
                'membership_price_block' => $block,
                'membership_price_tehsil' => $tehsil,
                'membership_price_district' => $district,
                'membership_price_mandal' => $mandal,
                'membership_price_state' => $state,
                'membership_price_national' => $national
            ];
            
            $db->beginTransaction();
            
            foreach ($prices as $key => $value) {
                $stmt = $db->prepare("SELECT COUNT(*) FROM settings WHERE setting_key = ?");
                $stmt->execute([$key]);
                $exists = $stmt->fetchColumn();
                
                if ($exists) {
                    $stmt = $db->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
                    $stmt->execute([$value, $key]);
                } else {
                    $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)");
                    $stmt->execute([$key, $value]);
                }
            }
            
            $db->commit();
            
            // Clear cache and refresh
            getMembershipPrices(true);
            
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

// Fetch payment configuration with caching
if (!function_exists('getPaymentConfig')) {
    function getPaymentConfig($forceRefresh = false) {
        static $paymentConfig = null;
        
        if (!$forceRefresh && $paymentConfig !== null) {
            return $paymentConfig;
        }
        
        try {
            $db = getDbConnection();
            $environment = isLocalEnvironment() ? 'local' : 'live';
            
            $stmt = $db->prepare("SELECT * FROM razorpay_config WHERE environment = ? AND is_active = 1 LIMIT 1");
            $stmt->execute([$environment]);
            $razorpayConfig = $stmt->fetch();
            
            $stmt = $db->prepare("SELECT * FROM bank_details WHERE environment = ? LIMIT 1");
            $stmt->execute([$environment]);
            $bankDetails = $stmt->fetch();
            
            $paymentConfig = array_merge($razorpayConfig ?: [], $bankDetails ?: []);
            
            if (empty($paymentConfig)) {
                $paymentConfig = [
                    'razorpay_key_id' => 'rzp_test_RIeIGa0uK9lUA5',
                    'razorpay_key_secret' => 'meZ2NWpgWyMVY3fA2bCwSNIL',
                    'bank_name' => 'Canara Bank',
                    'account_name' => 'Rawbit Foundation',
                    'account_number' => '120036505291',
                    'ifsc_code' => 'CNRB0002931',
                    'qr_code_image' => null
                ];
            }
        } catch (Exception $e) {
            logError('Payment config error: ' . $e->getMessage());
            $paymentConfig = [
                'razorpay_key_id' => 'rzp_test_RIeIGa0uK9lUA5',
                'razorpay_key_secret' => 'meZ2NWpgWyMVY3fA2bCwSNIL',
                'bank_name' => 'Canara Bank',
                'account_name' => 'Rawbit Foundation',
                'account_number' => '120036505291',
                'ifsc_code' => 'CNRB0002931',
                'qr_code_image' => null
            ];
        }
        
        return $paymentConfig;
    }
}

// Load payment configuration
$paymentConfig = getPaymentConfig();
define('RAZORPAY_KEY_ID', $paymentConfig['razorpay_key_id'] ?? 'rzp_test_RIeIGa0uK9lUA5');
define('RAZORPAY_KEY_SECRET', $paymentConfig['razorpay_key_secret'] ?? 'meZ2NWpgWyMVY3fA2bCwSNIL');
define('BANK_NAME', $paymentConfig['bank_name'] ?? 'Canara Bank');
define('ACCOUNT_NAME', $paymentConfig['account_name'] ?? 'Rawbit Foundation');
define('ACCOUNT_NUMBER', $paymentConfig['account_number'] ?? '120036505291');
define('IFSC_CODE', $paymentConfig['ifsc_code'] ?? 'CNRB0002931');
define('QR_CODE_IMAGE', !empty($paymentConfig['qr_code_image']) ? SITE_URL . '/img/' . $paymentConfig['qr_code_image'] : '');

// Sanitization function (unchanged - working perfectly)
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data, $encodeSpecialChars = true) {
        if (is_array($data)) {
            return array_map(function($item) use ($encodeSpecialChars) {
                return sanitizeInput($item, $encodeSpecialChars);
            }, $data);
        }
        
        $data = trim($data);
        $data = stripslashes($data);
        
        if ($encodeSpecialChars) {
            $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        return $data;
    }
}

// File upload function with better validation
if (!function_exists('uploadFile')) {
    function uploadFile($file, $uploadDir, $allowedTypes = null, $maxSize = null) {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'message' => 'Invalid file upload.'];
        }
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Upload failed with error code: ' . $file['error']];
        }
        
        $allowedTypes = $allowedTypes ?? ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/x-icon', 'application/pdf'];
        $maxSize = $maxSize ?? (5 * 1024 * 1024);
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid file type.'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'File too large. Maximum: ' . ($maxSize / 1024 / 1024) . 'MB'];
        }
        
        $uploadPath = __DIR__ . '/../' . trim($uploadDir, '/') . '/';
        if (!is_dir($uploadPath)) {
            if (!@mkdir($uploadPath, 0755, true)) {
                return ['success' => false, 'message' => 'Failed to create upload directory.'];
            }
        }
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('file_', true) . '.' . $extension;
        $fullPath = $uploadPath . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            @chmod($fullPath, 0644);
            return ['success' => true, 'filename' => $filename, 'path' => $uploadDir . '/' . $filename];
        }
        
        return ['success' => false, 'message' => 'Failed to move uploaded file.'];
    }
}

// Authentication helper functions (unchanged - working perfectly)
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
        return isLoggedIn() && isset($_SESSION['user_type']) && 
               in_array($_SESSION['user_type'], ['coordinator', 'admin']);
    }
}

if (!function_exists('isMember')) {
    function isMember($userId = null) {
        if ($userId === null) {
            return isLoggedIn() && 
                   isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'member' && 
                   isset($_SESSION['status']) && $_SESSION['status'] === 'approved';
        }
        
        try {
            $db = getDbConnection();
            $stmt = $db->prepare("SELECT user_type, status FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            return $user && $user['user_type'] === 'member' && $user['status'] === 'approved';
        } catch (Exception $e) {
            logError('Error checking membership status: ' . $e->getMessage());
            return false;
        }
    }
}

// CSRF Token functions (unchanged - working perfectly)
if (!function_exists('generateCSRF')) {
    function generateCSRF() {
        if (!isset($_SESSION['csrf_token']) || 
            !isset($_SESSION['csrf_token_time']) || 
            (time() - $_SESSION['csrf_token_time']) > 3600) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verifyCSRF')) {
    function verifyCSRF($token) {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        if ((time() - $_SESSION['csrf_token_time']) > 3600) {
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Get membership price by type
if (!function_exists('getMembershipPrice')) {
    function getMembershipPrice($membershipType) {
        $prices = getMembershipPrices();
        $priceKeys = [
            'active' => 'membership_price_active',
            'gram_panchayat' => 'membership_price_gram_panchayat',
            'block' => 'membership_price_block',
            'tehsil' => 'membership_price_tehsil',
            'district' => 'membership_price_district',
            'mandal' => 'membership_price_mandal',
            'state' => 'membership_price_state',
            'national' => 'membership_price_national'
        ];
        
        $key = $priceKeys[$membershipType] ?? null;
        return $key ? ($prices[$key] ?? 0) : 0;
    }
}

// Get membership type name in Hindi (unchanged - working perfectly)
if (!function_exists('getMembershipTypeName')) {
    function getMembershipTypeName($membershipType) {
        static $membershipNames = [
            'active' => 'सक्रिय सदस्यता',
            'gram_panchayat' => 'ग्राम पंचायत सदस्यता',
            'block' => 'ब्लॉक सदस्यता',
            'tehsil' => 'तहसील सदस्यता',
            'district' => 'जिला सदस्यता',
            'mandal' => 'मंडल सदस्यता',
            'state' => 'राज्य सदस्यता',
            'national' => 'राष्ट्रीय सदस्यता'
        ];
        return $membershipNames[$membershipType] ?? 'अज्ञात सदस्यता';
    }
}

// Get designations by membership type with caching
if (!function_exists('getDesignationsByMembershipType')) {
    function getDesignationsByMembershipType($membershipType) {
        static $cache = [];
        
        if (isset($cache[$membershipType])) {
            return $cache[$membershipType];
        }
        
        try {
            $db = getDbConnection();
            $stmt = $db->prepare("
                SELECT designation, designation_hindi 
                FROM membership_designations 
                WHERE membership_type = ? AND status = 'active' 
                ORDER BY sort_order
            ");
            $stmt->execute([$membershipType]);
            $designations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $cache[$membershipType] = $designations;
            return $designations;
        } catch (Exception $e) {
            logError('Error fetching designations: ' . $e->getMessage());
            return [];
        }
    }
}

// Convert number to Hindi words (unchanged - working perfectly)
if (!function_exists('numberToHindiWords')) {
    function numberToHindiWords($number) {
        $number = (int)floor($number);
        
        if ($number <= 0) return 'शून्य';
        
        $ones = [
            0 => '', 1 => 'एक', 2 => 'दो', 3 => 'तीन', 4 => 'चार', 5 => 'पांच',
            6 => 'छह', 7 => 'सात', 8 => 'आठ', 9 => 'नौ', 10 => 'दस',
            11 => 'ग्यारह', 12 => 'बारह', 13 => 'तेरह', 14 => 'चौदह', 15 => 'पंद्रह',
            16 => 'सोलह', 17 => 'सत्रह', 18 => 'अठारह', 19 => 'उन्नीस'
        ];
        
        $tens = [
            2 => 'बीस', 3 => 'तीस', 4 => 'चालीस', 5 => 'पचास',
            6 => 'साठ', 7 => 'सत्तर', 8 => 'अस्सी', 9 => 'नब्बे'
        ];
        
        $specialTwenties = [
            20 => 'बीस', 21 => 'इक्कीस', 22 => 'बाईस', 23 => 'तेईस', 24 => 'चौबीस',
            25 => 'पच्चीस', 26 => 'छब्बीस', 27 => 'सत्ताईस', 28 => 'अट्ठाईस', 29 => 'उनतीस'
        ];
        
        $specialThirties = [
            30 => 'तीस', 31 => 'इकतीस', 32 => 'बत्तीस', 33 => 'तैंतीस', 34 => 'चौंतीस',
            35 => 'पैंतीस', 36 => 'छत्तीस', 37 => 'सैंतीस', 38 => 'अड़तीस', 39 => 'उनचालीस'
        ];
        
        if ($number < 20) return $ones[$number];
        if ($number < 30) return $specialTwenties[$number] ?? ($tens[2] . ' ' . $ones[$number - 20]);
        if ($number < 40) return $specialThirties[$number] ?? ($tens[3] . ' ' . $ones[$number - 30]);
        if ($number < 100) {
            $ten = intval($number / 10);
            $one = $number % 10;
            return $tens[$ten] . ($one > 0 ? ' ' . $ones[$one] : '');
        }
        if ($number < 1000) {
            $hundred = intval($number / 100);
            $remainder = $number % 100;
            return $ones[$hundred] . ' सौ' . ($remainder > 0 ? ' ' . numberToHindiWords($remainder) : '');
        }
        if ($number < 100000) {
            $thousand = intval($number / 1000);
            $remainder = $number % 1000;
            return numberToHindiWords($thousand) . ' हजार' . ($remainder > 0 ? ' ' . numberToHindiWords($remainder) : '');
        }
        if ($number < 10000000) {
            $lakh = intval($number / 100000);
            $remainder = $number % 100000;
            return numberToHindiWords($lakh) . ' लाख' . ($remainder > 0 ? ' ' . numberToHindiWords($remainder) : '');
        }
        if ($number < 1000000000) {
            $crore = intval($number / 10000000);
            $remainder = $number % 10000000;
            return numberToHindiWords($crore) . ' करोड़' . ($remainder > 0 ? ' ' . numberToHindiWords($remainder) : '');
        }
        
        return 'बहुत बड़ी संख्या';
    }
}

// Generate unique ID (UPDATED PREFIX TO GHDAF)
if (!function_exists('generateUniqueId')) {
    function generateUniqueId($prefix = 'GHDAF') {
        return $prefix . date('Ymd') . strtoupper(substr(uniqid(), -6));
    }
}

// Format currency (unchanged - working perfectly)
if (!function_exists('formatCurrency')) {
    function formatCurrency($amount, $decimals = 2) {
        return '₹' . number_format($amount, $decimals);
    }
}

// Redirect function (unchanged - working perfectly)
if (!function_exists('redirectTo')) {
    function redirectTo($url, $statusCode = 302) {
        if (headers_sent()) {
            echo '<script>window.location.href="' . htmlspecialchars($url) . '";</script>';
            exit;
        }
        header('Location: ' . $url, true, $statusCode);
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
                        'username' => 'info@rawbitfoundation.org',
                        'password' => '^Lc5YdP|',
                        'encryption' => 'ssl'
                    ];
                }
            } catch (Exception $e) {
                logError('SMTP config error: ' . $e->getMessage());
                $smtpConfig = [
                    'host' => 'smtp.hostinger.com',
                    'port' => 465,
                    'username' => 'info@rawbitfoundation.org',
                    'password' => '^Lc5YdP|',
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