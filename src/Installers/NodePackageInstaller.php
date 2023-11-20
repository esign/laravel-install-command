<?php

namespace Esign\InstallCommand\Installers;

use Esign\InstallCommand\ValueObjects\NodePackage;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Process;

class NodePackageInstaller
{
    public function installPackages(Collection $packages): ProcessResult
    {
        $formattedDependencyList = $this->formatDependencyList($packages);

        return Process::path(base_path())
            ->run("npm install $formattedDependencyList")
            ->throw();
    }

    public function installDevPackages(Collection $packages): ProcessResult
    {
        $formattedDependencyList = $this->formatDependencyList($packages);

        return Process::path(base_path())
            ->run("npm install --save-dev $formattedDependencyList")
            ->throw();
    }

    protected function formatDependencyList(Collection $packages): string
    {
        return $packages
            ->map(fn (NodePackage $nodePackage) => $nodePackage->formattedInstallName())
            ->implode(' ');
    }
}
