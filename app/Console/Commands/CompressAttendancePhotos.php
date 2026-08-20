<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CompressAttendancePhotos extends Command
{
    protected $signature = 'attendance:compress-photos';

    protected $description = 'Compress old attendance photos larger than 200 KB';

    public function handle()
    {
        $directory = storage_path('app/public/attendance_photos');

        if (!File::exists($directory)) {
            $this->error('Folder attendance_photos tidak ditemukan.');

            return Command::FAILURE;
        }

        $files = File::files($directory);

        $total = count($files);

        $processed = 0;
        $skipped = 0;
        $failed = 0;

        $totalBefore = 0;
        $totalAfter = 0;

        $this->info("Found {$total} files.");
        $this->newLine();

        foreach ($files as $index => $file) {

            $path = $file->getPathname();

            $extension = strtolower($file->getExtension());

            /*
             * Hanya proses PNG, JPG, dan JPEG.
             */
            if (!in_array($extension, ['png','jpg','jpeg'])) {
                $skipped++;
                continue;
            }

            $before = filesize($path);

            /*
             * Hanya proses file yang lebih besar
             * dari 200 KB.
             */
            if ($before <= 200 * 1024) {
                $skipped++;
                continue;
            }

            $totalBefore += $before;

            $this->line(sprintf(
                '[%d/%d] %s (%s)',
                $index + 1,
                $total,
                $file->getFilename(),
                $this->formatBytes($before)
            ));

            try {
                if ($extension === 'png') {
                    $result = $this->compressPng($path);
                } else {
                    $result = $this->compressJpeg($path);
                }

                if (!$result) {
                    $failed++;
                    $this->error('  Failed');
                    continue;
                }

                $after = filesize($path);
                $totalAfter += $after;
                $processed++;
                $this->info(sprintf(
                    '  %s -> %s',
                    $this->formatBytes($before),
                    $this->formatBytes($after)
                ));

            } catch (\Throwable $e) {
                $failed++;
                $this->error('  Error: ' . $e->getMessage());

                Log::error(
                    'Failed to compress attendance photo',
                    [
                        'path' => $path,
                        'error' => $e->getMessage(),
                    ]
                );
            }
        }

        $this->newLine();

        $this->info('====================================');
        $this->info('Compression completed');
        $this->info('====================================');

        $this->line("Processed : {$processed}");
        $this->line("Skipped   : {$skipped}");
        $this->line("Failed    : {$failed}");

        if ($totalBefore > 0) {

            $this->line(
                'Before    : ' .
                $this->formatBytes($totalBefore)
            );

            $this->line(
                'After     : ' .
                $this->formatBytes($totalAfter)
            );

            $saved = $totalBefore - $totalAfter;

            $this->line(
                'Saved     : ' .
                $this->formatBytes($saved)
            );
        }

        return Command::SUCCESS;
    }

    /**
     * Compress JPG / JPEG.
     *
     * Target sekitar 50 KB.
     */
    private function compressJpeg(string $path): bool
    {
        $targetSize = 50 * 1024; // 50 KB
        $maxSize = 60 * 1024;    // 60 KB

        $image = @imagecreatefromjpeg($path);

        if (!$image) {
            return false;
        }

        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        /*
         * Maksimum dimensi foto.
         *
         * Tidak perlu mempertahankan resolusi
         * kamera HP yang sangat besar.
         */
        $maxDimension = 1280;

        $scale = min(
            $maxDimension / $originalWidth,
            $maxDimension / $originalHeight,
            1
        );

        $newWidth = max(
            1,
            (int) round($originalWidth * $scale)
        );

        $newHeight = max(
            1,
            (int) round($originalHeight * $scale)
        );

        $newImage = $this->resizeImage(
            $image,
            $newWidth,
            $newHeight
        );

        imagedestroy($image);

        $tempPath = $path . '.tmp';

        /*
         * Mulai dengan quality tinggi.
         */
        $quality = 85;

        imagejpeg(
            $newImage,
            $tempPath,
            $quality
        );

        /*
         * Turunkan quality perlahan sampai
         * ukuran mendekati 50 KB.
         */
        while (
            filesize($tempPath) > $targetSize &&
            $quality > 30
        ) {

            $quality -= 5;

            imagejpeg(
                $newImage,
                $tempPath,
                $quality
            );
        }

        /*
         * Kalau quality sudah rendah tetapi
         * masih >60 KB, baru resize.
         *
         * Resize hanya 10% setiap iterasi.
         */
        while (filesize($tempPath) > $maxSize && $newWidth > 640) {
            $newWidth = max(640,(int) round($newWidth * 0.90));
            $newHeight = max(640,(int) round($newHeight * 0.90));
            $smallerImage = $this->resizeImage($newImage,$newWidth,$newHeight);
            imagedestroy($newImage);
            $newImage = $smallerImage;
            /*
             * Setelah resize, naikkan kembali quality.
             */
            $quality = 75;
            imagejpeg($newImage,$tempPath,$quality);
            while (filesize($tempPath) > $targetSize && $quality > 30) {
                $quality -= 5;
                imagejpeg($newImage,$tempPath,$quality);
            }
        }

        imagedestroy($newImage);

        if (!file_exists($tempPath)) {
            return false;
        }

        /*
         * Jangan replace file kalau hasilnya
         * lebih besar dari file original.
         */
        if (filesize($tempPath) >= filesize($path)) {

@unlink($tempPath);

            return false;
        }

        /*
         * Replace file lama.
         *
         * Nama dan extension tetap sama.
         */
        if (!rename($tempPath, $path)) {

@unlink($tempPath);

            return false;
        }

        return true;
    }

    /**
     * Compress PNG.
     *
     * Target sekitar 50 KB.
     *
     * Extension tetap .png.
     */
    private function compressPng(string $path): bool
    {
        $targetSize = 50 * 1024; // 50 KB
        $maxSize = 60 * 1024;    // 60 KB

        $image = @imagecreatefrompng($path);

        if (!$image) {
            return false;
        }

        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        /*
         * Maksimum dimensi.
         */
        $maxDimension = 1280;
        $scale = min($maxDimension / $originalWidth,$maxDimension / $originalHeight,1);
        $newWidth = max(1,(int) round($originalWidth * $scale));
        $newHeight = max(1,(int) round($originalHeight * $scale));
        $newImage = $this->resizeImage($image,$newWidth,$newHeight);
        imagedestroy($image);
        $tempPath = $path . '.tmp';

        /*
         * PNG compression level 9.
         */
        imagepng($newImage,$tempPath,9);

        /*
         * Kalau masih >60 KB,
         * resize 10% setiap iterasi.
         */
        while (filesize($tempPath) > $maxSize && $newWidth > 320) {
            $newWidth = max(320,(int) round($newWidth * 0.90));
            $newHeight = max(320,(int) round($newHeight * 0.90));
            $smallerImage = $this->resizeImage($newImage,$newWidth,$newHeight);
            imagedestroy($newImage);
            $newImage = $smallerImage;
            imagepng($newImage,$tempPath,9);
        }

        imagedestroy($newImage);

        if (!file_exists($tempPath)) {
            return false;
        }

        /*
         * Jangan replace kalau hasil lebih besar
         * dari original.
         */
        if (filesize($tempPath) >= filesize($path)) {
@unlink($tempPath);
            return false;
        }

        /*
         * Replace file lama.
         *
         * Path tetap .png.
         */
        if (!rename($tempPath, $path)) {
@unlink($tempPath);
            return false;
        }

        return true;
    }

    /**
     * Resize image.
     *
     * Transparency PNG tetap dipertahankan.
     */
    private function resizeImage($source,int $width,int $height) {
        $target = imagecreatetruecolor($width,$height);
        /*
         * Preserve PNG transparency.
         */
        imagealphablending($target,false);
        imagesavealpha($target,true);
        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            0,
            0,
            $width,
            $height,
            imagesx($source),
            imagesy($source)
        );

        return $target;
    }

    /**
     * Format bytes menjadi KB / MB.
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return number_format($bytes / 1024 / 1024,2) . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024,2) . ' KB';
        }

        return $bytes . ' B';
    }
}
