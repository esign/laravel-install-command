<?php

namespace Esign\InstallCommand\ValueObjects;

class NodePackage
{
    public function __construct(
        public string $name,
        public string $version,
        public bool $dev = false,
    ) {}

    public function formattedInstallName(): string
    {
        if (is_string($this->version)) {
            return "$this->name@$this->version";
        }

        return $this->name;
    }
}
