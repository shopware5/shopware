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

namespace Shopware\Tests\Functional\Modules\Articles;

use Enlight_Components_Test_TestCase;
use sArticles;

class sGetArticlesByCategoryTest extends Enlight_Components_Test_TestCase
{
    /**
     * Module instance
     *
     * @var sArticles
     */
    protected $module;

    /**
     * Test set up method
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->module = Shopware()->Modules()->Articles();
    }

    public function testGetArticles(): void
    {
        $categories = [5, 6, 8, 12, 13, 14, 15, 31];
        foreach ($categories as $id => $expected) {
            $data = $this->module->sGetArticlesByCategory($id);
            static::assertIsArray($data);

            foreach ($data['sArticles'] as $key => $article) {
                static::assertSame($key, $article['ordernumber']);
            }
        }
    }
}
