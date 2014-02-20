<?php

namespace Shopware\Themes\Responsive;

class Theme extends \Shopware\Theme
{
    protected $extend = 'Bare';

    protected $name = 'Shopware responsive theme';

    protected $less = array('style.less', 'color.less');

    public function createConfig() { }
}