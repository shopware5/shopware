<?php

namespace Shopware\Tests\Mink\Element;

use Shopware\Tests\Mink\Helper;

/**
 * Element: ArticleBox
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class ArticleBox extends CartPosition
{
    /** @var array $selector */
    protected $selector = ['css' => 'div.product--box.box--basic'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'name' => 'div.product--info > a:product--title',
            'price' => 'div.product--price > .price--default'
        ];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'compare'  => ['de' => 'Vergleichen',  'en' => 'Compare'],
            'remember' => ['de' => 'Merken',       'en' => 'Remember']
        ];
    }

    /**
     * Returns the price
     * @return float
     */
    public function getPriceProperty()
    {
        $price = $this->getProperty('price');
        return Helper::floatValue($price);
    }
}
