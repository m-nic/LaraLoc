<?php

namespace mNic\LaraLoc\Facades;

use Illuminate\Support\Facades\Facade;
use mNic\LaraLoc\LaraLocService;

class LaraLoc extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return LaraLocService::getFacadeAccessorName();
    }
}
