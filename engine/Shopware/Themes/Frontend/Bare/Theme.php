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
     * @param Form\Container\TabContainer $container
     */
    public function createConfig(Form\Container\TabContainer $container)
    {
        $tab = $this->createTab('bareMain', '__bare_tab_header__', array('attributes' => array('layout' => 'anchor', 'autoScroll' => true, 'padding' => '0')));

        $fieldSet = $this->createFieldSet('bareGlobal', '__global_configuration__', array('attributes' => array('padding' => '10', 'margin'=> '5', 'layout' => 'anchor', 'defaults' => array('anchor' => '100%'))));
        $fieldSet->addElement($this->createCheckboxField('offcanvasCart', '__offcanvas_cart__', true, $this->getLabelAttribute('offcanvas_cart_description')));
        $fieldSet->addElement($this->createCheckboxField('focusSearch', '__focus_search__', false, $this->getLabelAttribute('focus_search_description')));
        $fieldSet->addElement($this->createCheckboxField('displaySidebar', '__display_sidebar__', true, $this->getLabelAttribute('display_sidebar_description')));
        $fieldSet->addElement($this->createCheckboxField('checkoutHeader', '__checkout_header__', true, $this->getLabelAttribute('checkout_header_description')));
        $fieldSet->addElement($this->createCheckboxField('infiniteScrolling', '__enable_infinite_scrolling__', true, $this->getLabelAttribute('enable_infinite_scrolling_description')));
        $fieldSet->addElement($this->createNumberField('infiniteThreshold', '__infinite_threshold__', 4, $this->getLabelAttribute('infinite_threshold_description')));
        $fieldSet->addElement($this->createSelectField('lightboxZoomFactor', '__lightbox_zoom_factor__', 0, array(
            array('value' => 0, 'text' => 'auto'),
            array('value' => 1, 'text' => 'no zoom'),
            array('value' => 2, 'text' => 'x2'),
            array('value' => 3, 'text' => 'x3'),
            array('value' => 5, 'text' => 'x5')
        ), $this->getLabelAttribute('lightbox_zoom_factor_description')));
        $tab->addElement($fieldSet);

        $fieldSet = $this->createFieldSet('bareLogos', '__logos__', array('attributes' => array('padding' => '10', 'margin'=> '5', 'layout' => 'anchor', 'defaults' => array('anchor' => '100%'))));
        $fieldSet->addElement($this->createMediaField('mobileLogo', '__smartphone__', 'frontend/_public/src/img/logos/logo--mobile.png'));
        $fieldSet->addElement($this->createMediaField('tabletLogo', '__tablet__', 'frontend/_public/src/img/logos/logo--tablet.png'));
        $fieldSet->addElement($this->createMediaField('tabletLandscapeLogo', '__tablet_landscape__', 'frontend/_public/src/img/logos/logo--tablet.png'));
        $fieldSet->addElement($this->createMediaField('desktopLogo', '__desktop__', 'frontend/_public/src/img/logos/logo--tablet.png'));
        $tab->addElement($fieldSet);

        $fieldSet = $this->createFieldSet('Icons', '__icons__', array('attributes' => array('padding' => '10', 'margin'=> '5', 'layout' => 'anchor', 'defaults' => array('labelWidth' => 280, 'anchor' => '100%'))));
        $fieldSet->addElement($this->createMediaField('appleTouchIcon', '__apple_touch_icon__', 'frontend/_public/src/img/apple-touch-icon-precomposed.png'));
        $fieldSet->addElement($this->createMediaField('win8TileImage', '__win8_tile_image__', 'frontend/_public/src/img/win-tile-image.png'));
        $fieldSet->addElement($this->createMediaField('favicon', '__favicon__', 'frontend/_public/src/img/favicon.ico'));
        $tab->addElement($fieldSet);

        $container->addTab($tab);
    }

    /**
     * Helper function to get the attribute of a checkbox field which shows a description label
     * @param $snippetName
     * @return array
     */
    private function getLabelAttribute($snippetName)
    {
        $description = Shopware()->Snippets()->getNamespace('themes/bare/backend/config')->get($snippetName);
        return array('attributes' => array('boxLabel' => $description));
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
