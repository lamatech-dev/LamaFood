<?php

namespace App\Http\Controllers\Api\Admin\V1\Qr;

use App\Core\Qr\Actions\RenderQrCodeArtwork;
use App\Core\Qr\Models\QrCode;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QrCodeArtworkController extends Controller
{
    public function __invoke(Request $request, string $qrCode, string $format, RenderQrCodeArtwork $renderer): Response
    {
        /** @var User $user */
        $user = $request->user();
        $query = QrCode::query();
        $model = ($user->isGodfather() ? $query : $query->where('business_id', $user->business_id))
            ->where('public_id', $qrCode)
            ->firstOrFail();
        $targetUrl = route('public.qr.redirect', ['publicId' => $model->public_id]);
        $artwork = $renderer->execute($targetUrl, $format);
        $filename = sprintf('qr-%s-%s.%s', $model->type->value, $model->public_id, $artwork->extension);

        return response($artwork->contents, Response::HTTP_OK, [
            'Content-Type' => $artwork->mimeType,
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
