<?php
namespace Shopware\Tests\Mink\Page;

use Shopware\Tests\Mink\Helper;
use Shopware\Tests\Mink\HelperSelectorInterface;

class AddressDelete extends Account implements HelperSelectorInterface
{
    /**
     * @var string $path
     */
    protected $path = '/address/delete';

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'panelTitle' => 'h1.panel--title'
        ];
    }

    public function getNamedSelectors()
    {
        return [
            'confirmDeleteButton' => ['de' => 'Bestätigen', 'en' => 'Confirm']
        ];
    }
}
