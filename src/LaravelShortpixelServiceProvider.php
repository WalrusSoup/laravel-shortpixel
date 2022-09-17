<?php

namespace WalrusSoup\LaravelShortpixel;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelShortpixelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-shortpixel')
            ->hasConfigFile();
//            ->hasMigration('create_laravel-shortpixel_table');
    }

    public function registeringPackage(): void
    {
        $this->app->bind(LaravelShortpixel::class, function () {
            return LaravelShortpixel::createFromConfig(config('shortpixel'));
        });

        $this->app->alias(LaravelShortpixel::class, 'laravel-shortpixel');
    }
}
