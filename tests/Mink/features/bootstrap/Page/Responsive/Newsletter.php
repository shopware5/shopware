<?php
namespace Shopware\Tests\Mink\Page\Responsive;

class Newsletter extends \Shopware\Tests\Mink\Page\Emotion\Newsletter
{
    /**
     * @inheritdoc
     */
    public function getCssSelectors()
    {
        return [
            'newsletterForm' => 'div.newsletter--form > form'
        ];
    }
}
