<?php

namespace Shopware\Tests\Service\Price;

use Shopware\Bundle\StoreFrontBundle\Service\CheapestPriceServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\Core\CheapestPriceService;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CheapestPriceImplementation implements CheapestPriceServiceInterface
{
    public function getList($products, ShopContextInterface $context)
    {
    }

    public function get(BaseProduct $product, ShopContextInterface $context)
    {
    }
}

class CheapestPriceBCTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CheapestPriceService
     */
    private $SUT;

    public function setUp()
    {
        $cheapestPriceGateway = $this->getMock('Shopware\Bundle\StoreFrontBundle\Gateway\CheapestPriceGatewayInterface');

        $this->SUT = new CheapestPriceService($cheapestPriceGateway);
    }

    public function testGetListThrowsExeptionOnInvalidContext()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            CheapestPriceService::contextError
        );

        $shopContext = $this->getMock('Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface');

        $this->SUT->getList([], $shopContext);
    }

    public function testGetThrowsExeptionOnInvalidContext()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            CheapestPriceService::contextError
        );

        $listProduct = $this->getMock('Shopware\Bundle\StoreFrontBundle\Struct\ListProduct');
        $shopContext = $this->getMock('Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface');

        $this->SUT->get($listProduct, $shopContext);
    }

    public function testGetThrowsExeptionOnInvalidProduct()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            CheapestPriceService::productError
        );

        $baseProduct = $this->getMock('Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct');
        $productContext = $this->getMock('Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface');

        $this->SUT->get($baseProduct, $productContext);
    }
}
