<?php

namespace App\Http\Controllers;

use App\Core\Analytics\Actions\RecordAnalyticsEvent;
use App\Core\Analytics\AnalyticsEventType;
use App\Core\Analytics\VisitorIdentity;
use App\Core\Business\Models\Branch;
use App\Core\Localization\TranslationState;
use App\Core\Menu\Models\MenuCategory;
use App\Core\Menu\Models\Product;
use App\Core\Menu\PublicationState;
use App\Http\Requests\PublicAnalyticsViewRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;

class PublicAnalyticsViewController extends Controller
{
    public function __invoke(PublicAnalyticsViewRequest $request, VisitorIdentity $visitors, RecordAnalyticsEvent $record): Response
    {
        $branch = Branch::query()
            ->with('business')
            ->where('slug', $request->string('branch')->toString())
            ->where('is_active', true)
            ->whereHas('business', fn (Builder $query): Builder => $query
                ->where('slug', config('denardi.business_slug'))
                ->where('is_active', true))
            ->firstOrFail();
        $type = AnalyticsEventType::from($request->string('type')->toString());
        $locale = $request->string('locale')->toString();
        $publicId = $request->string('subject_public_id')->toString();
        $subjectType = $type === AnalyticsEventType::CategoryView ? 'category' : 'product';

        $this->ensureSubjectIsPublic($branch, $type, $publicId, $locale);

        $identifier = $visitors->identifier($request);
        if (! $visitors->isBot($request)) {
            $record->execute(
                $branch->business,
                $type,
                $visitors->hash($identifier),
                $visitors->deviceClass($request),
                $locale,
                subjectType: $subjectType,
                subjectPublicId: $publicId,
                branch: $branch,
            );
        }

        return response()->noContent()->withCookie($visitors->cookie($identifier));
    }

    private function ensureSubjectIsPublic(Branch $branch, AnalyticsEventType $type, string $publicId, string $locale): void
    {
        $translation = fn (Builder $query): Builder => $query
            ->where('locale', $locale)
            ->where('translation_state', TranslationState::Ready)
            ->whereNotNull('name');

        if ($type === AnalyticsEventType::CategoryView) {
            MenuCategory::query()
                ->where('business_id', $branch->business_id)
                ->where('public_id', $publicId)
                ->where('publication_state', PublicationState::Published)
                ->whereHas('translations', $translation)
                ->firstOrFail();

            return;
        }

        Product::query()
            ->where('business_id', $branch->business_id)
            ->where('public_id', $publicId)
            ->where('publication_state', PublicationState::Published)
            ->whereHas('translations', $translation)
            ->whereHas('branchSettings', fn (Builder $query): Builder => $query->where('branch_id', $branch->id))
            ->firstOrFail();
    }
}
