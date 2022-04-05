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

use Enlight_Components_Test_Controller_TestCase;
use Symfony\Component\HttpFoundation\Response;

class SearchTest extends Enlight_Components_Test_Controller_TestCase
{
    public function tearDown(): void
    {
        $this->reset();
        parent::tearDown();
    }

    public function testAjaxSearch()
    {
        $this->dispatch('ajax_search?sSearch=ipad');

        // Check for valid markup
        // Ignore whitespace, since this testcase checks wether the list is structured correctly (li following ul)
        self::assertStringContainsStringIgnoringWhitespace(
            '<ul class="results--list"> <li class="list--entry',
            $this->Response()->getBody()
        );
        // Check for expected search link and number of results
        self::assertStringContainsStringIgnoringWhitespace(
            '/search?sSearch=ipad" class="search-result--link entry--all-results-link block"> <i class="icon--arrow-right"></i> Alle Ergebnisse anzeigen </a> <span class="entry--all-results-number block"> 1 Treffer </span>',
            $this->Response()->getBody()
        );
        // Check for expected name and price
        self::assertStringContainsStringIgnoringWhitespace(
            ' alt="iPadtasche mit Stiftmappe" class="media--image"> </span> <span class="entry--name block"> iPadtasche mit Stiftmappe </span> <span class="entry--price block"> <div class="product--price"> <span class="price--default is--nowrap"> 39,99&nbsp;&euro; * </span> </div> <div class="price--unit" title="Inhalt"> </div> </span> </a> </li> <li class="entry--all-results block-group result--item">',
            $this->Response()->getBody()
        );

        $this->Response()->clearBody();
        $this->dispatch('ajax_search?sSearch=1234%a5%27%20having%201=1--%20');

        /*
         * See: https://shopware.atlassian.net/browse/SW-24678
         *
         * We need to make sure, that special characters in search queries do
         * not lead to errors. Search keywords are cached though, so a check for
         * an empty result is not guaranteed to be successful.
         */
        static::assertSame(Response::HTTP_OK, $this->Response()->getStatusCode());

        $body = $this->Response()->getBody();

        static::assertIsString($body);
        static::assertStringNotContainsStringIgnoringCase('an error has occured', $body); // Check for error-handler response as well, which would fake a HTTP 200 OK response
        static::assertStringNotContainsStringIgnoringCase('ein fehler ist aufgetreten', $body);

        //search for an emoji, might not be displayed correctly in IDE
        $this->Response()->clearBody();
        $this->dispatch('ajax_search?sSearch=👨‍🚒');

        static::assertSame(Response::HTTP_OK, $this->Response()->getStatusCode());

        $body = $this->Response()->getBody();

        static::assertIsString($body);
        static::assertStringNotContainsStringIgnoringCase('an error has occured', $body); // Check for error-handler response as well, which would fake a HTTP 200 OK response
        static::assertStringNotContainsStringIgnoringCase('ein fehler ist aufgetreten', $body);
    }

    /**
     * @dataProvider searchTermProvider
     */
    public function testSearchEscapes(string $term, string $filtered)
    {
        $this->dispatch(sprintf('search?sSearch=%s', $term));

        $body = $this->Response()->getBody();

        static::assertIsString($body);
        static::assertStringContainsString($filtered, $body, sprintf('Expected filtered term "%s" not found on search page', $filtered));
        static::assertStringNotContainsString($term, $body, sprintf('Malicious term "%s" found on search page', $term));
    }

    public function searchTermProvider()
    {
        return [
            ['"Apostrophes"', htmlentities(strip_tags('"Apostrophes"'))],
            ['"Apostrophe', htmlentities(strip_tags('"Apostrophe'))],
            ['<tags></tags>', htmlentities(strip_tags('<tags></tags>'))],
            ['<tag/>', htmlentities(strip_tags('<tag/>'))],
            ['&#x3c;tag>', htmlentities(strip_tags('Suchergebnisse | Shopware Demo'))], // This is stripped completely
            [
                "<script>
x='<%'
</script> %>/
alert(2)
</script>",
                'r _x=',
            ],
        ];
    }

    private static function assertStringContainsStringIgnoringWhitespace(string $needle, string $haystack, string $message = ''): void
    {
        static::assertStringContainsString(
            preg_replace('/\s/', '', $needle),
            preg_replace('/\s/', '', $haystack),
            $message
        );
    }
}
