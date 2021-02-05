<?php

namespace ShoppinPal\YapepCommon\Robo;

class ApplicationConfig
{
    /** @var string */
    private $name;
    /** @var array */
    private $codePaths = [];
    /** @var string */
    private $entryPointPath;
    /** @var string */
    private $targetJsonPath;
    /** @var array */
    private $errorCodesByMethod = [];

    public function __construct(string $name, array $codePaths, string $entryPointPath, string $targetJsonPath)
    {
        $this->name           = $name;
        $this->codePaths      = $codePaths;
        $this->entryPointPath = $entryPointPath;
        $this->targetJsonPath = $targetJsonPath;
    }

    public function setErrorCodes(string $method, int ...$errorCodes): self
    {
        $this->errorCodesByMethod[$method] = $errorCodes;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCodePaths(): array
    {
        return $this->codePaths;
    }

    public function getEntryPointPath(): string
    {
        return $this->entryPointPath;
    }

    public function getTargetJsonPath(): string
    {
        return $this->targetJsonPath;
    }

    public function getErrorCodes(string $method): array
    {
        return $this->errorCodesByMethod[$method] ?? [];
    }
}
