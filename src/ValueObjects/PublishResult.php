<?php

namespace Esign\InstallCommand\ValueObjects;

class PublishResult
{
    public function __construct(
        public string $path,
        public string $target,
        public bool $published,
    ) {}

    public static function published(string $path, string $target): self
    {
        return new self(path: $path, target: $target, published: true);
    }

    public static function skipped(string $path, string $target): self
    {
        return new self(path: $path, target: $target, published: false);
    }
}