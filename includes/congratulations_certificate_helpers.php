<?php
if (!function_exists('ensureCongratulationsCertificatesTable')) {
    function ensureCongratulationsCertificatesTable(PDO $db): void
    {
        static $tableEnsured = false;

        if ($tableEnsured) {
            return;
        }

        $db->exec("
            CREATE TABLE IF NOT EXISTS congratulations_certificates (
                id INT(11) NOT NULL AUTO_INCREMENT,
                title VARCHAR(255) NOT NULL,
                file_path VARCHAR(500) NOT NULL,
                file_type ENUM('image', 'pdf') NOT NULL DEFAULT 'image',
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $tableEnsured = true;
    }
}

if (!function_exists('congratulationsCertificateDetermineType')) {
    function congratulationsCertificateDetermineType(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return $extension === 'pdf' ? 'pdf' : 'image';
    }
}

if (!function_exists('congratulationsCertificateIsPdf')) {
    function congratulationsCertificateIsPdf(array $certificate): bool
    {
        if (($certificate['file_type'] ?? '') === 'pdf') {
            return true;
        }

        return congratulationsCertificateDetermineType((string) ($certificate['file_path'] ?? '')) === 'pdf';
    }
}

if (!function_exists('congratulationsCertificateGetUrl')) {
    function congratulationsCertificateGetUrl(array $certificate): string
    {
        return SITE_URL . '/' . ltrim((string) ($certificate['file_path'] ?? ''), '/\\');
    }
}

if (!function_exists('congratulationsCertificateGetDiskPath')) {
    function congratulationsCertificateGetDiskPath(array $certificate): string
    {
        $relativePath = ltrim((string) ($certificate['file_path'] ?? ''), '/\\');
        $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativePath);
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . $normalizedPath;
    }
}

if (!function_exists('congratulationsCertificateContentType')) {
    function congratulationsCertificateContentType(array $certificate): string
    {
        $extension = strtolower(pathinfo((string) ($certificate['file_path'] ?? ''), PATHINFO_EXTENSION));

        switch ($extension) {
            case 'pdf':
                return 'application/pdf';
            case 'png':
                return 'image/png';
            case 'webp':
                return 'image/webp';
            case 'jpg':
            case 'jpeg':
            default:
                return 'image/jpeg';
        }
    }
}

if (!function_exists('congratulationsCertificateDownloadName')) {
    function congratulationsCertificateDownloadName(array $certificate): string
    {
        $title = trim((string) ($certificate['title'] ?? 'congratulations-certificate'));
        $slug = preg_replace('/[^A-Za-z0-9]+/', '-', $title);
        $slug = trim((string) $slug, '-');

        if ($slug === '') {
            $slug = 'congratulations-certificate';
        }

        $extension = strtolower(pathinfo((string) ($certificate['file_path'] ?? ''), PATHINFO_EXTENSION));
        if ($extension === '') {
            $extension = congratulationsCertificateIsPdf($certificate) ? 'pdf' : 'jpg';
        }

        return $slug . '.' . $extension;
    }
}

if (!function_exists('fetchCongratulationsCertificates')) {
    function fetchCongratulationsCertificates(PDO $db, ?int $limit = null): array
    {
        ensureCongratulationsCertificatesTable($db);

        $sql = "SELECT * FROM congratulations_certificates ORDER BY created_at DESC";
        if ($limit !== null) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $db->prepare($sql);
        if ($limit !== null) {
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
