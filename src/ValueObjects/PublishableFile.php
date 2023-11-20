<?php

namespace Esign\InstallCommand\ValueObjects;

class PublishableFile
{
    public function __construct(
        public string $path,
        public string $target,
    ) {}
}
