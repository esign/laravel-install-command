# Simplify stub publishing and effortlessly manage Composer and Node packages

[![Latest Version on Packagist](https://img.shields.io/packagist/v/esign/laravel-install-command.svg?style=flat-square)](https://packagist.org/packages/esign/laravel-install-command)
[![Total Downloads](https://img.shields.io/packagist/dt/esign/laravel-install-command.svg?style=flat-square)](https://packagist.org/packages/esign/laravel-install-command)
![GitHub Actions](https://github.com/esign/laravel-install-command/actions/workflows/main.yml/badge.svg)

This package provides a simple way to publish stubs and install Composer and Node packages using a Laravel Command.
This may be useful when creating packages that require stubs to be publish and require Composer and Node packages to be installed.

## Installation

You can install the package via composer:

```bash
composer require esign/laravel-install-command
```

## Usage
To make use of the InstallJob you may create a new command that extends the InstallCommand class.
In this command you may specify the stubs to publish and the Composer and Node packages to install.
```php
use Esign\InstallCommand\InstallCommand;
use Esign\InstallCommand\ValueObjects\AppendableFile;
use Esign\InstallCommand\ValueObjects\ComposerPackage;
use Esign\InstallCommand\ValueObjects\NodePackage;
use Esign\InstallCommand\ValueObjects\PublishableFile;

class MyInstallCommand extends InstallCommand
{
    protected $signature = 'my-install-command';
    protected $description = 'Publish my stubs and install my packages';

    protected function publishableFiles(): array
    {
        return [
            new PublishableFile(
                path: __DIR__ . '/../../stubs/my-stub.stub',
                target: base_path('my-stub.php'),
            ),
            new AppendableFile(
                path: __DIR__ . '/../../stubs/my-appendable-stub.stub',
                target: base_path('my-appendable-stub.php'),
            ),
            new AppendableFile(
                path: __DIR__ . '/../../stubs/my-appendable-stub.stub',
                target: base_path('my-appendable-stub.php'),
                search: 'insert-after-line-with-this-string',
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
```


### Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
