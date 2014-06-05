<?php

namespace Shopware\Themes\Bare;

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Form as Form;
use Shopware\Components\Theme\ConfigSet;

class Theme extends \Shopware\Components\Theme
{
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
		'vendors/jquery/dist/jquery.min.js',
		'vendors/picturefill/picturefill.js',
		'vendors/jquery.transit/jquery.transit.js',
		'vendors/jquery.event.move/js/jquery.event.move.js',
		'vendors/jquery.event.swipe/js/jquery.event.swipe.js',
		'vendors/glidejs/dist/jquery.glide.min.js',

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
		'src/js/jquery.shopware-responsive.js'
	);

    /**
     * @param Form\Container\TabContainer $container
     */
    public function createConfig(Form\Container\TabContainer $container)
    {
        $tab = $this->createTab('tab1', 'Basis Konfiguration');
        $tab->addElement($this->createCheckboxField('debugModeEnabled', 'Debug-Modus', 0, array('attributes' => array('boxLabel' => 'Zeigt ein Resize-Panel an, um die Fenster-Größe und die aktuelle Geräteklasse zu terminieren.'))));
        $container->addTab($tab);
    }

    /**
     * @param ArrayCollection $collection
     */
    public function createConfigSets(ArrayCollection $collection)
    {


        $set = new ConfigSet();
        $set->setName('Minimale Darstellung')
            ->setDescription('Deaktiviert alle nicht notwendigen Features des Bare Themes. Dadurch werden Sitebar Element und Slider deaktiviert')
            ->setValues(array('color' => '#fff'));
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('Maximale Darstellung')
            ->setDescription('Aktiviert alle zusätzlichen Features des Bare Themes. Slider, Einkaufswelten und Sitebars werden in der Storefront darstellt')
            ->setValues(array('color' => '#fff'));

        $collection->add($set);

    }
}
