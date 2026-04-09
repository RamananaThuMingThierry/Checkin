<?php

namespace App\Services;

use App\Interfaces\ModuleInterface;
use App\Models\Module;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class ModuleService
{
    public function __construct(private readonly ModuleInterface $moduleRepository)
    {
    }

    public function listModules()
    {
        return $this->moduleRepository->getAll(
            keys: null,
            value: null,
            orderBy: ['id' => 'asc'],
        );
    }

    public function createModule(array $data): Module
    {
        $payload = Arr::only($data, [
            'name',
            'code',
            'description',
            'version',
            'is_active',
        ]);

        $existingModule = $this->moduleRepository->getByKeys('code', $payload['code']);

        if ($existingModule !== null) {
            throw ValidationException::withMessages([
                'code' => 'A module with this code already exists.',
            ]);
        }

        $payload['is_active'] ??= true;

        return $this->moduleRepository->create($payload);
    }
}
