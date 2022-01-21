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

namespace Shopware\Tests\Functional\Bundle\EmotionBundle\ComponentHandler;

use OutOfBoundsException;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\EmotionBundle\ComponentHandler\ArticleSliderComponentHandler;
use Shopware\Bundle\EmotionBundle\Struct\Collection\PrepareDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\EmotionBundle\Struct\ElementConfig;
use Shopware\Bundle\SearchBundle\BatchProductSearchResult;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\ShopContextTrait;

class ProductSliderComponentHandlerTest extends TestCase
{
    use ContainerTrait;
    use ShopContextTrait;

    public function testPrepareWithNoSelectedProducts(): void
    {
        $articleSlideComponentHandler = $this->getContainer()->get(ArticleSliderComponentHandler::class);

        $prepareDataCollection = new PrepareDataCollection();
        $elementConfig = new ElementConfig();
        $elementConfig->set(ArticleSliderComponentHandler::SLIDER_TYPE_KEY, ArticleSliderComponentHandler::TYPE_STATIC_PRODUCT);
        $elementConfig->set(ArticleSliderComponentHandler::SELECTED_PRODUCTS, null);
        $element = new Element();
        $element->setId(PHP_INT_MAX);
        $element->setConfig($elementConfig);
        $shopContext = $this->createShopContext();

        $articleSlideComponentHandler->prepare(
            $prepareDataCollection,
            $element,
            $shopContext
        );

        static::assertSame([
            ArticleSliderComponentHandler::CRITERIA_KEY . PHP_INT_MAX => [],
        ], $prepareDataCollection->getBatchRequest()->getProductNumbers());
    }

    public function testPrepareWithNotSelectedVariants(): void
    {
        $articleSlideComponentHandler = $this->getContainer()->get(ArticleSliderComponentHandler::class);

        $prepareDataCollection = new PrepareDataCollection();
        $elementConfig = new ElementConfig();
        $elementConfig->set(ArticleSliderComponentHandler::SLIDER_TYPE_KEY, ArticleSliderComponentHandler::TYPE_STATIC_VARIANT);
        $elementConfig->set(ArticleSliderComponentHandler::SELECTED_VARIANTS, null);
        $element = new Element();
        $element->setId(PHP_INT_MAX);
        $element->setConfig($elementConfig);
        $shopContext = $this->createShopContext();

        $articleSlideComponentHandler->prepare(
            $prepareDataCollection,
            $element,
            $shopContext
        );

        static::assertSame([
            ArticleSliderComponentHandler::CRITERIA_KEY . PHP_INT_MAX => [],
        ], $prepareDataCollection->getBatchRequest()->getProductNumbers());
    }

    public function testHandleWithNoSelectedProducts(): void
    {
        $articleSlideComponentHandler = $this->getContainer()->get(ArticleSliderComponentHandler::class);

        $resolvedDataCollection = new ResolvedDataCollection();
        $resolvedDataCollection->setBatchResult(new BatchProductSearchResult([]));
        $elementConfig = new ElementConfig();
        $elementConfig->set(ArticleSliderComponentHandler::SLIDER_TYPE_KEY, ArticleSliderComponentHandler::TYPE_STATIC_PRODUCT);
        $elementConfig->set(ArticleSliderComponentHandler::SELECTED_PRODUCTS, null);
        $element = new Element();
        $element->setId(PHP_INT_MAX);
        $element->setConfig($elementConfig);
        $shopContext = $this->createShopContext();

        $this->expectException(OutOfBoundsException::class);
        $articleSlideComponentHandler->handle(
            $resolvedDataCollection,
            $element,
            $shopContext
        );
    }

    public function testHandleWithNoSelectedVariants(): void
    {
        $articleSlideComponentHandler = $this->getContainer()->get(ArticleSliderComponentHandler::class);

        $resolvedDataCollection = new ResolvedDataCollection();
        $resolvedDataCollection->setBatchResult(new BatchProductSearchResult([]));
        $elementConfig = new ElementConfig();
        $elementConfig->set(ArticleSliderComponentHandler::SLIDER_TYPE_KEY, ArticleSliderComponentHandler::TYPE_STATIC_VARIANT);
        $elementConfig->set(ArticleSliderComponentHandler::SELECTED_VARIANTS, null);
        $element = new Element();
        $element->setId(PHP_INT_MAX);
        $element->setConfig($elementConfig);
        $shopContext = $this->createShopContext();

        $this->expectException(OutOfBoundsException::class);
        $articleSlideComponentHandler->handle(
            $resolvedDataCollection,
            $element,
            $shopContext
        );
    }
}
