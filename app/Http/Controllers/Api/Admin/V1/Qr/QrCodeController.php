<?php

namespace App\Http\Controllers\Api\Admin\V1\Qr;

use App\Core\Audit\AuditRecorder;
use App\Core\Business\Models\Branch;
use App\Core\Qr\Actions\CreateQrCode;
use App\Core\Qr\Models\QrCode;
use App\Core\Qr\QrCodeType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\V1\Qr\StoreQrCodeRequest;
use App\Http\Requests\Api\Admin\V1\Qr\UpdateQrCodeRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QrCodeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $qrCodes = $this->query($request)
            ->with('branch:id,name,slug')
            ->withCount('events')
            ->latest('id')
            ->get();

        return response()->json(['data' => $qrCodes]);
    }

    public function store(StoreQrCodeRequest $request, CreateQrCode $create): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        abort_if($user->business_id === null, 422, 'A business context is required.');
        $branch = Branch::query()
            ->where('business_id', $user->business_id)
            ->whereKey($request->integer('branch_id'))
            ->firstOrFail();
        $qrCode = $create->execute(
            $branch,
            $user,
            QrCodeType::from($request->string('type')->toString()),
            $request->string('label')->toString(),
            $request->filled('table_key') ? $request->string('table_key')->toString() : null,
        );

        return response()->json(['data' => $qrCode], 201);
    }

    public function update(UpdateQrCodeRequest $request, string $qrCode, AuditRecorder $audit): JsonResponse
    {
        $model = $this->query($request)->where('public_id', $qrCode)->firstOrFail();
        $before = $model->toArray();
        $model->update($request->safe()->only(['label', 'is_active']));
        /** @var User $user */
        $user = $request->user();
        $audit->record('qr.updated', $user, $model, $model->business_id, $model->branch_id, before: $before, after: $model->fresh()->toArray());

        return response()->json(['data' => $model->fresh('branch')]);
    }

    /** @return Builder<QrCode> */
    private function query(Request $request): Builder
    {
        /** @var User $user */
        $user = $request->user();
        $query = QrCode::query();

        return $user->isGodfather() ? $query : $query->where('business_id', $user->business_id);
    }
}
