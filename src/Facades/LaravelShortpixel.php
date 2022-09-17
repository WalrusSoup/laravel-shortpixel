<?php

namespace WalrusSoup\LaravelShortpixel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \WalrusSoup\LaravelShortpixel\LaravelShortpixel
 */
class LaravelShortpixel extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \WalrusSoup\LaravelShortpixel\LaravelShortpixel::class;
    }
}
