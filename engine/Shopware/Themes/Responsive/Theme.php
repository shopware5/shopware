<?php

namespace Shopware\Themes\Responsive;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Form as Form;

class Theme extends \Shopware\Components\Theme
{
    protected $extend = 'Bare';

    /**
     * Defines the human readable theme name
     * which displayed in the backend
     * @var string
     */
    protected $name = '__theme_name__';

    /**
     * Allows to define a description text
     * for the theme
     * @var null
     */
    protected $description = '__theme_description__';

    /**
     * Name of the theme author.
     * @var null
     */
    protected $author = '__author__';

    /**
     * License of the theme source code.
     *
     * @var null
     */
    protected $license = '__license__';

    /**
     * @param Form\Container\TabContainer $container
     */
    public function createConfig(Form\Container\TabContainer $container)
    {
        $tab = $this->createTab('bare_tab_1', '__bare_tab_1__');
        $fieldSet = $this->createFieldSet('bare_field_set_1', '__bare_field_set_1__', array(
            'attributes' => array(
                'layout' => 'column',
                'defaults' => array('columnWidth' => 0.5, 'margin' => '5 10')
            )
        ));

        $text = $this->createTextField('bare_text', '__bare_text__', 'Hallo');
        $check = $this->createCheckboxField('bare_checkbox', '__bare_checkbox__', true);
        $date = $this->createDateField('bare_date', '__bare_date__', '2012-01-01');
        $em = $this->createEmField('bare_em', '__bare_em__', '100em');
        $media = $this->createMediaField('bare_media', '__bare_media__', 'media/image/deli_teaser503886c2336e3.jpg');
        $number = $this->createNumberField('bare_number', '__bare_number__', 100.10);
        $percent = $this->createPercentField('bare_percent', '__bare_percent__', '100%');
        $pixel = $this->createPixelField('bare_pixel', '__bare_pixel__', '100px');
        $color = $this->createColorPickerField('bare_color', '__bare_color__', '#fff');
        $textarea = $this->createTextAreaField('bare_text_area', '__bare_text_area__', 'TEST123');
        $select = $this->createSelectField('bare_select', '__bare_select__', '__arial__', array(
            '__arial__', '__courier_new__'
        ));

        $tab->addElement($fieldSet);
        $fieldSet->addElement($text)
            ->addElement($check)
            ->addElement($date)
            ->addElement($em)
            ->addElement($number)
            ->addElement($percent)
            ->addElement($pixel)
            ->addElement($color)
            ->addElement($select)
            ->addElement($textarea)
            ->addElement($media);

        $container->addTab($tab);
    }

    /**
     * @param ArrayCollection $collection
     */
    public function createConfigSets(ArrayCollection $collection)
    {
        $collection->add(array(
            'name' => '__blue_scheme_name__',
            'description' => '__blue_scheme_description__',
            'values' => array(
                'bare_text' => '__blue_bare_text__',
                'bare_select' => '__blue_bare_select__'
            )
        ));

        $collection->add(array(
            'name' => '__white_scheme_name__',
            'description' => '__white_scheme_description__',
            'values' => array(
                'bare_text' => '__white_bare_text__',
                'bare_select' => '__white_bare_select__'
            )
        ));

    }
}