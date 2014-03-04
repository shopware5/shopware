<?php

namespace Shopware\Themes\Responsive;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Form as Form;

class Theme extends \Shopware\Theme
{
    protected $extend = 'Bare';

    protected $name = 'Shopware responsive theme';

    /**
     * @param Form\Container\TabContainer $container
     */
    public function createConfig(Form\Container\TabContainer $container)
    {

    }

    /**
     * @param ArrayCollection $collection
     */
    public function createConfigSets(ArrayCollection $collection)
    {

    }
}