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
        'vendors/js/jquery/jquery.min.js',
        'vendors/js/picturefill/picturefill.js',
        'vendors/js/jquery.transit/jquery.transit.js',
        'vendors/js/jquery.event.move/jquery.event.move.js',
        'vendors/js/jquery.event.swipe/jquery.event.swipe.js',
        'vendors/js/masonry/masonry.pkgd.min.js',

        // Shopware specific plugins
        'src/js/jquery.ie-fixes.js',
        'src/js/jquery.plugin-base.js',
        'src/js/jquery.state-manager.js',
        'src/js/jquery.storage-manager.js',
        'src/js/jquery.off-canvas-menu.js',
        'src/js/jquery.search-field.js',
        'src/js/jquery.tab-menu.js',
        'src/js/jquery.image-slider.js',
        'src/js/jquery.image-zoom.js',
        'src/js/jquery.collapse-panel.js',
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
        'src/js/jquery.ajax-product-navigation.js',
        'src/js/jquery.newsletter.js',
        'src/js/jquery.menu-scroller.js',
        'src/js/jquery.shipping-payment.js',
        'src/js/jquery.add-article.js',
        'src/js/jquery.range-slider.js',
        'src/js/jquery.filter-component.js',
        'src/js/jquery.listing-actions.js',
        'src/js/jquery.collapse-cart.js',
        'src/js/jquery.emotion.js',
        'src/js/jquery.product-compare-add.js',
        'src/js/jquery.product-compare-menu.js',
        'src/js/jquery.infinite-scrolling.js',
        'src/js/jquery.off-canvas-button.js',
        'src/js/jquery.subcategory-nav.js',
        'src/js/jquery.ajax-wishlist.js',
        'src/js/jquery.preloader-button.js',
        'src/js/jquery.image-gallery.js',
        'src/js/jquery.offcanvas-html-panel.js',
        'src/js/jquery.shopware-responsive.js'
    );

    private $fieldSetDefaults = array(
        'layout' => 'column',
        'height' => 170,
        'flex' => 0,
        'defaults' => array('columnWidth' => 0.5, 'labelWidth' => 180, 'margin' => '3 16 3 0')
    );

    private $themeColorDefaults = array(
        "_brand-primary" => "#D9400B",
        "_brand-primary-light" => "saturate(lighten(@brand-primary,12%), 5%)",
        "_brand-secondary" => "#5F7285",
        "_brand-secondary-dark" => "darken(@brand-secondary, 15%)",
        "_gray" => "#F5F5F8",
        "_gray-light" => "lighten(@gray, 1%)",
        "_gray-dark" => "darken(@gray-light, 10%)",
        "_border-color" => "@gray-dark",
        "_highlight-success" => "#2ECC71",
        "_highlight-error" => "#E74C3C",
        "_highlight-notice" => "#F1C40F",
        "_highlight-info" => "#4AA3DF",
        "_body-bg" => "darken(@gray-light, 5%)",
        "_overlay-bg" => "#000000",
        "_overlay-opacity" => "0.7",
        "_text-color" => "@brand-secondary",
        "_text-color-dark" => "@brand-secondary-dark",
        "_link-color" => "@brand-primary",
        "_link-hover-color" => "darken(@link-color, 10%)",
        "_rating-star-color" => "@highlight-notice",
        "_btn-default-top-bg" => "#FFFFFF",
        "_btn-default-bottom-bg" => "@gray-light",
        "_btn-default-hover-bg" => "#FFFFFF",
        "_btn-default-text-color" => "@text-color",
        "_btn-default-hover-text-color" => "@brand-primary",
        "_btn-default-border-color" => "@border-color",
        "_btn-default-hover-border-color" => "@brand-primary",
        "_btn-primary-top-bg" => "@brand-primary-light",
        "_btn-primary-bottom-bg" => "@brand-primary",
        "_btn-primary-hover-bg" => "@brand-primary",
        "_btn-primary-text-color" => "#FFFFFF",
        "_btn-primary-hover-text-color" => "@btn-primary-text-color",
        "_btn-secondary-top-bg" => "@brand-secondary",
        "_btn-secondary-bottom-bg" => "@brand-secondary-dark",
        "_btn-secondary-hover-bg" => "@brand-secondary-dark",
        "_btn-secondary-text-color" => "#FFFFFF",
        "_btn-secondary-hover-text-color" => "@btn-secondary-text-color",
        "_panel-header-bg" => "@gray-light",
        "_panel-header-color" => "@text-color",
        "_panel-border" => "@border-color",
        "_panel-bg" => "#FFFFFF",
        "_label-color" => "@text-color",
        "_input-bg" => "@gray-light",
        "_input-color" => "@brand-secondary",
        "_input-placeholder-color" => "lighten(@text-color, 15%)",
        "_input-border" => "@border-color",
        "_input-focus-bg" => "#FFFFFF",
        "_input-focus-border" => "@brand-primary",
        "_input-focus-color" => "@brand-secondary",
        "_input-error-bg" => "desaturate(lighten(@highlight-error, 38%), 20%)",
        "_input-error-border" => "@highlight-error",
        "_input-error-color" => "@highlight-error",
        "_input-success-bg" => "#FFFFFF",
        "_input-success-border" => "@highlight-success",
        "_input-success-color" => "@brand-secondary-dark",
        "_panel-table-header-bg" => "@panel-bg",
        "_panel-table-header-color" => "@text-color-dark",
        "_table-row-bg" => "#FFFFFF",
        "_table-row-color" => "@brand-secondary",
        "_table-row-highlight-bg" => "darken(@table-row-bg, 4%)",
        "_table-header-bg" => "@brand-secondary",
        "_table-header-color" => "#FFFFFF",
        "_badge-discount-bg" => "@highlight-error",
        "_badge-discount-color" => "#FFFFFF",
        "_badge-newcomer-bg" => "@highlight-notice",
        "_badge-newcomer-color" => "#FFFFFF",
        "_badge-recommendation-bg" => "@highlight-success",
        "_badge-recommendation-color" => "#FFFFFF",
        "_badge-download-bg" => "@highlight-info",
        "_badge-download-color" => "#FFFFFF"
    );

    /**
     * @param Form\Container\TabContainer $container
     */
    public function createConfig(Form\Container\TabContainer $container)
    {

        $container->addTab($this->createMainConfigTab());

        $tab = $this->createTab(
            'responsive_tab',
            '__responsive_colors__'
        );
        $container->addTab($tab);

        $tab->addElement($this->createBottomTabPanel());
    }

    private function createBasicFieldSet()
    {
        $attributes = array_merge($this->fieldSetDefaults, array('height' => 130));
        $fieldSet = $this->createFieldSet(
            'basic_field_set',
            '__responsive_tab_general_fieldset_base__',
            array('attributes' => $attributes)
        );

        $fieldSet->addElement(
            $this->createColorPickerField(
                '_brand-primary',
                '@brand-primary',
                '#D9400B'
            )
        );
        $fieldSet->addElement(
            $this->createColorPickerField(
                '_brand-primary-light',
                '@brand-primary-light',
                'saturate(lighten(@brand-primary, 12%), 5%)'
            )
        );
        $fieldSet->addElement(
            $this->createColorPickerField(
                '_brand-secondary',
                '@brand-secondary',
                '#5F7285'
            )
        );
        $fieldSet->addElement(
            $this->createColorPickerField(
                '_brand-secondary-dark',
                '@brand-secondary-dark',
                'darken(@brand-secondary, 15%)'
            )
        );

        return $fieldSet;
    }

    /**
     * Helper function to create the child-tabs of ("Responsive colors")
     * @return Form\Container\Tab
     */
    private function createBottomTabPanel()
    {
        $tabPanel = $this->createTabPanel(
            'bottom_tab_panel',
            array(
                'attributes' => array(
                    'plain' => true
                )
            )
        );

        $tabPanel->addTab($this->createGeneralTab());
        $tabPanel->addTab($this->createTypographyTab());
        $tabPanel->addTab($this->createButtonsTab());
        $tabPanel->addTab($this->createFormsTab());
        $tabPanel->addTab($this->createTablesTab());

        return $tabPanel;
    }

    /**
     * Helper function to create the tab ("General")
     * @return Form\Container\Tab
     */
    private function createGeneralTab()
    {
        $tab = $this->createTab(
            'general_tab',
            '__responsive_tab_general__',
            array(
                'attributes' => array(
                    'autoScroll' => true
                )
            )
        );

        $attributes = array_merge($this->fieldSetDefaults, array('height' => 130));
        $fieldSetGrey = $this->createFieldSet(
            'grey_tones',
            '__responsive_tab_general_fieldset_grey__',
            array('attributes' => $attributes)
        );

        $fieldSetGrey->addElement(
            $this->createColorPickerField(
                '_gray',
                '@gray',
                '#F5F5F8'
            )
        );
        $fieldSetGrey->addElement(
            $this->createColorPickerField(
                '_gray-light',
                '@gray-light',
                'lighten(@gray, 1%)'
            )
        );
        $fieldSetGrey->addElement(
            $this->createColorPickerField(
                '_gray-dark',
                '@gray-dark',
                'darken(@gray-light, 10%)'
            )
        );
        $fieldSetGrey->addElement(
            $this->createColorPickerField(
                '_border-color',
                '@border-color',
                '@gray-dark'
            )
        );

        $basicFieldSet = $this->createBasicFieldSet();
        $tab->addElement($basicFieldSet);
        $tab->addElement($fieldSetGrey);

        $attributes = array_merge($this->fieldSetDefaults, array('height' => 130));
        $fieldSetHighlights = $this->createFieldSet(
            'highlight_colors',
            '__responsive_tab_general_fieldset_highlight__',
            array('attributes' => $attributes)
        );

        $fieldSetHighlights->addElement(
            $this->createColorPickerField(
                '_highlight-success',
                '@highlight-success',
                '#2ECC71'
            )
        );
        $fieldSetHighlights->addElement(
            $this->createColorPickerField(
                '_highlight-error',
                '@highlight-error',
                '#E74C3C'
            )
        );
        $fieldSetHighlights->addElement(
            $this->createColorPickerField(
                '_highlight-notice',
                '@highlight-notice',
                '#F1C40F'
            )
        );
        $fieldSetHighlights->addElement(
            $this->createColorPickerField(
                '_highlight-info',
                '@highlight-info',
                '#4AA3DF'
            )
        );

        $tab->addElement($fieldSetHighlights);

        $attributes = array_merge($this->fieldSetDefaults, array('height' => 200));
        $fieldSetScaffolding = $this->createFieldSet(
            'scaffolding',
            '__responsive_tab_general_fieldset_scaffolding__',
            array('attributes' => $attributes)
        );

        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                '_body-bg',
                '@body-bg',
                'darken(@gray-light, 5%)'
            )
        );
        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                '_text-color',
                '@text-color',
                '@brand-secondary'
            )
        );
        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                '_text-color-dark',
                '@text-color-dark',
                '@brand-secondary-dark'
            )
        );
        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                '_link-color',
                '@link-color',
                '@brand-primary'
            )
        );
        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                '_link-hover-color',
                '@link-hover-color',
                'darken(@link-color, 10%)'
            )
        );
        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                '_rating-star-color',
                '@rating-star-color',
                '@highlight-notice'
            )
        );
        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                '_overlay-bg',
                '@overlay-bg',
                '#000000'
            )
        );
        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                '_overlay-opacity',
                '@overlay-opacity',
                '0.7'
            )
        );

        $tab->addElement($fieldSetScaffolding);

        return $tab;
    }

    /**
     * Helper function to create the tab ("Typography")
     * @return Form\Container\Tab
     */
    private function createTypographyTab()
    {
        $tab = $this->createTab(
            'typo_tab',
            '__responsive_tab_typo__',
            array('attributes' => array('autoScroll' => true))
        );

        $attributes = array_merge($this->fieldSetDefaults, array('height' => 170));
        $fieldSetBasic = $this->createFieldSet(
            'typo_base',
            '__responsive_tab_typo_fieldset_base__',
            array('attributes' => $attributes)
        );

        $fieldSetBasic->addElement(
            $this->createTextField(
                '_font-base-stack',
                '@font-base-stack',
                '"Open Sans", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;'
            )
        );
        $fieldSetBasic->addElement(
            $this->createTextField(
                '_font-headline-stack',
                '@font-headline-stack',
                '@font-base-stack'
            )
        );
        $fieldSetBasic->addElement(
            $this->createTextField(
                '_font-size-base',
                '@font-size-base',
                '14'
            )
        );
        $fieldSetBasic->addElement(
            $this->createTextField(
                '_font-base-weight',
                '@font-base-weight',
                '500'
            )
        );
        $fieldSetBasic->addElement(
            $this->createTextField(
                '_font-light-weight',
                '@font-light-weight',
                '300'
            )
        );
        $fieldSetBasic->addElement(
            $this->createTextField(
                '_font-bold-weight',
                '@font-bold-weight',
                '700'
            )
        );

        $tab->addElement($fieldSetBasic);

        $attributes = array_merge($this->fieldSetDefaults, array('height' => 170));
        $fieldSetHeadlines = $this->createFieldSet(
            'typo_headlines',
            '__responsive_tab_typo_fieldset_headlines__',
            array('attributes' => $attributes)
        );

        $fieldSetHeadlines->addElement(
            $this->createTextField(
                '_font-size-h1',
                '@font-size-h1',
                '26'
            )
        );
        $fieldSetHeadlines->addElement(
            $this->createTextField(
                '_font-size-h2',
                '@font-size-h2',
                '21'
            )
        );
        $fieldSetHeadlines->addElement(
            $this->createTextField(
                '_font-size-h3',
                '@font-size-h3',
                '18'
            )
        );
        $fieldSetHeadlines->addElement(
            $this->createTextField(
                '_font-size-h4',
                '@font-size-h4',
                '16'
            )
        );
        $fieldSetHeadlines->addElement(
            $this->createTextField(
                '_font-size-h5',
                '@font-size-h5',
                '@font-size-base'
            )
        );
        $fieldSetHeadlines->addElement(
            $this->createTextField(
                '_font-size-h6',
                '@font-size-h6',
                '12'
            )
        );

        $tab->addElement($fieldSetHeadlines);

        return $tab;
    }

    /**
     * Helper function to create the tab ("Buttons & Panels")
     * @return Form\Container\Tab
     */
    private function createButtonsTab()
    {
        $tab = $this->createTab(
            'buttons_tab',
            '__responsive_tab_buttons__',
            array(
                'attributes' => array(
                    'autoScroll' => true
                )
            )
        );

        $attributes = array_merge($this->fieldSetDefaults, array('height' => 90));
        $fieldSetButtons = $this->createFieldSet(
            'buttons_fieldset',
            '__responsive_tab_buttons_fieldset_global__',
            array('attributes' => $attributes)
        );

        $fieldSetButtons->addElement(
            $this->createTextField(
                '_btn-font-size',
                '@btn-font-size',
                '14'
            )
        );
        $fieldSetButtons->addElement(
            $this->createTextField(
                '_btn-icon-size',
                '@btn-icon-size',
                '10'
            )
        );

        $tab->addElement($fieldSetButtons);

        $attributes = array_merge($this->fieldSetDefaults, array('height' => 200));
        $fieldSetDefaultButtons = $this->createFieldSet(
            'buttons_default_fieldset',
            '__responsive_tab_buttons_fieldset_default__',
            array('attributes' => $attributes)
        );

        $fieldSetDefaultButtons->addElement(
            $this->createColorPickerField(
                '_btn-default-top-bg',
                '@btn-default-top-bg',
                '#FFFFFF'
            )
        );
        $fieldSetDefaultButtons->addElement(
            $this->createColorPickerField(
                '_btn-default-bottom-bg',
                '@btn-default-bottom-bg',
                '@gray-light'
            )
        );
        $fieldSetDefaultButtons->addElement(
            $this->createColorPickerField(
                '_btn-default-hover-bg',
                '@btn-default-hover-bg',
                '#FFFFFF'
            )
        );
        $fieldSetDefaultButtons->addElement(
            $this->createColorPickerField(
                '_btn-default-text-color',
                '@btn-default-text-color',
                '@text-color'
            )
        );
        $fieldSetDefaultButtons->addElement(
            $this->createColorPickerField(
                '_btn-default-hover-text-color',
                '@btn-default-hover-text-color',
                '@brand-primary'
            )
        );
        $fieldSetDefaultButtons->addElement(
            $this->createColorPickerField(
                '_btn-default-border-color',
                '@btn-default-border-color',
                '@border-color'
            )
        );
        $fieldSetDefaultButtons->addElement(
            $this->createColorPickerField(
                '_btn-default-hover-border-color',
                '@btn-default-hover-border-color',
                '@brand-primary'
            )
        );

        $tab->addElement($fieldSetDefaultButtons);

        $attributes = array_merge($this->fieldSetDefaults, array('height' => 170));
        $fieldSetPrimaryButtons = $this->createFieldSet(
            'buttons_primary_fieldset',
            '__responsive_tab_buttons_fieldset_primary__',
            array('attributes' => $attributes)
        );

        $fieldSetPrimaryButtons->addElement(
            $this->createColorPickerField(
                '_btn-primary-top-bg',
                '@btn-primary-top-bg',
                '@brand-primary-light'
            )
        );
        $fieldSetPrimaryButtons->addElement(
            $this->createColorPickerField(
                '_btn-primary-bottom-bg',
                '@btn-primary-bottom-bg',
                '@brand-primary'
            )
        );
        $fieldSetPrimaryButtons->addElement(
            $this->createColorPickerField(
                '_btn-primary-hover-bg',
                '@btn-primary-hover-bg',
                '@brand-primary'
            )
        );
        $fieldSetPrimaryButtons->addElement(
            $this->createColorPickerField(
                '_btn-primary-text-color',
                '@btn-primary-text-color',
                '#FFFFFF'
            )
        );
        $fieldSetPrimaryButtons->addElement(
            $this->createColorPickerField(
                '_btn-primary-hover-text-color',
                '@btn-primary-hover-text-color',
                '@btn-primary-text-color'
            )
        );

        $tab->addElement($fieldSetPrimaryButtons);

        $attributes = array_merge($this->fieldSetDefaults, array('height' => 170));
        $fieldSetSecondaryButtons = $this->createFieldSet(
            'buttons_secondary_fieldset',
            '__responsive_tab_buttons_fieldset_secondary__',
            array('attributes' => $attributes)
        );

        $fieldSetSecondaryButtons->addElement(
            $this->createColorPickerField(
                '_btn-secondary-top-bg',
                '@btn-secondary-top-bg',
                '@brand-secondary'
            )
        );
        $fieldSetSecondaryButtons->addElement(
            $this->createColorPickerField(
                '_btn-secondary-bottom-bg',
                '@btn-secondary-bottom-bg',
                '@brand-secondary-dark'
            )
        );
        $fieldSetSecondaryButtons->addElement(
            $this->createColorPickerField(
                '_btn-secondary-hover-bg',
                '@btn-secondary-hover-bg',
                '@brand-secondary-dark'
            )
        );
        $fieldSetSecondaryButtons->addElement(
            $this->createColorPickerField(
                '_btn-secondary-text-color',
                '@btn-secondary-text-color',
                '#FFFFFF'
            )
        );
        $fieldSetSecondaryButtons->addElement(
            $this->createColorPickerField(
                '_btn-secondary-hover-text-color',
                '@btn-secondary-hover-text-color',
                '@btn-secondary-text-color'
            )
        );

        $tab->addElement($fieldSetSecondaryButtons);

        $attributes = array_merge($this->fieldSetDefaults, array('height' => 170));
        $fieldSetPanels = $this->createFieldSet(
            'panels_fieldset',
            '__responsive_tab_buttons_fieldset_panels__',
            array('attributes' => $attributes)
        );

        $fieldSetPanels->addElement(
            $this->createColorPickerField(
                '_panel-header-bg',
                '@panel-header-bg',
                '@gray-light'
            )
        );
        $fieldSetPanels->addElement(
            $this->createTextField(
                '_panel-header-font-size',
                '@panel-header-font-size',
                '14'
            )
        );
        $fieldSetPanels->addElement(
            $this->createColorPickerField(
                '_panel-header-color',
                '@panel-header-color',
                '@text-color'
            )
        );
        $fieldSetPanels->addElement(
            $this->createColorPickerField(
                '_panel-border',
                '@panel-border',
                '@border-color'
            )
        );
        $fieldSetPanels->addElement(
            $this->createColorPickerField(
                '_panel-bg',
                '@panel-bg',
                '#FFFFFF'
            )
        );

        $tab->addElement($fieldSetPanels);

        return $tab;
    }

    /**
     * Helper function to create the tab ("Forms")
     * @return Form\Container\Tab
     */
    private function createFormsTab()
    {
        $tab = $this->createTab(
            'forms_tab',
            '__responsive_tab_forms__',
            array(
                'attributes' => array(
                    'autoScroll' => true
                )
            )
        );

        $attributes = array_merge($this->fieldSetDefaults, array('height' => 90));
        $fieldSetLabels = $this->createFieldSet(
            'labels_fieldset',
            '__responsive_tab_forms_fieldset_labels__',
            array('attributes' => $attributes)
        );

        $fieldSetLabels->addElement(
            $this->createTextField(
                '_label-font-size',
                '@label-font-size',
                '14'
            )
        );
        $fieldSetLabels->addElement(
            $this->createColorPickerField(
                '_label-color',
                '@label-color',
                '@text-color'
            )
        );

        $tab->addElement($fieldSetLabels);

        $attributes = array_merge($this->fieldSetDefaults, array('height' => 160));
        $fieldSetFormBase = $this->createFieldSet(
            'form_base_fieldset',
            '__responsive_tab_forms_fieldset_global__',
            array('attributes' => $attributes)
        );

        $fieldSetFormBase->addElement(
            $this->createTextField(
                '_input-font-size',
                '@input-font-size',
                '14'
            )
        );
        $fieldSetFormBase->addElement(
            $this->createColorPickerField(
                '_input-bg',
                '@input-bg',
                '@gray-light'
            )
        );
        $fieldSetFormBase->addElement(
            $this->createColorPickerField(
                '_input-color',
                '@input-color',
                '@brand-secondary'
            )
        );
        $fieldSetFormBase->addElement(
            $this->createColorPickerField(
                '_input-placeholder-color',
                '@input-placeholder-color',
                'lighten(@text-color, 15%)'
            )
        );
        $fieldSetFormBase->addElement(
            $this->createColorPickerField(
                '_input-border',
                '@input-border',
                '@border-color'
            )
        );

        $tab->addElement($fieldSetFormBase);

        $attributes = array_merge($this->fieldSetDefaults, array('height' => 240));
        $fieldSetFormStates = $this->createFieldSet(
            'form_states_fieldset',
            '__responsive_tab_forms_fieldset_states__',
            array('attributes' => $attributes)
        );

        $fieldSetFormStates->addElement(
            $this->createColorPickerField(
                '_input-focus-bg',
                '@input-focus-bg',
                '#FFFFFF'
            )
        );
        $fieldSetFormStates->addElement(
            $this->createColorPickerField(
                '_input-focus-border',
                '@input-focus-border',
                '@brand-primary'
            )
        );
        $fieldSetFormStates->addElement(
            $this->createColorPickerField(
                '_input-focus-color',
                '@input-focus-color',
                '@brand-secondary'
            )
        );
        $fieldSetFormStates->addElement(
            $this->createColorPickerField(
                '_input-error-bg',
                '@input-error-bg',
                'desaturate(lighten(@highlight-error, 38%), 20%)'
            )
        );
        $fieldSetFormStates->addElement(
            $this->createColorPickerField(
                '_input-error-border',
                '@input-error-border',
                '@highlight-error'
            )
        );
        $fieldSetFormStates->addElement(
            $this->createColorPickerField(
                '_input-error-color',
                '@input-error-color',
                '@highlight-error'
            )
        );
        $fieldSetFormStates->addElement(
            $this->createColorPickerField(
                '_input-success-bg',
                '@input-success-bg',
                '#FFFFFF'
            )
        );
        $fieldSetFormStates->addElement(
            $this->createColorPickerField(
                '_input-success-border',
                '@input-success-border',
                '@highlight-success'
            )
        );
        $fieldSetFormStates->addElement(
            $this->createColorPickerField(
                '_input-success-color',
                '@input-success-color',
                '@brand-secondary-dark'
            )
        );

        $tab->addElement($fieldSetFormStates);

        return $tab;
    }

    /**
     * Helper function to create the tab ("Tables & Badges")
     * @return Form\Container\Tab
     */
    private function createTablesTab()
    {
        $tab = $this->createTab(
            'tables_tab',
            '__responsive_tab_tables__',
            array(
                'attributes' => array(
                    'autoScroll' => true
                )
            )
        );

        $attributes = array_merge($this->fieldSetDefaults, array('height' => 200));
        $fieldSetTables = $this->createFieldSet(
            'tables_fieldset',
            '__responsive_tab_tables_fieldset_tables__',
            array('attributes' => $attributes)
        );

        $fieldSetTables->addElement(
            $this->createColorPickerField(
                '_panel-table-header-bg',
                '@panel-table-header-bg',
                '@panel-bg'
            )
        );
        $fieldSetTables->addElement(
            $this->createColorPickerField(
                '_panel-table-header-color',
                '@panel-table-header-color',
                '@text-color-dark'
            )
        );
        $fieldSetTables->addElement(
            $this->createColorPickerField(
                '_table-row-bg',
                '@table-row-bg',
                '#FFFFFF'
            )
        );
        $fieldSetTables->addElement(
            $this->createColorPickerField(
                '_table-row-color',
                '@table-row-color',
                '@brand-secondary'
            )
        );
        $fieldSetTables->addElement(
            $this->createColorPickerField(
                '_table-row-highlight-bg',
                '@table-row-highlight-bg',
                'darken(@table-row-bg, 4%)'
            )
        );
        $fieldSetTables->addElement(
            $this->createColorPickerField(
                '_table-header-bg',
                '@table-header-bg',
                '@brand-secondary'
            )
        );
        $fieldSetTables->addElement(
            $this->createColorPickerField(
                '_table-header-color',
                '@table-header-color',
                '#FFFFFF'
            )
        );

        $tab->addElement($fieldSetTables);

        $attributes = array_merge($this->fieldSetDefaults, array('height' => 200));
        $fieldSetBadges = $this->createFieldSet(
            'badges_fieldset',
            '__responsive_tab_tables_fieldset_badges__',
            array('attributes' => $attributes)
        );

        $fieldSetBadges->addElement(
            $this->createColorPickerField(
                '_badge-discount-bg',
                '@badge-discount-bg',
                '@highlight-error'
            )
        );
        $fieldSetBadges->addElement(
            $this->createColorPickerField(
                '_badge-discount-color',
                '@badge-discount-color',
                '#FFFFFF'
            )
        );
        $fieldSetBadges->addElement(
            $this->createColorPickerField(
                '_badge-newcomer-bg',
                '@badge-newcomer-bg',
                '@highlight-notice'
            )
        );
        $fieldSetBadges->addElement(
            $this->createColorPickerField(
                '_badge-newcomer-color',
                '@badge-newcomer-color',
                '#FFFFFF'
            )
        );
        $fieldSetBadges->addElement(
            $this->createColorPickerField(
                '_badge-recommendation-bg',
                '@badge-recommendation-bg',
                '@highlight-success'
            )
        );
        $fieldSetBadges->addElement(
            $this->createColorPickerField(
                '_badge-recommendation-color',
                '@badge-recommendation-color',
                '#FFFFFF'
            )
        );
        $fieldSetBadges->addElement(
            $this->createColorPickerField(
                '_badge-download-bg',
                '@badge-download-bg',
                '@highlight-info'
            )
        );
        $fieldSetBadges->addElement(
            $this->createColorPickerField(
                '_badge-download-color',
                '@badge-download-color',
                '#FFFFFF'
            )
        );

        $tab->addElement($fieldSetBadges);

        return $tab;
    }

    /**
     * Helper function to create the main tab ("Responsive configuration")
     * @return Form\Container\Tab
     */
    private function createMainConfigTab()
    {
        $tab = $this->createTab(
            'responsiveMain',
            '__responsive_tab_header__',
            array(
                'attributes' => array(
                    'layout' => 'anchor',
                    'autoScroll' => true,
                    'padding' => '0',
                    'defaults' => array('anchor' => '100%')
                )
            )
        );

        $fieldSet = $this->createFieldSet(
            'bareGlobal',
            '__global_configuration__',
            array(
                'attributes' => array(
                    'padding' => '10',
                    'margin'=> '5',
                    'layout' => 'anchor',
                    'defaults' => array('labelWidth' => 155, 'anchor' => '100%')
                )
            )
        );

        $fieldSet->addElement(
            $this->createCheckboxField(
                'offcanvasCart',
                '__offcanvas_cart__',
                true,
                $this->getLabelAttribute(
                    'offcanvas_cart_description'
                )
            )
        );

        $fieldSet->addElement(
            $this->createCheckboxField(
                'offcanvasOverlayPage',
                '__offcanvas_move_method__',
                true,
                $this->getLabelAttribute(
                    'offcanvas_move_method_description'
                )
            )
        );

        $fieldSet->addElement(
            $this->createCheckboxField(
                'focusSearch',
                '__focus_search__',
                false,
                $this->getLabelAttribute(
                    'focus_search_description'
                )
            )
        );

        $fieldSet->addElement(
            $this->createCheckboxField(
                'displaySidebar',
                '__display_sidebar__',
                true,
                $this->getLabelAttribute(
                    'display_sidebar_description'
                )
            )
        );

        $fieldSet->addElement(
            $this->createCheckboxField(
                'checkoutHeader',
                '__checkout_header__',
                true,
                $this->getLabelAttribute(
                    'checkout_header_description'
                )
            )
        );

        $fieldSet->addElement(
            $this->createCheckboxField(
                'checkoutFooter',
                '__checkout_footer__',
                true,
                $this->getLabelAttribute(
                    'checkout_footer_description'
                )
            )
        );

        $fieldSet->addElement(
            $this->createCheckboxField(
                'infiniteScrolling',
                '__enable_infinite_scrolling__',
                true,
                $this->getLabelAttribute(
                    'enable_infinite_scrolling_description'
                )
            )
        );

        $fieldSet->addElement(
            $this->createNumberField(
                'infiniteThreshold',
                '__infinite_threshold__',
                4,
                $this->getLabelAttribute(
                    'infinite_threshold_description',
                    'supportText'
                )
            )
        );

        $fieldSet->addElement(
            $this->createSelectField(
                'lightboxZoomFactor',
                '__lightbox_zoom_factor__',
                0,
                array(
                    array('value' => 0, 'text' => '__lightbox_zoom_factor_auto__'),
                    array('value' => 1, 'text' => '__lightbox_zoom_factor_none__'),
                    array('value' => 2, 'text' => '__lightbox_zoom_factor_2x__'),
                    array('value' => 3, 'text' => '__lightbox_zoom_factor_3x__'),
                    array('value' => 5, 'text' => '__lightbox_zoom_factor_5x__')
                ),
                $this->getLabelAttribute(
                    'lightbox_zoom_factor_description',
                    'supportText'
                )
            )
        );
        
        $fieldSet->addElement(
            $this->createTextField(
                'appleWebAppTitle',
                '__apple_web_app_title__',
                ''
            )
        );

        $tab->addElement($fieldSet);

        $fieldSet = $this->createFieldSet(
            'responsiveGlobal',
            '__advanced_settings__',
            array(
                'attributes' => array(
                    'padding' => '10',
                    'margin' => '5',
                    'layout' => 'anchor',
                    'defaults' => array('anchor' => '100%', 'labelWidth' => 155)
                )
            )
        );

        $fieldSet->addElement(
            $this->createTextAreaField(
                'additionalCssData',
                '__additional_css_data__',
                '',
                array('attributes' => array('xtype' => 'textarea', 'lessCompatible' => false), 'help' => '__additional_css_data_description__')
            )
        );

        $fieldSet->addElement(
            $this->createTextAreaField(
                'additionalJsLibraries',
                '__additional_js_libraries__',
                '',
                array('attributes' => array('xtype' => 'textarea', 'lessCompatible' => false), 'help' => '__additional_js_libraries_description__')
            )
        );

        $tab->addElement($fieldSet);

        return $tab;
    }

    /**
     * Helper function to get the attribute of a checkbox field which shows a description label
     * @param $snippetName
     * @return array
     */
    private function getLabelAttribute($snippetName, $labelType = 'boxLabel')
    {
        $description = Shopware()->Snippets()->getNamespace('themes/bare/backend/config')->get($snippetName);
        return array('attributes' => array($labelType => $description));
    }

    /**
     * Helper function to merge default theme colors with color schemes
     * @param ArrayCollection $collection
     */
    public function createConfigSets(ArrayCollection $collection)
    {
        $set = new ConfigSet();
        $set->setName('__color_scheme_turquoise__')->setDescription(
            '__color_scheme_turquoise_description__'
        )->setValues(
            array_merge($this->themeColorDefaults,
                array(
                    '_brand-primary' => '#1db3b8',
                    '_brand-primary-light' => 'lighten(@brand-primary, 5%)'
                )
            )
        );
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('__color_scheme_green__')->setDescription(
            '__color_scheme_green_description__'
        )->setValues(
            array_merge($this->themeColorDefaults,
                array(
                    '_brand-primary' => '#72a425',
                    '_brand-primary-light' => 'saturate(lighten(@brand-primary, 5%), 5%)'
                )
            )
        );
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('__color_scheme_red__')->setDescription(
            '__color_scheme_red_description__'
        )->setValues(
            array_merge($this->themeColorDefaults,
                array(
                    '_brand-primary' => '#be0a30',
                    '_brand-primary-light' => 'saturate(lighten(@brand-primary, 10%), 5%)'
                )
            )
        );
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('__color_scheme_pink__')->setDescription(
            '__color_scheme_pink_description__'
        )->setValues(
            array_merge($this->themeColorDefaults,
                array(
                    '_brand-primary' => '#d31e81'
                )
            )
        );
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('__color_scheme_gray__')->setDescription(
            '__color_scheme_gray_description__'
        )->setValues(
            array_merge($this->themeColorDefaults,
                array(
                    '_brand-primary' => '#555555',
                    '_brand-primary-light' => 'lighten(@brand-primary, 10%)',
                    '_brand-secondary' => '#999999',
                    '_brand-secondary-dark' => 'darken(@brand-secondary, 8%)',
                    '_text-color' => '@brand-primary-light',
                    '_text-color-dark' => '@brand-primary',
                    '_link-color' => '@brand-secondary'
                )
            )
        );
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('__color_scheme_brown__')->setDescription(
            '__color_scheme_brown_description__'
        )->setValues(
            array_merge($this->themeColorDefaults,
                array(
                    '_brand-primary' => '#613400',
                    '_brand-primary-light' => 'saturate(lighten(@brand-primary,5%), 5%)'
                )
            )
        );
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('__color_scheme_blue__')->setDescription(
            '__color_scheme_blue_description__'
        )->setValues(
            array_merge($this->themeColorDefaults,
                array(
                    '_brand-primary' => '#009ee0'
                )
            )
        );
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('__color_scheme_black__')->setDescription(
            '__color_scheme_black_description__'
        )->setValues(
            array_merge($this->themeColorDefaults,
                array(
                    '_brand-primary' => '#000000',
                    '_brand-primary-light' => 'lighten(@brand-primary, 20%)',
                    '_brand-secondary' => '#555555',
                    '_brand-secondary-dark' => 'darken(@brand-secondary, 10%)'
                )
            )
        );
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('__color_scheme_orange__')->setDescription(
            '__color_scheme_orange_description__'
        )->setValues($this->themeColorDefaults);
        $collection->add($set);
    }
}
