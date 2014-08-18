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
        'src/js/jquery.ie-fixes.js',
        'src/js/jquery.plugin-base.js',
		'src/js/jquery.state-manager.js',
		'src/js/jquery.storage-manager.js',
		'src/js/jquery.emotions.js',
		'src/js/jquery.off-canvas-menu.js',
		'src/js/jquery.search-field.js',
		'src/js/jquery.slide-panel.js',
		'src/js/jquery.tab-navigation.js',
		'src/js/jquery.image-slider.js',
		'src/js/jquery.image-zoom.js',
		'src/js/jquery.collapse-panel.js',
		'src/js/jquery.collapse-text.js',
		'src/js/jquery.auto-submit.js',
		'src/js/jquery.scroll.js',
		'src/js/jquery.product-slider.js',
		'src/js/jquery.register.js',
		'src/js/jquery.modal.js',
		'src/js/jquery.selectbox-replacement.js',
        'src/js/jquery.captcha.js',
        'src/js/jquery.drop-down-menu.js',
        'src/js/jquery.live-search.js',
        'src/js/jquery.loading-indicator.js',
        'src/js/jquery.overlay.js',
        'src/js/jquery.form-polyfill.js',
        'src/js/jquery.pseudo-text.js',
        'src/js/jquery.last-seen-products.js',
        'src/js/jquery.lightbox.js',
        'src/js/jquery.http-cache-filters.js',
	    'src/js/jquery.newsletter.js',
	    'src/js/jquery.menu-scroller.js',
        'src/js/jquery.shipping-payment.js',
        'src/js/jquery.add-article.js',
		'src/js/jquery.shopware-responsive.js'
	);

    /**
     * @param Form\Container\TabContainer $container
     */
    public function createConfig(Form\Container\TabContainer $container)
    {
        $container->addTab($this->createMainConfigTab());
        $container->addTab($this->createColorConfigTab());
    }

    /**
     * Helper function to create the main tab ("Responsive configuration")
     * @return Form\Container\Tab
     */
    private function createMainConfigTab()
    {
        $tab = $this->createTab('responsiveMain', '__responsive_tab_header__', array('attributes' => array('layout' => 'anchor', 'autoScroll' => true, 'padding' => '0', 'defaults' => array('anchor' => '100%'))));

        $fieldSet = $this->createFieldSet('responsiveGlobal', '__global_configuration__', array('attributes' => array('padding' => '10', 'margin'=> '5', 'layout' => 'anchor', 'defaults' => array('anchor' => '100%', 'labelWidth' => 150))));

        $fieldSet->addElement($this->createTextAreaField('additionalCssData', '__additional_css_data__', '', array('attributes' => array('xtype' => 'textarea'))));
        $fieldSet->addElement($this->createTextAreaField('additionalJsLibraries', '__additional_js_libraries__', '', array('attributes' => array('xtype' => 'textarea'))));
        $fieldSet->addElement($this->createTextField('bodyFontStack', 'bodyFontStack', '"Open Sans", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif'));
        $fieldSet->addElement($this->createTextField('headlineFontStack', 'headlineFontStack', '"Open Sans", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif'));
        $fieldSet->addElement($this->createTextField('subheadlineFontStack', 'subheadlineFontStack', '"Open Sans", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif'));

        $description = Shopware()->Snippets()->getNamespace('themes/responsive/backend/config')->get('desktop_responsive_description');
        $fieldSet->addElement($this->createCheckboxField('desktopResponsive', '__desktop_responsive__', true, array('attributes' => array('boxLabel' => $description))));

        $tab->addElement($fieldSet);

        return $tab;
    }

    /**
     * Helper function to create the color tab ("Responsive colors")
     * @return Form\Container\Tab
     */
    private function createColorConfigTab()
    {
        $colorTab = $this->createTab('responsiveColors', '__responsive_tab_colors__', array('attributes' => array('layout' => 'anchor', 'autoScroll' => true, 'padding' => '0', 'defaults' => array('anchor' => '100%'))));

        $fieldSet = $this->createFieldSet('responsiveColorsInnerBox', '', array('attributes' => array('padding' => '0', 'margin'=> '0', 'border' => 0, 'layout' => 'hbox')));

        $fieldSet->addElement($this->createLeftFieldSet());
        $fieldSet->addElement($this->createRightFieldSet());

        $colorTab->addElement($fieldSet);

        return $colorTab;
    }

    /**
     * Helper function to create the left column of the color tab (includes all main colors)
     * @return Form\Container\FieldSet
     */
    private function createLeftFieldSet()
    {
        $fieldSet = $this->createFieldSet('responsiveColorsLeft', '__main_colors__', array('attributes' => array('padding' => '10', 'margin'=> '5', 'flex' => 1, 'layout' => 'anchor', 'defaults' => array('labelWidth' => 200))));

        $fieldSet->addElement($this->createColorPickerField('primaryColor', 'primaryColor', '#e1540f'));
        $fieldSet->addElement($this->createColorPickerField('primaryContrastColor', 'primaryContrastColor', '#ad1200'));
        $fieldSet->addElement($this->createColorPickerField('secondaryColor', 'secondaryColor', '#d9400b'));

        $fieldSet->addElement($this->createColorPickerField('complementaryPrimaryColor', 'complementaryPrimaryColor', '#1ABC9C'));
        $fieldSet->addElement($this->createColorPickerField('complementarySecondaryColor', 'complementarySecondaryColor', '#006943'));

        $fieldSet->addElement($this->createColorPickerField('darkTextColor', 'darkTextColor', '#2b3742'));
        $fieldSet->addElement($this->createColorPickerField('primaryTextColor', 'primaryTextColor', '#5f7285'));
        $fieldSet->addElement($this->createColorPickerField('lightTextColor', 'lightTextColor', '#8594a5'));
        $fieldSet->addElement($this->createColorPickerField('softTextColor', 'softTextColor', '#a5b2bf'));
        $fieldSet->addElement($this->createColorPickerField('discountTextColor', 'discountTextColor', '#990000'));

        $fieldSet->addElement($this->createColorPickerField('borderColor', 'borderColor', '#d8dde5'));
        $fieldSet->addElement($this->createColorPickerField('lightDarkBorderColor', 'lightDarkBorderColor', '#C9D0DB'));
        $fieldSet->addElement($this->createColorPickerField('darkBorderColor', 'darkBorderColor', '#515b66'));
        $fieldSet->addElement($this->createColorPickerField('primaryBackgroundColor', 'primaryBackgroundColor', '#eceef1'));
        $fieldSet->addElement($this->createColorPickerField('softBackgroundColor', 'softBackgroundColor', '#f1f4f7'));
        $fieldSet->addElement($this->createColorPickerField('darkBackgroundColor', 'darkBackgroundColor', '#475c6a'));
        $fieldSet->addElement($this->createColorPickerField('primaryLightBackgroundColor', 'primaryLightBackgroundColor', '#f7f8fa'));

        $fieldSet->addElement($this->createColorPickerField('lightGradientStart', 'lightGradientStart', '#fff'));
        $fieldSet->addElement($this->createColorPickerField('lightGradientEnd', 'lightGradientEnd', '#f8f8fa'));
        $fieldSet->addElement($this->createColorPickerField('gradientContrastColor', 'gradientContrastColor', '#cc1d00'));

        $fieldSet->addElement($this->createColorPickerField('overlayBackground', 'overlayBackground', '#555555'));

        $fieldSet->addElement($this->createColorPickerField('reviewStarColor', 'reviewStarColor', '#ffcb00'));

        return $fieldSet;
    }

    /**
     * Helper function to create the right column of the color tab
     * @return Form\Container\FieldSet
     */
    private function createRightFieldSet()
    {
        $fieldSet = $this->createFieldSet('responsiveColorsRight', '', array('attributes' => array('padding' => '0', 'margin'=> '0', 'border' => 0, 'flex' => 1, 'layout' => 'anchor')));

        $fieldSet->addElement($this->createAlertColorsFieldSet());
        $fieldSet->addElement($this->createDeliveryColorsFieldSet());

        return $fieldSet;
    }

    /**
     * Helper function to create the alert colors fieldset for the right column of color tab
     * @return Form\Container\FieldSet
     */
    private function createAlertColorsFieldSet()
    {
        $fieldSet = $this->createFieldSet('alertColors', '__alert_colors__', array('attributes' => array('padding' => '10', 'margin'=> '5', 'layout' => 'anchor', 'defaults' => array('labelWidth' => 200))));

        $fieldSet->addElement($this->createColorPickerField('successText', 'successText', '#3c763d'));
        $fieldSet->addElement($this->createColorPickerField('successBackground', 'successBackground', '#dff0d8'));
        $fieldSet->addElement($this->createColorPickerField('successBorderColor', 'successBorderColor', '#d6e9c6'));

        $fieldSet->addElement($this->createColorPickerField('infoText', 'infoText', '#31708f'));
        $fieldSet->addElement($this->createColorPickerField('infoBackground', 'infoBackground', '#d9edf7'));
        $fieldSet->addElement($this->createColorPickerField('infoBorderColor', 'infoBorderColor', '#bce8f1'));

        $fieldSet->addElement($this->createColorPickerField('warningText', 'warningText', '#8a6d3b'));
        $fieldSet->addElement($this->createColorPickerField('warningBackground', 'warningBackground', '#fcf8e3'));

        $fieldSet->addElement($this->createColorPickerField('errorText', 'errorText', '#a94442'));
        $fieldSet->addElement($this->createColorPickerField('errorBackground', 'errorBackground', '#f2dede'));
        $fieldSet->addElement($this->createColorPickerField('errorBorderColor', 'errorBorderColor', '#ebccd1'));

        return $fieldSet;
    }

    /**
     * Helper function to create the delivery colors fieldset for the right column of color tab
     * @return Form\Container\FieldSet
     */
    private function createDeliveryColorsFieldSet()
    {
        $fieldSet = $this->createFieldSet('delivery', '__delivery_header__', array('attributes' => array('padding' => '10', 'margin'=> '5', 'layout' => 'anchor', 'defaults' => array('labelWidth' => 200))));

        $fieldSet->addElement($this->createColorPickerField('deliveryInfoText', 'deliveryInfoText', '#ffc000'));

        $fieldSet->addElement($this->createColorPickerField('deliveryAvailableIcon', 'deliveryAvailableIcon', '#62d100'));
        $fieldSet->addElement($this->createColorPickerField('deliveryAvailableText', 'deliveryAvailableText', '#449101'));

        $fieldSet->addElement($this->createColorPickerField('deliveryMoreIsComingIcon', 'deliveryMoreIsComingIcon', '#f0ad4e'));
        $fieldSet->addElement($this->createColorPickerField('deliveryMoreIsComingText', 'deliveryMoreIsComingText', '#8a6d3b'));

        $fieldSet->addElement($this->createColorPickerField('deliveryNotAvailableIcon', 'deliveryNotAvailableIcon', '#f9390a'));
        $fieldSet->addElement($this->createColorPickerField('deliveryNotAvailableText', 'deliveryNotAvailableText', '#b1001d'));

        return $fieldSet;
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
