<?php

namespace Esign\InstallCommand\Installers;

use Esign\InstallCommand\ValueObjects\ComposerPackage;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;

class ComposerPackageInstaller
{
    public function installPackages(Collection $packages): ProcessResult
    {
        $formattedDependencyList = $this->formatDependencyList($packages);

        return Process::path(base_path())
            ->run("composer require $formattedDependencyList")
            ->throw();
    }

    public function installDevPackages(Collection $packages): ProcessResult
    {
        $formattedDependencyList = $this->formatDependencyList($packages);

        return Process::path(base_path())
            ->run("composer require --dev $formattedDependencyList")
            ->throw();
    }

    protected function formatDependencyList(Collection $packages): string
    {
        return $packages
            ->map(fn (ComposerPackage $composerPackage) => $composerPackage->formattedInstallName())
            ->implode(' ');
    }
}
