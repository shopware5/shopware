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
        $fieldSet = $this->createFieldSet('bare_field_set', 'Bare configuration');

        $fieldSet->addElement($this->createColorPickerField('mainColor', 'Main color', 'blue'));

        $tab = $this->createTab('bare_tab', 'Bare configuration');
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