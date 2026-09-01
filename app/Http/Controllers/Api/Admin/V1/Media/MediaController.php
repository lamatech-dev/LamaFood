<?php

namespace App\Http\Controllers\Api\Admin\V1\Media;

use App\Core\Audit\AuditRecorder;
use App\Core\Localization\LocaleRegistry;
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
        return response()->json(['data' => $this->query($request)->with('translations')->latest()->paginate(30)]);
    }

    public function store(StoreMediaRequest $request, LocaleRegistry $locales, AuditRecorder $audit): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_if($user->business_id === null, 422, 'A business context is required to upload media.');
        $file = $request->file('file');
        abort_if($file === null, 422, 'A file is required.');
        $dimensions = @getimagesize($file->getRealPath());
        $checksum = hash_file('sha256', $file->getRealPath());
        $path = $file->store("businesses/{$user->business_id}/media", 'public');
        abort_if($path === false, 500, 'Media storage failed.');

        $media = DB::transaction(function () use ($request, $locales, $audit, $user, $file, $dimensions, $checksum, $path): Media {
            $media = Media::query()->create([
                'public_id' => (string) Str::ulid(),
                'business_id' => $user->business_id,
                'disk' => 'public',
                'path' => $path,
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
            $audit->record('media.uploaded', $user, $media, $user->business_id, after: $media->toArray());

            return $media->load('translations');
        });

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
        if ($model->usages()->exists()) {
            throw new ConflictHttpException('Media with active usages cannot be deleted; archive it instead.');
        }
        /** @var User $user */
        $user = $request->user();
        $audit->record('media.deleted', $user, $model, $model->business_id, before: $model->toArray());
        Storage::disk($model->disk)->delete($model->path);
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
