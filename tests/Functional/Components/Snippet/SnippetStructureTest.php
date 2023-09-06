<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Components\Snippet;

use DirectoryIterator;
use Enlight_Components_Test_TestCase;

class SnippetStructureTest extends Enlight_Components_Test_TestCase
{
    public function testSnippetsShouldBeValid(): void
    {
        $rootDir = Shopware()->Container()->getParameter('kernel.root_dir');
        $source = $rootDir . '/snippets';

        $validator = Shopware()->Container()->get('shopware.snippet_validator');

        $validationResult = $validator->validate($source);

        $pluginBasePath = Shopware()->Container()->get('application')->AppPath('Plugins_Default');
        foreach (['Backend', 'Core', 'Frontend'] as $namespace) {
            foreach (new DirectoryIterator($pluginBasePath . $namespace) as $pluginDir) {
                if ($pluginDir->isDot() || !$pluginDir->isDir()) {
                    continue;
                }

                $validationResult = array_merge($validationResult, $validator->validate($pluginDir->getPathname()));
            }
        }

        static::assertEmpty($validationResult, "Snippet validation errors detected: \n" . implode("\n", $validationResult));
    }
}
