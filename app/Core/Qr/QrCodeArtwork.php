<?php

namespace App\Core\Qr;

final readonly class QrCodeArtwork
{
    public function __construct(
        public string $contents,
        public string $mimeType,
        public string $extension,
    ) {}
}
