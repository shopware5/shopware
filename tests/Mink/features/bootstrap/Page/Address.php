<?php
namespace Shopware\Tests\Mink\Page;

use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

class Address extends Account implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/address';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'addressForm' => 'div.account--address-form form'
        ];
    }
}
