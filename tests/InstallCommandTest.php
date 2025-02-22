<?php

namespace Esign\InstallCommand\Tests;

use PHPUnit\Framework\Attributes\Test;
use Esign\InstallCommand\Exceptions\CouldNotInstallNodePackagesException;
use Esign\InstallCommand\Tests\Support\InstallCommand;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

class InstallCommandTest extends TestCase
{
    #[Test]
    public function it_can_publish_files()
    {
        $this->artisan(InstallCommand::class);

        $this->assertFileExists(app_path('Services/UserService.php'));
    }

    #[Test]
    public function it_can_append_after_the_search_value_in_a_file()
    {
        $this->artisan(InstallCommand::class);

        $this->assertTrue(str_contains(
            haystack: File::get(app_path('Models/User.php')),
            needle: <<<PHP
                public function isAdmin(): bool
                {
                    return false;
                }
            PHP,
        ));
    }

    #[Test]
    public function it_can_install_composer_packages()
    {
        $this->artisan(InstallCommand::class);

        Process::assertRan('composer require my/composer-package my/specific-composer-package:^1.0');
        Process::assertRan('composer require --dev my/dev-composer-package');
    }

    #[Test]
    public function it_can_throw_an_exception_when_no_package_json_file_is_present()
    {
        File::delete(base_path('package.json'));
        $command = $this->artisan(InstallCommand::class);

        $command->expectsOutput("Could not find package.json file in the root of your project. Please create one using `npm init`");
    }

    #[Test]
    public function it_can_install_node_packages()
    {
        $this->artisan(InstallCommand::class);

        Process::assertRan('npm install my/node-package my/specific-node-package@^1.0');
        Process::assertRan('npm install --save-dev my/dev-node-package');
    }
}