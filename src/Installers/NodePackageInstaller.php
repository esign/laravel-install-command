<?php

namespace Esign\InstallCommand\Installers;

use Esign\InstallCommand\Exceptions\CouldNotInstallNodePackagesException;
use Esign\InstallCommand\ValueObjects\NodePackage;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;

class NodePackageInstaller
{
    public function __construct(
        protected Filesystem $filesystem,
    ) {}

    public function installPackages(Collection $packages): ProcessResult
    {
        $this->ensurePackageJsonExists();
        $formattedDependencyList = $this->formatDependencyList($packages);

        return Process::path(base_path())
            ->forever()
            ->run("npm install $formattedDependencyList")
            ->throw();
    }

    public function installDevPackages(Collection $packages): ProcessResult
    {
        $this->ensurePackageJsonExists();
        $formattedDependencyList = $this->formatDependencyList($packages);

        return Process::path(base_path())
            ->forever()
            ->run("npm install --save-dev $formattedDependencyList")
            ->throw();
    }

    protected function formatDependencyList(Collection $packages): string
    {
        return $packages
            ->map(fn (NodePackage $nodePackage) => $nodePackage->formattedInstallName())
            ->implode(' ');
    }

    protected function ensurePackageJsonExists(): void
    {
        if (! $this->filesystem->exists(base_path('package.json'))) {
            throw CouldNotInstallNodePackagesException::packageJsonNotFound();
        }
    }
}
