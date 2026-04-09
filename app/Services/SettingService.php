<?php

namespace App\Services;

use App\Interfaces\SettingInterface;
use App\Interfaces\TenantInterface;
use Illuminate\Validation\ValidationException;

class SettingService
{
    public function __construct(
        private readonly SettingInterface $settingRepository,
        private readonly TenantInterface $tenantRepository,
    ) {
    }

    public function getTenantSettings(string $tenantEncryptedId): array
    {
        $tenantId = decrypt_to_int_or_null($tenantEncryptedId);

        if ($tenantId === null || $this->tenantRepository->getById($tenantId) === null) {
            throw ValidationException::withMessages([
                'tenant_id' => 'The selected tenant is invalid.',
            ]);
        }

        $settings = $this->settingRepository->getAll(
            keys: 'tenant_id',
            value: $tenantId,
            orderBy: ['key' => 'asc'],
        );

        $mapped = [];
        foreach ($settings as $setting) {
            data_set($mapped, $setting->key, json_decode($setting->value, true));
        }

        return [
            'tenant_id' => $tenantId,
            'tenant_encrypted_id' => $tenantEncryptedId,
            'settings' => $mapped,
        ];
    }

    public function updateTenantSettings(array $data): array
    {
        $tenantId = decrypt_to_int_or_null($data['tenant_id']);

        if ($tenantId === null || $this->tenantRepository->getById($tenantId) === null) {
            throw ValidationException::withMessages([
                'tenant_id' => 'The selected tenant is invalid.',
            ]);
        }

        $flatSettings = [
            'attendance.grace_minutes' => data_get($data, 'settings.attendance.grace_minutes'),
            'attendance.default_timezone' => data_get($data, 'settings.attendance.default_timezone'),
            'reporting.include_weekends' => data_get($data, 'settings.reporting.include_weekends'),
            'reporting.default_period_days' => data_get($data, 'settings.reporting.default_period_days'),
        ];

        foreach ($flatSettings as $key => $value) {
            if ($value === null) {
                continue;
            }

            $existing = $this->settingRepository->getByKeys(['tenant_id', 'key'], [$tenantId, $key]);

            if ($existing !== null) {
                $this->settingRepository->update($existing, [
                    'value' => json_encode($value),
                ]);

                continue;
            }

            $this->settingRepository->create([
                'tenant_id' => $tenantId,
                'key' => $key,
                'value' => json_encode($value),
            ]);
        }

        return $this->getTenantSettings($data['tenant_id']);
    }
}
