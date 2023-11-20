<?php

namespace Esign\InstallCommand\ValueObjects;

class AppendableFile
{
    public function __construct(
        public string $path,
        public string $target,
        public ?string $search = null,
    ) {}
}
