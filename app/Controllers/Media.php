<?php

namespace App\Controllers;

class Media extends BaseController
{
    public function favicon(int $size = 32)
    {
        $size = in_array($size, [16, 32, 180], true) ? $size : 32;
        $file = setting('logo_path');

        if ($file === '') {
            return redirect()->to(base_url('favicon.svg'));
        }

        $path = FCPATH . 'uploads/' . $file;
        if (! is_file($path)) {
            return redirect()->to(base_url('favicon.svg'));
        }

        $source = @imagecreatefromstring((string) file_get_contents($path));
        if (! $source) {
            return redirect()->to(base_url('favicon.svg'));
        }

        $srcW = imagesx($source);
        $srcH = imagesy($source);

        $canvas = imagecreatetruecolor($size, $size);
        $white  = imagecolorallocate($canvas, 255, 255, 255);
        $border = imagecolorallocate($canvas, 226, 229, 234);

        imagefilledrectangle($canvas, 0, 0, $size - 1, $size - 1, $white);
        imagerectangle($canvas, 0, 0, $size - 1, $size - 1, $border);

        $padding = max(2, (int) round($size * 0.08));
        $box     = $size - ($padding * 2);
        $ratio   = min($box / $srcW, $box / $srcH);
        $dstW    = max(1, (int) round($srcW * $ratio));
        $dstH    = max(1, (int) round($srcH * $ratio));
        $dstX    = (int) round(($size - $dstW) / 2);
        $dstY    = (int) round(($size - $dstH) / 2);

        imagecopyresampled($canvas, $source, $dstX, $dstY, 0, 0, $dstW, $dstH, $srcW, $srcH);

        ob_start();
        imagepng($canvas);
        $body = (string) ob_get_clean();

        imagedestroy($canvas);
        imagedestroy($source);

        return $this->response
            ->setHeader('Content-Type', 'image/png')
            ->setHeader('Cache-Control', 'public, max-age=86400')
            ->setBody($body);
    }
}
