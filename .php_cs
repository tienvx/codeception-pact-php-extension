<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
    ->exclude('_data')
    ->exclude('_output')
    ->exclude('_generated')
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
    ])
    ->setUsingCache(false)
    ->setFinder($finder)
;
