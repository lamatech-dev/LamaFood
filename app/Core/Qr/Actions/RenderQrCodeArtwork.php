<?php

namespace App\Core\Qr\Actions;

use App\Core\Qr\QrCodeArtwork;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use InvalidArgumentException;

final class RenderQrCodeArtwork
{
    public function execute(string $targetUrl, string $format): QrCodeArtwork
    {
        return match ($format) {
            'svg' => $this->renderSvg($targetUrl),
            'png' => $this->renderPng($targetUrl),
            'pdf' => $this->renderPdf($targetUrl),
            default => throw new InvalidArgumentException('Unsupported QR artwork format.'),
        };
    }

    private function renderSvg(string $targetUrl): QrCodeArtwork
    {
        $result = (new Builder(
            writer: new SvgWriter,
            data: $targetUrl,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 1024,
            margin: 48,
        ))->build();

        return new QrCodeArtwork($result->getString(), $result->getMimeType(), 'svg');
    }

    private function renderPng(string $targetUrl): QrCodeArtwork
    {
        $result = (new Builder(
            writer: new PngWriter,
            data: $targetUrl,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 1600,
            margin: 80,
        ))->build();

        return new QrCodeArtwork($result->getString(), $result->getMimeType(), 'png');
    }

    private function renderPdf(string $targetUrl): QrCodeArtwork
    {
        $svg = $this->renderSvg($targetUrl)->contents;
        $safeUrl = htmlspecialchars($targetUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $svgDataUri = 'data:image/svg+xml;base64,'.base64_encode($svg);
        $html = <<<HTML
            <!doctype html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <style>
                    @page { margin: 18mm; }
                    body { color: #111; font-family: "DejaVu Sans", sans-serif; text-align: center; }
                    img { display: block; height: 150mm; margin: 10mm auto 8mm; width: 150mm; }
                    p { font-size: 11pt; overflow-wrap: anywhere; }
                </style>
            </head>
            <body>
                <img src="{$svgDataUri}" alt="QR code">
                <p>{$safeUrl}</p>
            </body>
            </html>
            HTML;
        $options = new Options;
        $options->set('isRemoteEnabled', false);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new QrCodeArtwork($dompdf->output(), 'application/pdf', 'pdf');
    }
}
