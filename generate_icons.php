<?php
/**
 * Generate ikon PWA dari logo Universitas Pattimura
 * Jalankan: php generate_icons.php
 */

$sourcePath = __DIR__ . '/Universitas-Pattimura-Ambon-Logo.png';
$outputDir = __DIR__ . '/public/icons';

if (!file_exists($sourcePath)) {
    die("ERROR: File logo tidak ditemukan: $sourcePath\n");
}

if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

// Load source image
$sourceImage = imagecreatefrompng($sourcePath);
if (!$sourceImage) {
    die("ERROR: Gagal membaca file PNG.\n");
}

$srcW = imagesx($sourceImage);
$srcH = imagesy($sourceImage);

echo "Source: {$srcW}x{$srcH}px\n\n";

$sizes = [72, 96, 128, 144, 152, 192, 384, 512];

foreach ($sizes as $size) {
    $dest = imagecreatetruecolor($size, $size);

    // Preserve transparency
    imagealphablending($dest, false);
    imagesavealpha($dest, true);
    $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
    imagefilledrectangle($dest, 0, 0, $size, $size, $transparent);

    // Fill with white background (for PWA icon compatibility)
    imagealphablending($dest, true);
    $white = imagecolorallocate($dest, 255, 255, 255);
    imagefilledrectangle($dest, 0, 0, $size, $size, $white);

    // Calculate aspect-fit with padding
    $padding = intval($size * 0.08); // 8% padding
    $availableSize = $size - ($padding * 2);

    $ratio = min($availableSize / $srcW, $availableSize / $srcH);
    $newW = intval($srcW * $ratio);
    $newH = intval($srcH * $ratio);
    $dstX = intval(($size - $newW) / 2);
    $dstY = intval(($size - $newH) / 2);

    // Resample with high quality
    imagecopyresampled($dest, $sourceImage, $dstX, $dstY, 0, 0, $newW, $newH, $srcW, $srcH);

    $filename = $outputDir . "/icon-{$size}x{$size}.png";
    imagepng($dest, $filename, 9); // Compression level 9
    imagedestroy($dest);

    $fileSize = filesize($filename);
    echo "✓ icon-{$size}x{$size}.png ({$fileSize} bytes)\n";
}

imagedestroy($sourceImage);

echo "\nSelesai! Semua ikon PWA telah di-generate dari logo Universitas Pattimura.\n";
