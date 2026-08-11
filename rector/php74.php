<?php
declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
    ])
    ->withPhpSets(php74: true)
    ->withoutParallel()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
    )
    ->withSkip([
        DeclareStrictTypesRector::class,
    ]);
