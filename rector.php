<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\SafeDeclareStrictTypesRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPhpSets(php85: true)
    ->withSkip([
        // DeclareStrictTypesRector::class,
        SafeDeclareStrictTypesRector::class,
    ])
    ->withParallel(timeoutSeconds: 800, maxNumberOfProcess: 4, jobSize: 20)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
    );
