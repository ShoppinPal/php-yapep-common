<?php

namespace ShoppinPal\YapepCommon\Robo\Swagger;

use Robo\Collection\CollectionBuilder;
use Robo\Task\Base\Exec;
use ShoppinPal\YapepCommon\Robo\ApplicationConfig;

class DocumentationGenerator
{
    /** @var GeneratorInterface */
    private $generator;

    public function __construct(GeneratorInterface $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param CollectionBuilder[]|Exec[] $swaggerTasks
     * @param ApplicationConfig          ...$configs
     */
    public function generate(array $swaggerTasks, ApplicationConfig ...$configs): void
    {
        foreach ($configs as $index => $config) {
            $currentTask = $swaggerTasks[$index];
            $swaggerPath = $config->getTargetJsonPath();

            foreach ($config->getCodePaths() as $path) {
                $currentTask->arg($path);
            }
            $currentTask->arg($config->getEntryPointPath());

            $currentTask->option('--output')
                ->arg($swaggerPath)
                ->run();

            $jsonContent = json_decode(file_get_contents($swaggerPath), true);

            $this->generator->addErrorsToSwaggerJsonContent($jsonContent, $config);

            file_put_contents($swaggerPath, json_encode($jsonContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $currentTask = null;
        }
    }
}