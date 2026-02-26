<?php

namespace Giogiosw\Finder\Facades;

use Illuminate\Support\Facades\Facade;
use Giogiosw\Finder\FinderManager;

/**
 * @method static \Giogiosw\Finder\Drivers\DriverInterface driver(string $disk = null)
 * @method static array getConfig()
 * @method static array getAllDisks()
 *
 * @see \Giogiosw\Finder\FinderManager
 */
class Finder extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FinderManager::class;
    }
}
