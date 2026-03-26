<?php

namespace Esign\InstallCommand\Installers;

use Esign\InstallCommand\ValueObjects\PublishResult;
use Illuminate\Filesystem\Filesystem;
use SplFileInfo;

class FileInstaller
{
    public function __construct(
        protected Filesystem $filesystem,
    ) {}

    public function publishFile(string $path, string $target, bool $force = true): PublishResult
    {
        if ($this->filesystem->exists($target) && !$force) {
            return PublishResult::skipped(path: $path, target: $target);
        }

        $this->filesystem->ensureDirectoryExists(
            path: dirname($target)
        );

        $this->filesystem->copy(
            path: $path,
            target: $target,
        );

        return PublishResult::published(path: $path, target: $target);
    }

    /** @return array<PublishResult> */
    public function publishFolder(string $path, string $target, bool $force = true): array
    {
        $sourceDirectory = rtrim($path, DIRECTORY_SEPARATOR);
        $targetDirectory = rtrim($target, DIRECTORY_SEPARATOR);

        return collect($this->filesystem->allFiles($sourceDirectory))
            ->map(function (SplFileInfo $sourceFile) use ($sourceDirectory, $targetDirectory, $force) {
                $sourcePath = $sourceFile->getPathname();
                $relativePath = ltrim(str_replace($sourceDirectory, '', $sourcePath), DIRECTORY_SEPARATOR);
                $targetPath = $targetDirectory . DIRECTORY_SEPARATOR . $relativePath;

                return $this->publishFile($sourcePath, $targetPath, $force);
            })
            ->all();
    }

    public function appendToFile(string $path, string $target, ?string $search, bool $force = true): PublishResult
    {
        $contentToAppend = $this->filesystem->get($path);

        if (!$this->filesystem->exists($target)) {
            $this->appendFileToEndOfFile(path: $path, target: $target);

            return PublishResult::published(path: $path, target: $target);
        }

        if (!$force && $this->fileContainsString(path: $target, search: $contentToAppend)) {
            return PublishResult::skipped(path: $path, target: $target);
        }

        $noSearchResultSupplied = is_null($search);
        $searchResultNotFound = ! $this->fileContainsString(path: $target, search: $search);

        if ($noSearchResultSupplied || $searchResultNotFound) {
            $this->appendFileToEndOfFile(path: $path, target: $target);

            return PublishResult::published(path: $path, target: $target);
        }

        $this->appendFileAfterSearchResultInFile(path: $path, target: $target, search: $search);

        return PublishResult::published(path: $path, target: $target);
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

    public function fileContainsString(string $path, ?string $search): bool
    {
        return str_contains(
            haystack: $this->filesystem->get($path),
            needle: $search ?? ''
        );
    }
}
