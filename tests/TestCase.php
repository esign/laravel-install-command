<?php

namespace Esign\InstallCommand\Tests;

use Esign\InstallCommand\InstallCommandServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [InstallCommandServiceProvider::class];
    }
} 