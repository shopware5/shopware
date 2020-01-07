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

namespace Shopware\Tests\Functional\Controllers\Frontend;

class SearchTest extends \Enlight_Components_Test_Controller_TestCase
{
    public function tearDown(): void
    {
        $this->reset();
    }

    public function testAjaxSearch()
    {
        $this->dispatch('ajax_search?sSearch=ipad');

        // Check for valid markup
        static::assertContains(
            ' <ul class="results--list"> <li class="list--entry block-group result--item">',
            $this->Response()->getBody()
        );
        // Check for expected search link and number of results
        static::assertContains(
            '/search?sSearch=ipad" class="search-result--link entry--all-results-link block"> <i class="icon--arrow-right"></i> Alle Ergebnisse anzeigen </a> <span class="entry--all-results-number block"> 1 Treffer </span>',
            $this->Response()->getBody()
        );
        // Check for expected name and price
        static::assertContains(
            ' alt="iPadtasche mit Stiftmappe" class="media--image"> </span> <span class="entry--name block"> iPadtasche mit Stiftmappe </span> <span class="entry--price block"> <div class="product--price"> <span class="price--default is--nowrap"> 39,99&nbsp;&euro; * </span> </div> <div class="price--unit" title="Inhalt"> </div> </span> </a> </li> <li class="entry--all-results block-group result--item">',
            $this->Response()->getBody()
        );

        $this->Response()->clearBody();
        $this->dispatch('ajax_search?sSearch=1234%a5%27%20having%201=1--%20');
        static::assertContains('Keine Suchergebnisse gefunden', $this->Response()->getBody());
        //search for an emoji, might not be displayed correctly in IDE
        $this->Response()->clearBody();
        $this->dispatch('ajax_search?sSearch=👨‍🚒');
        static::assertContains('Keine Suchergebnisse gefunden', $this->Response()->getBody());
    }

    /**
     * @dataProvider searchTermProvider
     */
    public function testSearchEscapes(string $term, string $filtered)
    {
        $this->dispatch(sprintf('search?sSearch=%s', $term));

        static::assertContains($filtered, $this->Response()->getBody());
        static::assertNotContains($term, $this->Response()->getBody());
    }

    public function searchTermProvider()
    {
        return [
            ['"Apostrophes"', htmlentities(strip_tags('"Apostrophes"'))],
            ['"Apostrophe', htmlentities(strip_tags('"Apostrophe'))],
            ['<tags></tags>', htmlentities(strip_tags('<tags></tags>'))],
            ['<tag/>', htmlentities(strip_tags('<tag/>'))],
            ['&#x3c;tag>', htmlentities(strip_tags('Suchergebnisse | Shopware Demo'))], // This is stripped completely
            ["<script>
x='<%'
</script> %>/
alert(2)
</script>", "r _x='"],
        ];
    }
}
