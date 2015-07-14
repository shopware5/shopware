<?php

namespace Element\Responsive;

/**
 * Element: SitemapGroup
 * Location: Billing address box on account dashboard
 *
 * Available retrievable properties:
 * - address (Element[], please use Account::checkAddress())
 */
class SitemapGroup extends \Element\Emotion\SitemapGroup
{
    /** @var array $selector */
    protected $selector = ['css' => '.sitemap--navigation-head'];
}
