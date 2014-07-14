<?php

namespace Shopware\Themes\TestResponsive;

use Shopware\Components\Theme\ConfigSet;

class Theme extends \Shopware\Components\Theme
{
    protected $extend = 'Bare';

    protected $inheritanceConfig = true;

    protected $javascript = array('responsive_1.js', 'responsive_2.js');

    protected $css = array('responsive_1.css', 'responsive_2.css');

    public function createConfig(\Shopware\Components\Form\Container\TabContainer $container)
    {
        $container->addTab(new \Shopware\Components\Form\Container\Tab('responsive', 'responsive'));
    }

    public function createConfigSets(\Doctrine\Common\Collections\ArrayCollection $collection)
    {
        $collection->add(new ConfigSet('set1', array('value1' => 1)));
        $collection->add(new ConfigSet('set2', array('value1' => 2)));
    }
}