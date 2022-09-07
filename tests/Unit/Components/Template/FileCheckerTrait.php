<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Unit\Components\Template;

use Symfony\Component\Finder\Finder;

trait FileCheckerTrait
{
    /**
     * @param array<string, int> $multipleOccurrencesAllowed Indexed by item names, value is amount of allowed occurrences
     */
    protected function checkFiles(Finder $files, string $regEx, string $itemToTest, array $multipleOccurrencesAllowed = []): void
    {
        $items = [];
        foreach ($files as $file) {
            $matchCounter = preg_match_all($regEx, $file->getContents(), $matches);
            if ($matchCounter === false) {
                continue;
            }

            foreach ($matches[1] as $match) {
                if (\array_key_exists($match, $items)) {
                    ++$items[$match];
                } else {
                    $items[$match] = 1;
                }
            }
        }

        foreach ($items as $itemName => $count) {
            if (\array_key_exists($itemName, $multipleOccurrencesAllowed)) {
                $expectedCount = $multipleOccurrencesAllowed[$itemName];
            } else {
                $expectedCount = 1;
            }

            static::assertSame(
                $expectedCount,
                $count,
                sprintf('%s "%s" expected to have %s matches. Got %s matches instead', $itemToTest, $itemName, $expectedCount, $count)
            );
        }
    }
}
