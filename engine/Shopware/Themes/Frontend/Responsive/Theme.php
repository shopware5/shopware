<?php

namespace Shopware\Themes\Responsive;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Form as Form;
use Shopware\Components\Theme\ConfigSet;

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
	 * Javascript files which will be used in the theme
	 *
	 * @var array
	 */
	protected $javascript = array(

        // Third party plugins / libraries
		'vendors/jquery/dist/jquery.min.js',
		'vendors/picturefill/picturefill.js',
		'vendors/jquery.transit/jquery.transit.js',
		'vendors/jquery.event.move/js/jquery.event.move.js',
		'vendors/jquery.event.swipe/js/jquery.event.swipe.js',
		'vendors/glidejs/dist/jquery.glide.min.js',

        // Shopware specific plugins
        'src/js/jquery.plugin-base.js',
		'src/js/jquery.state-manager.js',
		'src/js/jquery.emotions.js',
		'src/js/jquery.off-canvas-menu.js',
		'src/js/jquery.search-field.js',
		'src/js/jquery.slide-panel.js',
		'src/js/jquery.tab-navigation.js',
		'src/js/jquery.image-slider.js',
		'src/js/jquery.quantity-field.js',
		'src/js/jquery.collapse-panel.js',
		'src/js/jquery.collapse-text.js',
		'src/js/jquery.auto-submit.js',
		'src/js/jquery.scroll.js',
		'src/js/jquery.product-slider.js',
		'src/js/jquery.register.js',
        'src/js/jquery.modal.js',
        'src/js/jquery.clicker.js',
		'src/js/jquery.shopware-responsive.js'
	);

    /**
     * @param Form\Container\TabContainer $container
     */
    public function createConfig(Form\Container\TabContainer $container)
    {
        $columnWidth = array('columnWidth' => 0.5);
        $tab = $this->createTab('tab1', 'Responsive Konfiguration');

        $fieldSet = $this->createFieldSet('field1', 'Startseiten Konfiguration', array('attributes' => array('layout' => 'anchor', 'defaults' => array('anchor' => '100%'))));

        $fieldSet->addElement($this->createTextField('text1', 'Überschrift', 'Willkommen'));
        $fieldSet->addElement($this->createColorPickerField('color1', 'Schriftfarbe', '#000'));
        $fieldSet->addElement($this->createSelectField('textType', 'Schriftart', 'Arial', array(
            'Courier New',
            'Arial'
        )));
        $fieldSet->addElement($this->createTextAreaField('text2', 'Begrüßungs-Text', ''));

        $secondFieldSet = $this->createFieldSet('field2', 'Logo Konfiguration', array('attributes' => array('margin'=> '15 0', 'layout' => 'column', 'defaults' => array('margin' => '0 10'))));
        $secondFieldSet->addElement($this->createPixelField('logoMarginTop', 'Abstand unten', '10px', array('attributes' => $columnWidth)));

        $secondFieldSet->addElement($this->createEmField('logoMarginBottom', 'Abstand oben', '10em', array('attributes' => $columnWidth)));
        $secondFieldSet->addElement($this->createMediaField('logo', 'Logo', 'media/image/logo.jpg', array('attributes' => array('columnWidth' => 1.0, 'margin' => '10'))));
        $fieldSet->addElement($secondFieldSet);

        $tab->addElement($fieldSet);
        $container->addTab($tab);

    }

    /**
     * @param ArrayCollection $collection
     */
    public function createConfigSets(ArrayCollection $collection)
    {
        $set = new ConfigSet();
        $set->setName('Grünes Farbschema')
            ->setDescription('Konfiguriert die Farben des Responsive Themes mit unterschiedlichen Grün-Tönen, die von Shopware aufeinander abgestimmt sind')
            ->setValues(array('color' => '#fff'));
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('Blaues Farbschema')
            ->setDescription('Konfiguriert die Farben des Responsive Themes mit unterschiedlichen Blau-Tönen, die von Shopware aufeinander abgestimmt sind')
            ->setValues(array('color' => '#fff'));
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('Rotes Farbschema')
            ->setDescription('Konfiguriert die Farben des Responsive Themes mit unterschiedlichen Rot-Tönen, die von Shopware aufeinander abgestimmt sind')
            ->setValues(array('color' => '#fff'));

        $collection->add($set);

    }
}