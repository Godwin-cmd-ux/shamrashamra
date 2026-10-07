<?php

namespace App\Services;

use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\QRCode;

class QrService
{
    /** Render a PNG QR code for arbitrary data (e.g. an invitation URL). */
    public function png(string $data, int $scale = 10): string
    {
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'outputBase64' => false,
            'scale' => $scale,
            'addQuietzone' => true,
        ]);

        $png = (new QRCode($options))->render($data);

        return is_string($png) ? $png : (string) $png;
    }
}
