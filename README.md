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
use Esign\InstallCommand\ValueObjects\PublishableFolder;

class MyInstallCommand extends InstallCommand
{
    protected $signature = 'my-install-command {--force : Overwrite or re-append existing files}';
    protected $description = 'Publish my stubs and install my packages';

    protected function publishableFiles(): array
    {
        return [
            new PublishableFile(
                path: __DIR__ . '/../../stubs/my-stub.stub',
                target: base_path('my-stub.php'),
            ),
            new PublishableFolder(
                path: __DIR__ . '/../../stubs/resources',
                target: base_path('resources'),
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

### Publishing behavior

By default, file publishing is conservative:

- `PublishableFile`: publishes the file only when the target file does not already exist.
- `PublishableFolder`: evaluates files inside the folder individually. Missing files are published, existing files are skipped.
- `AppendableFile`: appends content when the target file does not exist yet, or when the appendable content is not already present.

If you run the command with `--force`, the installer becomes aggressive:

- existing published files are overwritten
- existing files inside published folders are overwritten
- appendable content is appended again, even if it is already present

### Publish overview

After publishing files, the command prints an overview of what happened:

```text
📄 Publish overview: 3 published, 1 skipped.
Published files:
 + /path/to/source.stub -> /path/to/target.php
Skipped files:
 - /path/to/source.stub -> /path/to/target.php
```

This makes it easier to see which files were created, overwritten, appended, or skipped during installation.


### Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
