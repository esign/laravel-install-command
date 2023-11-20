<?php

namespace Esign\InstallCommand\Exceptions;

use Exception;

class CouldNotInstallNodePackagesException extends Exception
{
    public static function packageJsonNotFound(): static
    {
        return new static("Could not find package.json file in the root of your project. Please create one using `npm init`");
    }
}
