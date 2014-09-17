<?php

namespace Page\Responsive;

use Behat\Behat\Context\Step;

class Homepage extends \Page\Emotion\Homepage
{
    public $cssLocator = array(
        'contentBlock' => 'section.content-main > div.content-main--inner',
        'searchForm' => 'form.main-search--form',
        'newsletterForm' => 'form.newsletter--form',
        'newsletterFormSubmit' => 'form.newsletter--form button[type="submit"]',
        'controller' => array(
            'account' => 'body.is--ctl-account',
            'checkout' => 'body.is--ctl-checkout',
            'newsletter' => 'body.is--ctl-newsletter'
        )
    );

    protected $srcAttribute = 'data-image-src';
}
