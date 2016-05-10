<?php

namespace Shopware\Tests\Mink\Element;

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
    protected $selector = ['css' => 'div.blog--box.panel'];

    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getNamedSelectors()
    {
        return [
            'readMore'   => ['de' => 'Mehr lesen', 'en' => 'Read more']
        ];
    }
}
