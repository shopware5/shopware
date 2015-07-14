<?php

namespace Element\Responsive;

/**
 * Element: ArticleBox
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class ArticleBox extends \Element\Emotion\ArticleBox
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.product--box.box--basic'];

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return [
            'name' => 'div.product--info > a:product--title',
            'price' => 'div.product--price > .price--default'
        ];
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return [
            'compare'  => ['de' => 'Vergleichen',  'en' => 'Compare'],
            'remember' => ['de' => 'Merken',       'en' => 'Remember']
        ];
    }
}
