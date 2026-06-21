<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EReceipt;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EReceiptService
{
    public function getAll(int $perPage = 10): LengthAwarePaginator
    {
        return EReceipt::with(['pet', 'doctor', 'items'])->latest()->paginate($perPage);
    }

    public function getById(int $id): EReceipt
    {
        return EReceipt::with(['pet', 'doctor', 'items'])->findOrFail($id);
    }

    public function create(array $data): EReceipt
    {
        return DB::transaction(static function () use ($data): EReceipt {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $receipt = EReceipt::create($data);

            foreach ($items as $item) {
                $receipt->items()->create($item);
            }

            return $receipt->load(['pet', 'doctor', 'items']);
        });
    }

    public function update(int $id, array $data): EReceipt
    {
        $receipt = $this->getById($id);

        return DB::transaction(function () use ($id, $data): EReceipt {
            $receipt = $this->getById($id);
            $items = $data['items'] ?? null;
            unset($data['items']);

            $receipt->update($data);

            if ($items !== null) {
                $receipt->items()->delete();

                foreach ($items as $item) {
                    $receipt->items()->create($item);
                }
            }

            return $receipt->load(['pet', 'doctor', 'items']);
        });
    }

    public function delete(int $id): bool
    {
        $receipt = $this->getById($id);

        return $receipt->delete();
    }
}
