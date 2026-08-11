<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPhpSets(php82: true)
    ->withParallel(timeoutSeconds: 800, maxNumberOfProcess: 4, jobSize: 20)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
    )
    ->withSkip([
        DeclareStrictTypesRector::class,
    ]);

