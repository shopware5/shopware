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

namespace Shopware\Tests\Functional\Controllers\Widgets;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Controller_Response_ResponseTestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use PHPUnit\Framework\TestCase;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\Helper;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware_Controllers_Widgets_Listing as ListingController;

class ListingTest extends TestCase
{
    use ContainerTrait;

    public function testListingCountActionWithEmptySearch(): void
    {
        $controller = $this->getController();
        $controller->Request()->setParam('sSearch', '');

        $controller->listingCountAction();

        $result = $controller->View()->getAssign();

        static::assertIsArray($result);
        static::assertSame(196, $result['totalCount']);
    }

    /**
     * @group elasticSearch
     */
    public function testListingCountActionWithHTMLInFacets(): void
    {
        $this->prepareElasticSearchIndex();
        $controller = $this->getController();
        $controller->Request()->setParam('sCategory', '14');
        $controller->Request()->setParam('loadFacets', '1');

        $controller->listingCountAction();

        $controller->View()->addTemplateDir(__DIR__ . '/../../../../themes/Frontend/Bare', 'test');

        $html = $controller->View()->fetch('widgets/listing/listing_count.tpl');

        preg_match('/<div id="facets">(.*?)<\/div>/s', $html, $match);
        $facetsJson = trim($match[1]);
        $facets = json_decode(htmlspecialchars_decode($facetsJson, ENT_QUOTES), true);
        static::assertIsArray($facets);

        foreach ($facets as $facet) {
            if (\array_key_exists('test', $facet)) {
                static::assertSame('<b>', $facet['test']);

                return;
            }
        }
        static::fail('Test facet not found');
    }

    private function getController(): ListingController
    {
        $controller = new ListingController();

        $request = new Enlight_Controller_Request_RequestTestCase();
        $response = new Enlight_Controller_Response_ResponseTestCase();

        $front = $this->getContainer()->get('front');
        $front->setRequest($request);
        $front->setResponse($response);

        $container = $this->getContainer();
        static::assertInstanceOf(Container::class, $container);

        $controller->setContainer($container);
        $controller->setFront($front);
        $controller->setRequest($request);
        $controller->setResponse($response);
        $controller->setView(new Enlight_View_Default(new Enlight_Template_Manager()));

        return $controller;
    }

    private function prepareElasticSearchIndex(): void
    {
        $helper = new Helper($this->getContainer());
        $context = $helper->createContext(
            $helper->createCustomerGroup(),
            $helper->getShop(),
            [],
            null,
            $helper->createCurrency()
        );
        $shop = $context->getShop();
        $shop->setCurrency($context->getCurrency());
        $helper->refreshSearchIndexes($shop);
    }
}
