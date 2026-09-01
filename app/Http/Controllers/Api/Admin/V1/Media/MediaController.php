<?php

namespace App\Http\Controllers\Api\Admin\V1\Media;

use App\Core\Audit\AuditRecorder;
use App\Core\Business\BusinessContextResolver;
use App\Core\Localization\LocaleRegistry;
use App\Core\Media\MediaDerivativeGenerator;
use App\Core\Media\MediaStatus;
use App\Core\Media\Models\Media;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\V1\Media\StoreMediaRequest;
use App\Http\Requests\Api\Admin\V1\Media\UpdateMediaRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class MediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $media = $this->query($request)
            ->with([
                'translations',
                'usages:id,media_id,subject_type,subject_id,field',
                'products:id,public_id,primary_media_id,slug',
            ])
            ->withCount(['usages', 'products'])
            ->latest('id')
            ->paginate(30);

        return response()->json(['data' => $media]);
    }

    public function store(StoreMediaRequest $request, LocaleRegistry $locales, AuditRecorder $audit, BusinessContextResolver $contexts, MediaDerivativeGenerator $derivatives): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $business = $contexts->forUser($user);
        $file = $request->file('file');
        abort_if($file === null, 422, 'A file is required.');
        $dimensions = @getimagesize($file->getRealPath());
        $checksum = hash_file('sha256', $file->getRealPath());
        $path = $file->store("businesses/{$business->id}/media", 'public');
        abort_if($path === false, 500, 'Media storage failed.');
        try {
            $generated = $derivatives->execute('public', $path);
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);

            throw $exception;
        }

        try {
            $media = DB::transaction(function () use ($request, $locales, $audit, $user, $business, $file, $dimensions, $checksum, $path, $generated): Media {
                $media = Media::query()->create([
                    'public_id' => (string) Str::ulid(),
                    'business_id' => $business->id,
                    'disk' => 'public',
                    'path' => $path,
                    'optimized_path' => $generated['optimized_path'],
                    'thumbnail_path' => $generated['thumbnail_path'],
                    'mime' => $file->getMimeType() ?? 'application/octet-stream',
                    'size' => $file->getSize(),
                    'width' => is_array($dimensions) ? $dimensions[0] : null,
                    'height' => is_array($dimensions) ? $dimensions[1] : null,
                    'checksum' => $checksum,
                    'status' => MediaStatus::Ready,
                    'uploaded_by' => $user->id,
                ]);

                foreach ($locales->codes() as $locale) {
                    $translation = $request->array("translations.{$locale}");
                    $media->translations()->create(['locale' => $locale, ...$translation]);
                }
                $audit->record('media.uploaded', $user, $media, $business->id, after: $media->toArray());

                return $media->load('translations');
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete([$path, ...array_values($generated)]);

            throw $exception;
        }

        return response()->json(['data' => $media], 201);
    }

    public function update(UpdateMediaRequest $request, string $media, LocaleRegistry $locales, AuditRecorder $audit): JsonResponse
    {
        $model = $this->find($request, $media);
        $before = $model->toArray();
        DB::transaction(function () use ($request, $locales, $audit, $model, $before): void {
            if ($request->has('status')) {
                $model->update(['status' => $request->string('status')->toString()]);
            }
            foreach ($locales->codes() as $locale) {
                if ($request->has("translations.{$locale}")) {
                    $model->translations()->updateOrCreate(['locale' => $locale], $request->array("translations.{$locale}"));
                }
            }
            /** @var User $user */
            $user = $request->user();
            $audit->record('media.updated', $user, $model, $model->business_id, before: $before, after: $model->fresh()->toArray());
        });

        return response()->json(['data' => $model->fresh('translations')]);
    }

    public function destroy(Request $request, string $media, AuditRecorder $audit): JsonResponse
    {
        $model = $this->find($request, $media);
        if ($model->usages()->exists() || $model->products()->exists()) {
            throw new ConflictHttpException('Media with active usages cannot be deleted; archive it instead.');
        }
        /** @var User $user */
        $user = $request->user();
        $audit->record('media.deleted', $user, $model, $model->business_id, before: $model->toArray());
        Storage::disk($model->disk)->delete(array_filter([$model->path, $model->optimized_path, $model->thumbnail_path]));
        $model->delete();

        return response()->json(status: 204);
    }

    /** @return Builder<Media> */
    private function query(Request $request): Builder
    {
        /** @var User $user */
        $user = $request->user();
        $query = Media::query();

        return $user->isGodfather() ? $query : $query->where('business_id', $user->business_id);
    }

    private function find(Request $request, string $publicId): Media
    {
        return $this->query($request)->where('public_id', $publicId)->firstOrFail();
    }
}
