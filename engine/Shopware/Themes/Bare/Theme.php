<?php

namespace Shopware\Themes\Bare;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Form as Form;

class Theme extends \Shopware\Theme
{
    protected $extend = null;

    protected $name = 'Shopware bare theme';

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