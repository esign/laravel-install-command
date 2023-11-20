<?php

namespace Esign\InstallCommand\ValueObjects;

class ComposerPackage
{
    public function __construct(
        public string $name,
        public ?string $version = null,
        public bool $dev = false,
    ) {}

    public function formattedInstallName(): string
    {
        if (is_string($this->version)) {
            return "$this->name:$this->version";
        }

        return $this->name;
    }
}
