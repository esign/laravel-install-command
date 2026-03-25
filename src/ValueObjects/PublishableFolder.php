<?php

namespace Esign\InstallCommand\ValueObjects;

class PublishableFolder
{
    public function __construct(
        public string $path,
        public string $target,
    ) {}
}
