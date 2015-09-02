<?php
namespace Shopware\Tests\Mink\Page\Responsive;

class Newsletter extends \Shopware\Tests\Mink\Page\Emotion\Newsletter
{
    /**
     * Returns an array of all css selectors of the element/page
     * @return array
     */
    public function getCssSelectors()
    {
        return array(
            'newsletterForm' => 'div.newsletter--form > form'
        );
    }
}
