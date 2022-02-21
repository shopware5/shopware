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

namespace Shopware\Tests\Functional\Regressions;

use Enlight_Components_Test_Controller_TestCase;

class Ticket4788Test extends Enlight_Components_Test_Controller_TestCase
{
    /**
     * @var array<int, int>
     */
    protected array $articlesToTest = [
        206 => 23,
        209 => 23,
    ];

    /**
     * @var array<int, array<string, mixed>>
     */
    protected array $backup;

    protected string $shortDescription = '';

    protected string $longDescription = '&nbsp;äü @ Старт <strong>test</strong>';

    protected string $longDescriptionStripped = 'äü @ Старт test';

    /**
     * Set up test case, fix demo data where needed
     */
    public function setUp(): void
    {
        parent::setUp();

        // Get a copy of article descriptions
        $ids = implode(', ', array_keys($this->articlesToTest));
        $sql = sprintf('SELECT `id`, `description_long`, `description` FROM s_articles WHERE `id` IN (%s)', $ids);
        $this->backup = Shopware()->Db()->fetchAssoc($sql);

        // Update article description, set UTF-8 string
        $sql = sprintf('UPDATE s_articles SET `description_long`= ?, `description` = ? WHERE `id` IN (%s)', $ids);
        Shopware()->Db()->query($sql, [$this->longDescription, $this->shortDescription]);
    }

    /**
     * Cleaning up testData
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        // Restore old descriptions
        $sql = '';
        $values = [];
        foreach ($this->backup as $key => $fields) {
            $sql .= sprintf('UPDATE s_articles SET `description_long` = ?, `description` = ? WHERE `id` = %s;', $key);
            $values[] = $fields['description_long'];
            $values[] = $fields['description'];
        }
        Shopware()->Db()->query($sql, $values);
    }

    /**
     * Test for long description fallback in category listing
     */
    public function testArticleLongDescriptionForCategoryListing(): void
    {
        $oldValue = Shopware()->Config()->get('useShortDescriptionInListing');
        Shopware()->Db()->query("UPDATE s_core_config_elements SET value = 'b:1;' WHERE name = 'useShortDescriptionInListing'");
        Shopware()->Container()->get('cache')->clean();

        // Count occurrences in category listing
        $this->dispatch('/cat/index/sCategory/23');
        $body = $this->Response()->getBody();
        static::assertIsString($body);
        $count = mb_substr_count($body, $this->longDescriptionStripped);
        static::assertSame(2, $count);

        $oldValue = 'b:' . $oldValue . ';';
        Shopware()->Db()->query(
            "UPDATE s_core_config_elements SET value = ? WHERE name = 'useShortDescriptionInListing'",
            [$oldValue]
        );

        $this->reset();
    }

    /**
     * Test long description on article detail page
     */
    public function testArticleLongDescriptionOnDetailPage(): void
    {
        // Check
        foreach ($this->articlesToTest as $articleId => $categoryId) {
            $this->dispatch(sprintf('/detail/index/sArticle/%s', $articleId));
            $body = $this->Response()->getBody();
            static::assertIsString($body);
            static::assertStringContainsString($this->longDescription, $body);
            static::assertStringContainsString($this->longDescriptionStripped, $body);
            $this->reset();
        }
    }
}
