<?php

namespace Element\Responsive;

/**
 * Element: BlogBox
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class BlogBox extends \Element\Emotion\BlogBox
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.blog--box.panel'];
}
