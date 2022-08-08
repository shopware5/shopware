<?php

declare(strict_types=1);
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

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

class TemplateBlockTest extends TestCase
{
    private const BLOCK_REGEX = '/{block name=(["|\'])(.*?)(["|\'])}/';

    public function testForDuplicateBlocksBackend(): void
    {
        $files = (new Finder())
            ->in(__DIR__ . '/../../../../themes/Backend/ExtJs/backend')
            ->name('*.js')
            ->contains(self::BLOCK_REGEX)
        ;
        $result = [];
        foreach ($files as $file) {
            $matchCounter = preg_match_all(self::BLOCK_REGEX, $file->getContents(), $matches);
            if ($matchCounter === false) {
                continue;
            }

            foreach ($matches[0] as $match) {
                if (\in_array($match, $result, true)) {
                    static::fail(sprintf('%s has more than one match. FILE: %s', $match, $file->getRealPath()));
                }
                $result[] = $match;
            }
        }
        $this->expectNotToPerformAssertions();
    }
}
