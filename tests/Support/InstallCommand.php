<?php

namespace Esign\InstallCommand\Tests\Support;

use Esign\InstallCommand\InstallCommand as BaseInstallCommand;
use Esign\InstallCommand\ValueObjects\AppendableFile;
use Esign\InstallCommand\ValueObjects\ComposerPackage;
use Esign\InstallCommand\ValueObjects\NodePackage;
use Esign\InstallCommand\ValueObjects\PublishableFile;

class InstallCommand extends BaseInstallCommand
{
    protected $signature = 'app:install-command';

    protected function publishableFiles(): array
    {
        return [
            new PublishableFile(
                path: __DIR__ . '/stubs/app/Services/UserService.php',
                target: base_path('app/Services/UserService.php'),
            ),
            new AppendableFile(
                path: __DIR__ . '/stubs/app/Models/User.php',
                target: base_path('app/Models/User.php'),
                search: 'use HasFactory;',
            ),
        ];
    }

    protected function composerPackages(): array
    {
        return [
            new ComposerPackage(name: 'my/composer-package'),
            new ComposerPackage(name: 'my/specific-composer-package', version: '^1.0'),
            new ComposerPackage(name: 'my/dev-composer-package', dev: true),
        ];
    }

    protected function nodePackages(): array
    {
        return [
            new NodePackage(name: 'my/node-package'),
            new NodePackage(name: 'my/specific-node-package', version: '^1.0'),
            new NodePackage(name: 'my/dev-node-package', dev: true),
        ];
    }
}
