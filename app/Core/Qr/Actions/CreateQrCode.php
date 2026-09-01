<?php

namespace App\Core\Qr\Actions;

use App\Core\Audit\AuditRecorder;
use App\Core\Business\Models\Branch;
use App\Core\Qr\Models\QrCode;
use App\Core\Qr\QrCodeType;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateQrCode
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function execute(Branch $branch, User $actor, QrCodeType $type, string $label, ?string $tableKey): QrCode
    {
        if ($type === QrCodeType::Table && blank($tableKey)) {
            throw ValidationException::withMessages(['table_key' => ['A table QR requires a table key.']]);
        }

        if ($type === QrCodeType::Menu && filled($tableKey)) {
            throw ValidationException::withMessages(['table_key' => ['A general menu QR cannot contain a table key.']]);
        }

        $qrCode = QrCode::query()->create([
            'public_id' => (string) Str::ulid(),
            'business_id' => $branch->business_id,
            'branch_id' => $branch->id,
            'type' => $type,
            'label' => $label,
            'table_key' => $tableKey,
            'is_active' => true,
            'created_by' => $actor->id,
        ]);
        $this->audit->record('qr.created', $actor, $qrCode, $branch->business_id, $branch->id, after: $qrCode->toArray());

        return $qrCode->load('branch');
    }
}
