<?php

namespace Shopware\Themes\Responsive;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Form as Form;

class Theme extends \Shopware\Theme
{
    protected $extend = 'Bare';

    protected $name = 'Shopware responsive theme';

    protected $less = array();

    /**
     * @param Form\Container\TabContainer $container
     */
    public function createConfig(Form\Container\TabContainer $container)
    {
        $fieldSet = $this->createFieldSet('responsive_field_set', 'Responsive configuration');

        $fieldSet->addElement($this->createColorPickerField('bodyColor', 'Body color', 'red'));

        $tab = $this->createTab('responsive_tab', 'Responsive configuration');
        $tab->addElement($fieldSet);

        $container->addTab($tab);
    }

    /**
     * @param ArrayCollection $collection
     */
    public function createConfigSets(ArrayCollection $collection)
    {

    }
}