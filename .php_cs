<?php

$finder = Symfony\CS\Finder::create()
    ->in(__DIR__.'/engine/Library/Enlight')
    ->in(__DIR__.'/engine/Shopware')
    ->in(__DIR__.'/tests')
;

return Symfony\CS\Config::create()
    ->setUsingCache(true)
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers([
        '-psr0', // tests are using a PSR-4 path that is not PSR-0 compatible
    ])
    ->finder($finder)
;
