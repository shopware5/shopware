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

namespace Shopware\Tests\Unit\Components\Template;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

class JavascriptEventTest extends TestCase
{
    use FileCheckerTrait;

    private const EVENT_REGEX = '/\.publish\(\'(.*)\'/';

    public function testForDuplicateEvents(): void
    {
        $files = (new Finder())
            ->in(__DIR__ . '/../../../../themes/Frontend/Responsive/frontend/_public/src/js')
            ->name('jquery.*.js')
            ->contains(self::EVENT_REGEX);

        $multipleOccurrencesAllowed = [
            'plugin/swAddArticle/onCloseModal' => 2,
            'plugin/swFilterComponent/onChange' => 2,
            'plugin/swMenuScroller/onSetOffset' => 2,
            'plugin/swListingActions/onSetCategoryParamsFromUrlParams' => 2,
            'onShowContent-' => 2,
            'plugin/swProductCompareMenu/onDeleteItemSuccess' => 2,
            'plugin/swSubCategoryNav/onLoadTemplate' => 2,
        ];

        $this->checkFiles($files, self::EVENT_REGEX, 'jQuery event', $multipleOccurrencesAllowed);
    }
}
