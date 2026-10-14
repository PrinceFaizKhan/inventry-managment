<?php

namespace Modules\Settings\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Auto-discover translations across all sub-menu slices
        foreach (File::glob(base_path('Modules/Settings/*/Resources/lang'), GLOB_ONLYDIR) as $langDir) {
            $sliceName = strtolower(basename(dirname($langDir, 2)));
            $this->loadTranslationsFrom($langDir, 'settings.'.$sliceName);
        }
    }
}
