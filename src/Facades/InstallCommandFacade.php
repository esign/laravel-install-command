<?php

namespace Esign\InstallCommand\Facades;

use Illuminate\Support\Facades\Facade;

class InstallCommandFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'install-command';
    }
}
