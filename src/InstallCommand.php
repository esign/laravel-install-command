<?php

namespace Esign\InstallCommand;

use Esign\InstallCommand\Exceptions\CouldNotInstallNodePackagesException;
use Esign\InstallCommand\Installers\ComposerPackageInstaller;
use Esign\InstallCommand\Installers\FileInstaller;
use Esign\InstallCommand\Installers\NodePackageInstaller;
use Esign\InstallCommand\ValueObjects\AppendableFile;
use Esign\InstallCommand\ValueObjects\ComposerPackage;
use Esign\InstallCommand\ValueObjects\NodePackage;
use Esign\InstallCommand\ValueObjects\PublishableFile;
use Illuminate\Console\Command;
use Illuminate\Process\Exceptions\ProcessFailedException;

abstract class InstallCommand extends Command
{
    protected FileInstaller $fileInstaller;
    protected ComposerPackageInstaller $composerPackageInstaller;
    protected NodePackageInstaller $nodePackageInstaller;

    public function handle(
        FileInstaller $fileInstaller,
        ComposerPackageInstaller $composerPackageInstaller,
        NodePackageInstaller $nodePackageInstaller,
    )
    {
        $this->fileInstaller = $fileInstaller;
        $this->composerPackageInstaller = $composerPackageInstaller;
        $this->nodePackageInstaller = $nodePackageInstaller;

        foreach ([
            'publishableFiles' => 'installFiles',
            'composerPackages' => 'installComposerPackages',
            'nodePackages' => 'installNodePackages',
        ] as $method => $installMethod) {
            if (method_exists($this, $method)) {
                $this->{$installMethod}();
            }
        }

        $this->info('✨ All done, go build something amazing');
    }

    protected function installFiles(): void
    {
        $fileCollection = collect($this->publishableFiles());

        $this->info("🗄 Installing files...");

        $fileCollection
            ->filter(fn ($publishableFile) => $publishableFile instanceof PublishableFile)
            ->each(function (PublishableFile $publishableFile) {
                $this->fileInstaller->publishFile(
                    path: $publishableFile->path,
                    target: $publishableFile->target
                );
            });

        $fileCollection
            ->filter(fn ($publishableFile) => $publishableFile instanceof AppendableFile)
            ->each(function (AppendableFile $appendableFile) {
                $this->fileInstaller->appendToFile(
                    path: $appendableFile->path,
                    target: $appendableFile->target,
                    search: $appendableFile->search,
                );
            });

        $this->info("✅ Successfully installed files.");
    }

    protected function installComposerPackages(): void
    {
        $composerPackageCollection = collect($this->composerPackages());
        $requireComposerPackageCollection = $composerPackageCollection->filter(fn (ComposerPackage $composerPackage) => $composerPackage->dev === false);
        $requireDevComposerPackageCollection = $composerPackageCollection->filter(fn (ComposerPackage $composerPackage) => $composerPackage->dev === true);

        try {
            $this->info("📦 Installing composer packages...");

            $this->composerPackageInstaller->installPackages($requireComposerPackageCollection);
            $this->composerPackageInstaller->installDevPackages($requireDevComposerPackageCollection);

            $this->info("✅ Successfully installed composer packages.");
        } catch (ProcessFailedException $exception) {
            $command = $exception->result->command();

            $this->error("❌ Failed to install composer packages. Please run [$command] manually.");
        }
    }

    protected function installNodePackages(): void
    {
        $nodePackageCollection = collect($this->nodePackages());
        $requireNodePackageCollection = $nodePackageCollection->filter(fn (NodePackage $nodePackage) => $nodePackage->dev === false);
        $requireDevNodePackageCollection = $nodePackageCollection->filter(fn (NodePackage $nodePackage) => $nodePackage->dev === true);

        try {
            $this->info("📦 Installing node packages...");

            $this->nodePackageInstaller->installPackages($requireNodePackageCollection);
            $this->nodePackageInstaller->installDevPackages($requireDevNodePackageCollection);

            $this->info("✅ Successfully installed node packages.");
        } catch (ProcessFailedException $exception) {
            $command = $exception->result->command();

            $this->error("❌ Failed to install node packages. Please run [$command] manually.");
        } catch (CouldNotInstallNodePackagesException $exception) {
            $this->error($exception->getMessage());
        }
    }
}
