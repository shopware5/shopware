<?php
namespace Responsive;

class Newsletter extends \Emotion\Newsletter
{
    public $cssLocator = array(
        'newsletterForm' => 'div.newsletter--form > form'
    );
}
