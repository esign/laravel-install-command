<?php

namespace Esign\InstallCommand\Tests;

use Esign\InstallCommand\Tests\Support\InstallCommand;
use Illuminate\Console\Application;
use Illuminate\Foundation\Console\ModelMakeCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        File::put(base_path('package.json'), '{}');

        Application::starting(function (Application $artisan) {
            $artisan->add(app(ModelMakeCommand::class));
            $artisan->call(ModelMakeCommand::class, ['name' => 'User']);
            $artisan->add(app(InstallCommand::class));
        });

        Process::fake();
    }

    protected function tearDown(): void
    {
        File::delete(app_path('Models/User.php'));
        File::delete(base_path('package.json'));

        parent::tearDown();
    }
}