<?php

namespace Shopware\Tests\Mink\Element;

/**
 * Element: BlogArticleBox
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class BlogArticleBox extends ArticleBox
{
    /**
     * @var array $selector
     */
    protected $selector = ['css' => 'div.blog--crossselling div.product-slider--item'];
}
