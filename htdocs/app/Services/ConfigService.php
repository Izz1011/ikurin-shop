<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Repositories\SettingRepository;

class ConfigService
{
    protected SettingRepository $settingRepo;

    public function __construct(?SettingRepository $settingRepo = null)
    {
        $this->settingRepo = $settingRepo ?? new SettingRepository();
    }

    /**
     * Get public configuration according to the API contract.
     */
    public function getPublicConfig(): array
    {
        $siteName = $this->settingRepo->get('site_name', 'Ikurin Shop');
        $serviceFeePercent = $this->settingRepo->getInt('service_fee_percent', 2);

        $pusherKey = (string) Env::get('PUSHER_KEY', '');
        $pusherCluster = (string) Env::get('PUSHER_CLUSTER', 'ap1');

        $categories = ['bakery', 'snack', 'makanan_berat', 'minuman'];

        return [
            'site_name' => $siteName,
            'service_fee_percent' => $serviceFeePercent,
            'pusher_key' => $pusherKey,
            'pusher_cluster' => $pusherCluster,
            'categories' => $categories,
        ];
    }
}
