<?php

namespace Element\Emotion;

/**
 * Element: BlogBox
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class BlogBox extends CartPosition
{
    /** @var array $selector */
    protected $selector = array('css' => 'div.blogbox');

    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return [];
    }

    /**
     * Returns an array of all named selectors of the element/page
     * @return array
     */
    public function getNamedSelectors()
    {
        return [
            'readMore'   => ['de' => 'Mehr lesen', 'en' => 'Read more']
        ];
    }
}
