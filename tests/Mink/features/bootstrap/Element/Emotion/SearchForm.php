<?php

namespace Shopware\Tests\Mink\Element\Emotion;

use SensioLabs\Behat\PageObjectExtension\PageObject\Element;

/**
 * Element: SearchForm
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class SearchForm extends Element implements \Shopware\Tests\Mink\HelperSelectorInterface
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => '#searchcontainer'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'searchForm' => '#searchform',
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'searchButton' => ['de' => 'Suchen', 'en' => 'Search']
        ];
    }
}
