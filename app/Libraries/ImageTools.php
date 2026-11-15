<?php

namespace App\Libraries;

class ImageTools
{
    /**
     * Recorta los márgenes transparentes de un PNG (los logos suelen venir con
     * mucho padding y terminan viéndose chicos en la UI).
     */
    public function trimTransparentPng(string $path): bool
    {
        if (! function_exists('imagecreatefrompng') || ! is_file($path)) {
            return false;
        }

        $info = @getimagesize($path);
        if (! $info || $info[2] !== IMAGETYPE_PNG) {
            return false;
        }

        $image = @imagecreatefrompng($path);
        if (! $image) {
            return false;
        }

        if (function_exists('imagepalettetotruecolor')) {
            imagepalettetotruecolor($image);
        }

        $width  = imagesx($image);
        $height = imagesy($image);
        $minX   = $width;
        $minY   = $height;
        $maxX   = 0;
        $maxY   = 0;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgba = imagecolorsforindex($image, imagecolorat($image, $x, $y));
                if (($rgba['alpha'] ?? 0) < 110) {
                    $minX = min($minX, $x);
                    $minY = min($minY, $y);
                    $maxX = max($maxX, $x);
                    $maxY = max($maxY, $y);
                }
            }
        }

        if ($maxX < $minX || $maxY < $minY) {
            imagedestroy($image);

            return false;
        }

        // Un pequeño respiro para no cortar los bordes.
        $padding = 4;
        $minX    = max(0, $minX - $padding);
        $minY    = max(0, $minY - $padding);
        $maxX    = min($width - 1, $maxX + $padding);
        $maxY    = min($height - 1, $maxY + $padding);

        $cropWidth  = $maxX - $minX + 1;
        $cropHeight = $maxY - $minY + 1;

        if ($cropWidth === $width && $cropHeight === $height) {
            imagedestroy($image);

            return false;
        }

        $cropped = imagecrop($image, [
            'x'      => $minX,
            'y'      => $minY,
            'width'  => $cropWidth,
            'height' => $cropHeight,
        ]);

        if (! $cropped) {
            imagedestroy($image);

            return false;
        }

        imagealphablending($cropped, false);
        imagesavealpha($cropped, true);
        $ok = imagepng($cropped, $path);
        imagedestroy($cropped);
        imagedestroy($image);

        return (bool) $ok;
    }
}
