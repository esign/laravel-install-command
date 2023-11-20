<?php

namespace Esign\InstallCommand\Installers;

use Illuminate\Filesystem\Filesystem;

class FileInstaller
{
    public function __construct(
        protected Filesystem $filesystem,
    ) {}

    public function publishFile(string $path, string $target): void
    {
        $this->filesystem->ensureDirectoryExists(
            path: dirname($target)
        );

        $this->filesystem->copy(
            path: $path,
            target: $target,
        );
    }

    public function appendToFile(string $path, string $target, ?string $search): void
    {
        $noSearchResultSupplied = is_null($search);
        $searchResultNotFound = ! $this->fileContainsString(path: $target, search: $search);

        if ($noSearchResultSupplied || $searchResultNotFound) {
            $this->appendFileToEndOfFile(path: $path, target: $target);
            return;
        }

        $this->appendFileAfterSearchResultInFile(path: $path, target: $target, search: $search);
    }

    public function appendFileToEndOfFile(string $path, string $target): void
    {
        $this->filesystem->append(
            path: $target,
            data: $this->filesystem->get($path)
        );
    }

    public function appendFileAfterSearchResultInFile(string $path, string $target, string $search): void
    {
        $this->filesystem->replaceInFile(
            search: $search,
            replace: $search . PHP_EOL . $this->filesystem->get($path),
            path: $target,
        );
    }

    public function fileContainsString(string $path, string $search): bool
    {
        return str_contains(
            haystack: $this->filesystem->get($path),
            needle: $search
        );
    }
}
