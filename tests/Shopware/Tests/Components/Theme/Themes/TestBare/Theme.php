<?php

namespace Shopware\Themes\TestBare;

class Theme extends \Shopware\Components\Theme
{
    protected $extends = null;

    protected $javascript = array('bare_1.js', 'bare_2.js');

    protected $css = array('bare_1.css', 'bare_2.css');
    
    public function createConfig(\Shopware\Components\Form\Container\TabContainer $container)
    {
        $container->addTab(new \Shopware\Components\Form\Container\Tab('bare', 'bare'));
    }
}