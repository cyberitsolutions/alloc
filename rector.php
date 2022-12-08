<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/announcement',
        __DIR__ . '/audit',
        __DIR__ . '/calendar',
        __DIR__ . '/client',
        __DIR__ . '/comment',
        __DIR__ . '/config',
        __DIR__ . '/email',
        __DIR__ . '/finance',
        __DIR__ . '/help',
        __DIR__ . '/home',
        __DIR__ . '/installation',
        __DIR__ . '/invoice',
        __DIR__ . '/item',
        __DIR__ . '/login',
        __DIR__ . '/patches',
        __DIR__ . '/person',
        __DIR__ . '/project',
        __DIR__ . '/reminder',
        __DIR__ . '/report',
        __DIR__ . '/sale',
        __DIR__ . '/search',
        __DIR__ . '/security',
        __DIR__ . '/services',
        __DIR__ . '/shared',
        __DIR__ . '/task',
        __DIR__ . '/time',
        __DIR__ . '/tools',
        __DIR__ . '/util',
        __DIR__ . '/wiki',
        __DIR__ . '/zend',
    ]);

    // // register a single rule
    // $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // define sets of rules
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_54,
    ]);
};
