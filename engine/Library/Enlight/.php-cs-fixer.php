<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

use PhpCsFixer\Config;
use PhpCsFixerCustomFixers\Fixer\NoSuperfluousConcatenationFixer;
use PhpCsFixerCustomFixers\Fixer\NoUselessCommentFixer;
use PhpCsFixerCustomFixers\Fixer\NoUselessParenthesisFixer;
use PhpCsFixerCustomFixers\Fixer\NoUselessStrlenFixer;
use PhpCsFixerCustomFixers\Fixer\PhpdocParamTypeFixer;
use PhpCsFixerCustomFixers\Fixer\SingleSpaceAfterStatementFixer;
use PhpCsFixerCustomFixers\Fixer\SingleSpaceBeforeStatementFixer;
use PhpCsFixerCustomFixers\Fixers;

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__);

$header = <<<EOF
Enlight

LICENSE

This source file is subject to the new BSD license that is bundled
with this package in the file LICENSE.txt.
It is also available through the world-wide-web at this URL:
http://enlight.de/license
If you did not receive a copy of the license and are unable to
obtain it through the world-wide-web, please send an email
to license@shopware.de so we can send you a copy immediately.

@category   Enlight
@copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
@license    http://enlight.de/license     New BSD License
EOF;

return (new Config())
    ->registerCustomFixers(new Fixers())
    ->setRiskyAllowed(true)
    ->setCacheFile('var/cache/php-cs-fixer-enlight')
    ->setRules([
        '@PSR12' => true,
        '@Symfony' => true,

        'class_attributes_separation' => ['elements' => ['method' => 'one', 'property' => 'one']],
        'concat_space' => ['spacing' => 'one'],
        'doctrine_annotation_indentation' => true,
        'doctrine_annotation_spaces' => true,
        'phpdoc_no_package' => false,
        'header_comment' => ['header' => $header, 'separate' => 'bottom', 'comment_type' => 'PHPDoc'],
        'no_useless_else' => true,
        'no_useless_return' => true,
        'no_superfluous_phpdoc_tags' => true,
        'phpdoc_order' => true,
        'phpdoc_summary' => false,
        'phpdoc_var_annotation_correct_order' => true,
        'php_unit_test_case_static_method_calls' => true,
        'single_line_throw' => false,
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'operator_linebreak' => ['only_booleans' => true],
        'native_function_invocation' => true,

        NoUselessCommentFixer::name() => true,
        SingleSpaceAfterStatementFixer::name() => true,
        SingleSpaceBeforeStatementFixer::name() => true,
        PhpdocParamTypeFixer::name() => true,
        NoSuperfluousConcatenationFixer::name() => true,
        NoUselessStrlenFixer::name() => true,
        NoUselessParenthesisFixer::name() => true,
    ])
    ->setFinder($finder);
