<?php

namespace Shopware\Tests\Mink\Element\Responsive;

/**
 * Element: SearchForm
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class SearchForm extends \Shopware\Tests\Mink\Element\Emotion\SearchForm
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'form.main-search--form'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'searchForm' => '.main-search--form',
        ];
    }
}
