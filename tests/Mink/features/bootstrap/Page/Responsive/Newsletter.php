<?php
namespace Page\Responsive;

class Newsletter extends \Page\Emotion\Newsletter
{
    public $cssLocator = array(
        'newsletterForm' => 'div.newsletter--form > form'
    );
}
