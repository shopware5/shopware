<?php

namespace Element\Responsive;

/**
 * Element: SearchForm
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class SearchForm extends \Element\Emotion\SearchForm
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'form.main-search--form'];

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return [
            'searchForm' => '.main-search--form',
        ];
    }
}