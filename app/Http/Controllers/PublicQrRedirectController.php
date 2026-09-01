<?php

namespace App\Http\Controllers;

use App\Core\Analytics\Actions\RecordAnalyticsEvent;
use App\Core\Analytics\AnalyticsEventType;
use App\Core\Analytics\VisitorIdentity;
use App\Core\Localization\LocaleRegistry;
use App\Core\Qr\Models\QrCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PublicQrRedirectController extends Controller
{
    public function __invoke(Request $request, string $publicId, LocaleRegistry $locales, VisitorIdentity $visitors, RecordAnalyticsEvent $record): RedirectResponse
    {
        $qrCode = QrCode::query()
            ->with(['business', 'branch'])
            ->where('public_id', $publicId)
            ->where('is_active', true)
            ->whereHas('business', fn ($query) => $query->where('is_active', true))
            ->whereHas('branch', fn ($query) => $query->where('is_active', true))
            ->firstOrFail();
        $supported = $locales->codes();
        $locale = (string) $request->cookie('denardi_locale', '');
        if (! in_array($locale, $supported, true)) {
            $locale = $request->getPreferredLanguage($supported) ?: $locales->default();
        }

        $identifier = $visitors->identifier($request);
        if (! $visitors->isBot($request)) {
            $record->execute($qrCode->business, AnalyticsEventType::Scan, $visitors->hash($identifier), $visitors->deviceClass($request), $locale, $qrCode);
        }

        $query = ['branch' => $qrCode->branch->slug];
        if ($qrCode->type->value === 'table') {
            $query['table'] = $qrCode->table_key;
        }

        return redirect()->route('public.menu', ['locale' => $locale, ...$query])
            ->withCookie($visitors->cookie($identifier));
    }
}
