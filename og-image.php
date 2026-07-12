<?php
// og-image.php - Generates Open Graph images dynamically
session_start();
require_once 'config/config.php';

$db = getDbConnection();
$db->exec("SET NAMES utf8mb4");

function news_image_src(?string $file): string {
    if ($file) {
        $p1 = __DIR__ . "/uploads/news/" . $file;
        if (is_file($p1)) return $p1;
        $p2 = __DIR__ . "/img/" . $file;
        if (is_file($p2)) return $p2;
    }
    return __DIR__ . "/img/news-placeholder.jpg";
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = isset($_GET['type']) ? $_GET['type'] : 'news';

if ($id <= 0 || $type !== 'news') {
    // Return default image
    header('Content-Type: image/jpeg');
    $defaultImage = __DIR__ . '/img/news-placeholder.jpg';
    if (file_exists($defaultImage)) {
        readfile($defaultImage);
    }
    exit;
}

// Fetch article
$stmt = $db->prepare("
    SELECT id, title, image, author, created_at
    FROM news
    WHERE id = :id AND status = 'active'
    LIMIT 1
");
$stmt->execute([':id' => $id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    header('Content-Type: image/jpeg');
    $defaultImage = __DIR__ . '/img/news-placeholder.jpg';
    if (file_exists($defaultImage)) {
        readfile($defaultImage);
    }
    exit;
}

// Check if GD extension is available
if (!extension_loaded('gd')) {
    // Fallback to original image
    $imagePath = news_image_src($article['image'] ?? null);
    if (file_exists($imagePath)) {
        $imageInfo = getimagesize($imagePath);
        header('Content-Type: ' . $imageInfo['mime']);
        readfile($imagePath);
    }
    exit;
}

// Generate Open Graph image with overlay
$imagePath = news_image_src($article['image'] ?? null);
$title = $article['title'] ?? '';
$author = $article['author'] ?? 'CGMA Team';
$date = date('d M Y', strtotime($article['created_at']));

// Create base image
$width = 1200;
$height = 630;
$image = imagecreatetruecolor($width, $height);

// Colors
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
$primary = imagecolorallocate($image, 0, 123, 255); // Bootstrap primary color
$gray = imagecolorallocate($image, 108, 117, 125);
$overlay = imagecolorallocatealpha($image, 0, 0, 0, 50); // Semi-transparent overlay

// Fill background
imagefill($image, 0, 0, $white);

// Load and resize background image
if (file_exists($imagePath)) {
    $bgImage = null;
    $imageInfo = getimagesize($imagePath);
    
    switch ($imageInfo[2]) {
        case IMAGETYPE_JPEG:
            $bgImage = imagecreatefromjpeg($imagePath);
            break;
        case IMAGETYPE_PNG:
            $bgImage = imagecreatefrompng($imagePath);
            break;
        case IMAGETYPE_GIF:
            $bgImage = imagecreatefromgif($imagePath);
            break;
    }
    
    if ($bgImage) {
        // Resize and copy background image
        imagecopyresampled($image, $bgImage, 0, 0, 0, 0, $width, $height, 
                          imagesx($bgImage), imagesy($bgImage));
        
        // Add semi-transparent overlay
        imagefilledrectangle($image, 0, $height - 200, $width, $height, $overlay);
        
        imagedestroy($bgImage);
    }
}

// Add CGMA logo/branding (if logo exists)
$logoPath = __DIR__ . '/img/logo.png';
if (file_exists($logoPath)) {
    $logo = imagecreatefrompng($logoPath);
    if ($logo) {
        $logoWidth = 80;
        $logoHeight = 80;
        $logoX = $width - $logoWidth - 30;
        $logoY = 30;
        
        imagecopyresampled($image, $logo, $logoX, $logoY, 0, 0, 
                          $logoWidth, $logoHeight, imagesx($logo), imagesy($logo));
        imagedestroy($logo);
    }
}

// Load fonts (fallback to built-in if custom fonts not available)
$titleFont = __DIR__ . '/fonts/Bakbak-One.ttf';
$textFont = __DIR__ . '/fonts/Open-Sans.ttf';
$useTTF = file_exists($titleFont) && file_exists($textFont);

// Add title text
$titleY = $height - 150;
$titleSize = $useTTF ? 24 : 5;
$maxTitleWidth = $width - 60;

// Wrap title text
$wrappedTitle = wordwrap($title, 50, "\n", true);
$titleLines = explode("\n", $wrappedTitle);
$maxLines = 3; // Maximum 3 lines for title

for ($i = 0; $i < min(count($titleLines), $maxLines); $i++) {
    $line = $titleLines[$i];
    if ($i == $maxLines - 1 && count($titleLines) > $maxLines) {
        $line = substr($line, 0, -3) . '...';
    }
    
    if ($useTTF) {
        imagettftext($image, $titleSize, 0, 30, $titleY + ($i * 35), $white, $titleFont, $line);
    } else {
        imagestring($image, $titleSize, 30, $titleY + ($i * 25), $line, $white);
    }
}

// Add author and date
$metaY = $height - 50;
$metaSize = $useTTF ? 14 : 3;
$metaText = "By " . $author . " | " . $date;

if ($useTTF) {
    imagettftext($image, $metaSize, 0, 30, $metaY, $gray, $textFont, $metaText);
} else {
    imagestring($image, $metaSize, 30, $metaY, $metaText, $gray);
}

// Add "CGMA News" watermark
$watermarkText = "CGMA समाचार";
$watermarkSize = $useTTF ? 16 : 4;
$watermarkY = 60;

if ($useTTF) {
    imagettftext($image, $watermarkSize, 0, 30, $watermarkY, $primary, $titleFont, $watermarkText);
} else {
    imagestring($image, $watermarkSize, 30, $watermarkY, $watermarkText, $primary);
}

// Output image
header('Content-Type: image/jpeg');
header('Cache-Control: public, max-age=86400'); // Cache for 24 hours
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

imagejpeg($image, null, 85);
imagedestroy($image);
?>