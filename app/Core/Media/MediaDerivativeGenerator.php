<?php

namespace App\Core\Media;

use GdImage;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class MediaDerivativeGenerator
{
    /** @return array{optimized_path: string, thumbnail_path: string} */
    public function execute(string $disk, string $originalPath): array
    {
        $bytes = Storage::disk($disk)->get($originalPath);
        $source = @imagecreatefromstring($bytes);
        if (! $source instanceof GdImage) {
            throw new RuntimeException('The uploaded image could not be decoded safely.');
        }

        try {
            $directory = trim(pathinfo($originalPath, PATHINFO_DIRNAME), '.');
            $basename = pathinfo($originalPath, PATHINFO_FILENAME);
            $prefix = $directory === '' ? $basename : $directory.'/'.$basename;
            $optimizedPath = $prefix.'.optimized.webp';
            $thumbnailPath = $prefix.'.thumbnail.webp';

            Storage::disk($disk)->put($optimizedPath, $this->webp($source, 1600, 82));
            Storage::disk($disk)->put($thumbnailPath, $this->webp($source, 480, 78));

            return ['optimized_path' => $optimizedPath, 'thumbnail_path' => $thumbnailPath];
        } finally {
            imagedestroy($source);
        }
    }

    private function webp(GdImage $source, int $maximumWidth, int $quality): string
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $targetWidth = min($width, $maximumWidth);
        $targetHeight = max(1, (int) round($height * ($targetWidth / $width)));
        $scaled = imagecreatetruecolor($targetWidth, $targetHeight);
        if (! $scaled instanceof GdImage) {
            throw new RuntimeException('The uploaded image could not be resized.');
        }

        imagealphablending($scaled, false);
        $transparent = imagecolorallocatealpha($scaled, 0, 0, 0, 127);
        imagefill($scaled, 0, 0, $transparent);
        imagesavealpha($scaled, true);
        if (! imagecopyresampled($scaled, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height)) {
            imagedestroy($scaled);

            throw new RuntimeException('The uploaded image could not be resized.');
        }
        imagealphablending($scaled, true);
        ob_start();
        $encoded = imagewebp($scaled, null, $quality);
        $contents = ob_get_clean();
        imagedestroy($scaled);

        if (! $encoded || ! is_string($contents)) {
            throw new RuntimeException('The uploaded image could not be converted to WebP.');
        }

        return $contents;
    }
}
