<?php

namespace Shopware\Tests\Mink\Element\Responsive;

/**
 * Element: Paging
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class Paging extends \Shopware\Tests\Mink\Element\Emotion\Paging
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.listing--paging'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'previous' => 'a.pagination--link.paging--prev',
            'next' => 'a.pagination--link.paging--next'
        ];
    }
}
