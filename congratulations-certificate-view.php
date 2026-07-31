<?php
require_once 'config/config.php';
require_once __DIR__ . '/includes/congratulations_certificate_helpers.php';

$certificateId = (int) ($_GET['id'] ?? 0);

if ($certificateId <= 0) {
    http_response_code(404);
    echo 'E-certificate not found.';
    exit;
}

try {
    $db = getDbConnection();
    ensureCongratulationsCertificatesTable($db);

    $stmt = $db->prepare("SELECT * FROM congratulations_certificates WHERE id = ? LIMIT 1");
    $stmt->execute([$certificateId]);
    $certificate = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$certificate) {
        http_response_code(404);
        echo 'E-certificate not found.';
        exit;
    }

    $filePath = congratulationsCertificateGetDiskPath($certificate);
    if (!is_file($filePath)) {
        http_response_code(404);
        echo 'The requested file is missing.';
        exit;
    }

    $contentType = congratulationsCertificateContentType($certificate);
    if (function_exists('mime_content_type')) {
        $detectedType = @mime_content_type($filePath);
        if (!empty($detectedType)) {
            $contentType = $detectedType;
        }
    }

    header('X-Content-Type-Options: nosniff');
    header('Content-Type: ' . $contentType);
    header('Content-Length: ' . filesize($filePath));

    if (congratulationsCertificateIsPdf($certificate)) {
        header('Content-Disposition: attachment; filename="' . congratulationsCertificateDownloadName($certificate) . '"');
    } else {
        header('Content-Disposition: inline; filename="' . congratulationsCertificateDownloadName($certificate) . '"');
    }

    readfile($filePath);
    exit;
} catch (Exception $e) {
    logError('E-certificate view error: ' . $e->getMessage());
    http_response_code(500);
    echo 'Unable to open the requested congratulations-certificate.';
    exit;
}
