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
     * @var bool
     */
    protected $injectBeforePlugins = true;

    /**
     * Javascript files which will be used in the theme
     *
     * @var array
     */
    protected $javascript = [

        // Third party plugins / libraries
        'src/js/vendors/modernizr/modernizr.custom.35977.js',
        'vendors/js/jquery/jquery.min.js',
        'vendors/js/picturefill/picturefill.min.js',
        'vendors/js/jquery.transit/jquery.transit.js',
        'vendors/js/jquery.event.move/jquery.event.move.js',
        'vendors/js/jquery.event.swipe/jquery.event.swipe.js',
        'vendors/js/flatpickr/flatpickr.min.js',

        // Shopware specific plugins
        'src/js/jquery.ie-fixes.js',
        'src/js/jquery.plugin-base.js',
        'src/js/jquery.state-manager.js',
        'src/js/jquery.storage-manager.js',
        'src/js/jquery.off-canvas-menu.js',
        'src/js/jquery.search.js',
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
        'src/js/jquery.jump-to-tab.js',
        'src/js/jquery.ajax-variant.js',
        'src/js/jquery.csrf-protection.js',
        'src/js/jquery.panel-auto-resizer.js',
        'src/js/jquery.address-selection.js',
        'src/js/jquery.address-editor.js',
        'src/js/jquery.datepicker.js',
        'src/js/jquery.cookie-permission.js',
        'src/js/jquery.shopware-responsive.js'
    ];

    private $fieldSetDefaults = [
        'layout' => 'column',
        'height' => 170,
        'flex' => 0,
        'defaults' => ['columnWidth' => 0.5, 'labelWidth' => 180, 'margin' => '3 16 3 0']
    ];

    private $themeColorDefaults = [
        "brand-primary" => "#D9400B",
        "brand-primary-light" => "saturate(lighten(@brand-primary,12%), 5%)",
        "brand-secondary" => "#5F7285",
        "brand-secondary-dark" => "darken(@brand-secondary, 15%)",
        "gray" => "#F5F5F8",
        "gray-light" => "lighten(@gray, 1%)",
        "gray-dark" => "darken(@gray-light, 10%)",
        "border-color" => "@gray-dark",
        "highlight-success" => "#2ECC71",
        "highlight-error" => "#E74C3C",
        "highlight-notice" => "#F1C40F",
        "highlight-info" => "#4AA3DF",
        "body-bg" => "darken(@gray-light, 5%)",
        "overlay-bg" => "#000000",
        "overlay-dark-bg" => "@overlay-bg",
        "overlay-light-bg" => "#FFFFFF",
        "overlay-opacity" => "0.7",
        "text-color" => "@brand-secondary",
        "text-color-dark" => "@brand-secondary-dark",
        "link-color" => "@brand-primary",
        "link-hover-color" => "darken(@link-color, 10%)",
        "rating-star-color" => "@highlight-notice",
        "btn-default-top-bg" => "#FFFFFF",
        "btn-default-bottom-bg" => "@gray-light",
        "btn-default-hover-bg" => "#FFFFFF",
        "btn-default-text-color" => "@text-color",
        "btn-default-hover-text-color" => "@brand-primary",
        "btn-default-border-color" => "@border-color",
        "btn-default-hover-border-color" => "@brand-primary",
        "btn-primary-top-bg" => "@brand-primary-light",
        "btn-primary-bottom-bg" => "@brand-primary",
        "btn-primary-hover-bg" => "@brand-primary",
        "btn-primary-text-color" => "#FFFFFF",
        "btn-primary-hover-text-color" => "@btn-primary-text-color",
        "btn-secondary-top-bg" => "@brand-secondary",
        "btn-secondary-bottom-bg" => "@brand-secondary-dark",
        "btn-secondary-hover-bg" => "@brand-secondary-dark",
        "btn-secondary-text-color" => "#FFFFFF",
        "btn-secondary-hover-text-color" => "@btn-secondary-text-color",
        "panel-header-bg" => "@gray-light",
        "panel-header-color" => "@text-color",
        "panel-border" => "@border-color",
        "panel-bg" => "#FFFFFF",
        "label-color" => "@text-color",
        "input-bg" => "@gray-light",
        "input-color" => "@brand-secondary",
        "input-placeholder-color" => "lighten(@text-color, 15%)",
        "input-border" => "@border-color",
        "input-focus-bg" => "#FFFFFF",
        "input-focus-border" => "@brand-primary",
        "input-focus-color" => "@brand-secondary",
        "input-error-bg" => "desaturate(lighten(@highlight-error, 38%), 20%)",
        "input-error-border" => "@highlight-error",
        "input-error-color" => "@highlight-error",
        "input-success-bg" => "#FFFFFF",
        "input-success-border" => "@highlight-success",
        "input-success-color" => "@brand-secondary-dark",
        "panel-table-header-bg" => "@panel-bg",
        "panel-table-header-color" => "@text-color-dark",
        "table-row-bg" => "#FFFFFF",
        "table-row-color" => "@brand-secondary",
        "table-row-highlight-bg" => "darken(@table-row-bg, 4%)",
        "table-header-bg" => "@brand-secondary",
        "table-header-color" => "#FFFFFF",
        "badge-discount-bg" => "@highlight-error",
        "badge-discount-color" => "#FFFFFF",
        "badge-newcomer-bg" => "@highlight-notice",
        "badge-newcomer-color" => "#FFFFFF",
        "badge-recommendation-bg" => "@highlight-success",
        "badge-recommendation-color" => "#FFFFFF",
        "badge-download-bg" => "@highlight-info",
        "badge-download-color" => "#FFFFFF"
    ];

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
        $attributes = array_merge($this->fieldSetDefaults, ['height' => 130]);
        $fieldSet = $this->createFieldSet(
            'basic_field_set',
            '__responsive_tab_general_fieldset_base__',
            ['attributes' => $attributes]
        );

        $fieldSet->addElement(
            $this->createColorPickerField(
                'brand-primary',
                '@brand-primary',
                '#D9400B'
            )
        );
        $fieldSet->addElement(
            $this->createColorPickerField(
                'brand-primary-light',
                '@brand-primary-light',
                'saturate(lighten(@brand-primary, 12%), 5%)'
            )
        );
        $fieldSet->addElement(
            $this->createColorPickerField(
                'brand-secondary',
                '@brand-secondary',
                '#5F7285'
            )
        );
        $fieldSet->addElement(
            $this->createColorPickerField(
                'brand-secondary-dark',
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
            [
                'attributes' => [
                    'plain' => true
                ]
            ]
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
            [
                'attributes' => [
                    'autoScroll' => true
                ]
            ]
        );

        $attributes = array_merge($this->fieldSetDefaults, ['height' => 130]);
        $fieldSetGrey = $this->createFieldSet(
            'grey_tones',
            '__responsive_tab_general_fieldset_grey__',
            ['attributes' => $attributes]
        );

        $fieldSetGrey->addElement(
            $this->createColorPickerField(
                'gray',
                '@gray',
                '#F5F5F8'
            )
        );
        $fieldSetGrey->addElement(
            $this->createColorPickerField(
                'gray-light',
                '@gray-light',
                'lighten(@gray, 1%)'
            )
        );
        $fieldSetGrey->addElement(
            $this->createColorPickerField(
                'gray-dark',
                '@gray-dark',
                'darken(@gray-light, 10%)'
            )
        );
        $fieldSetGrey->addElement(
            $this->createColorPickerField(
                'border-color',
                '@border-color',
                '@gray-dark'
            )
        );

        $basicFieldSet = $this->createBasicFieldSet();
        $tab->addElement($basicFieldSet);
        $tab->addElement($fieldSetGrey);

        $attributes = array_merge($this->fieldSetDefaults, ['height' => 130]);
        $fieldSetHighlights = $this->createFieldSet(
            'highlight_colors',
            '__responsive_tab_general_fieldset_highlight__',
            ['attributes' => $attributes]
        );

        $fieldSetHighlights->addElement(
            $this->createColorPickerField(
                'highlight-success',
                '@highlight-success',
                '#2ECC71'
            )
        );
        $fieldSetHighlights->addElement(
            $this->createColorPickerField(
                'highlight-error',
                '@highlight-error',
                '#E74C3C'
            )
        );
        $fieldSetHighlights->addElement(
            $this->createColorPickerField(
                'highlight-notice',
                '@highlight-notice',
                '#F1C40F'
            )
        );
        $fieldSetHighlights->addElement(
            $this->createColorPickerField(
                'highlight-info',
                '@highlight-info',
                '#4AA3DF'
            )
        );

        $tab->addElement($fieldSetHighlights);

        $attributes = array_merge($this->fieldSetDefaults, ['height' => 220]);
        $fieldSetScaffolding = $this->createFieldSet(
            'scaffolding',
            '__responsive_tab_general_fieldset_scaffolding__',
            ['attributes' => $attributes]
        );

        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                'body-bg',
                '@body-bg',
                'darken(@gray-light, 5%)'
            )
        );
        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                'text-color',
                '@text-color',
                '@brand-secondary'
            )
        );
        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                'text-color-dark',
                '@text-color-dark',
                '@brand-secondary-dark'
            )
        );
        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                'link-color',
                '@link-color',
                '@brand-primary'
            )
        );
        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                'link-hover-color',
                '@link-hover-color',
                'darken(@link-color, 10%)'
            )
        );
        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                'rating-star-color',
                '@rating-star-color',
                '@highlight-notice'
            )
        );
        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                'overlay-bg',
                '@overlay-bg',
                '#000000'
            )
        );

        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                'overlay-theme-dark-bg',
                '@overlay-theme-dark-bg',
                '@overlay-bg'
            )
        );

        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                'overlay-theme-light-bg',
                '@overlay-theme-light-bg',
                '#FFFFFF'
            )
        );

        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                'overlay-opacity',
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
            ['attributes' => ['autoScroll' => true]]
        );

        $attributes = array_merge($this->fieldSetDefaults, ['height' => 170]);
        $fieldSetBasic = $this->createFieldSet(
            'typo_base',
            '__responsive_tab_typo_fieldset_base__',
            ['attributes' => $attributes]
        );

        $fieldSetBasic->addElement(
            $this->createTextField(
                'font-base-stack',
                '@font-base-stack',
                '"Open Sans", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;'
            )
        );
        $fieldSetBasic->addElement(
            $this->createTextField(
                'font-headline-stack',
                '@font-headline-stack',
                '@font-base-stack'
            )
        );
        $fieldSetBasic->addElement(
            $this->createTextField(
                'font-size-base',
                '@font-size-base',
                '14'
            )
        );
        $fieldSetBasic->addElement(
            $this->createTextField(
                'font-base-weight',
                '@font-base-weight',
                '500'
            )
        );
        $fieldSetBasic->addElement(
            $this->createTextField(
                'font-light-weight',
                '@font-light-weight',
                '300'
            )
        );
        $fieldSetBasic->addElement(
            $this->createTextField(
                'font-bold-weight',
                '@font-bold-weight',
                '700'
            )
        );

        $tab->addElement($fieldSetBasic);

        $attributes = array_merge($this->fieldSetDefaults, ['height' => 170]);
        $fieldSetHeadlines = $this->createFieldSet(
            'typo_headlines',
            '__responsive_tab_typo_fieldset_headlines__',
            ['attributes' => $attributes]
        );

        $fieldSetHeadlines->addElement(
            $this->createTextField(
                'font-size-h1',
                '@font-size-h1',
                '26'
            )
        );
        $fieldSetHeadlines->addElement(
            $this->createTextField(
                'font-size-h2',
                '@font-size-h2',
                '21'
            )
        );
        $fieldSetHeadlines->addElement(
            $this->createTextField(
                'font-size-h3',
                '@font-size-h3',
                '18'
            )
        );
        $fieldSetHeadlines->addElement(
            $this->createTextField(
                'font-size-h4',
                '@font-size-h4',
                '16'
            )
        );
        $fieldSetHeadlines->addElement(
            $this->createTextField(
                'font-size-h5',
                '@font-size-h5',
                '@font-size-base'
            )
        );
        $fieldSetHeadlines->addElement(
            $this->createTextField(
                'font-size-h6',
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
            [
                'attributes' => [
                    'autoScroll' => true
                ]
            ]
        );

        $attributes = array_merge($this->fieldSetDefaults, ['height' => 90]);
        $fieldSetButtons = $this->createFieldSet(
            'buttons_fieldset',
            '__responsive_tab_buttons_fieldset_global__',
            ['attributes' => $attributes]
        );

        $fieldSetButtons->addElement(
            $this->createTextField(
                'btn-font-size',
                '@btn-font-size',
                '14'
            )
        );
        $fieldSetButtons->addElement(
            $this->createTextField(
                'btn-icon-size',
                '@btn-icon-size',
                '10'
            )
        );

        $tab->addElement($fieldSetButtons);

        $attributes = array_merge($this->fieldSetDefaults, ['height' => 200]);
        $fieldSetDefaultButtons = $this->createFieldSet(
            'buttons_default_fieldset',
            '__responsive_tab_buttons_fieldset_default__',
            ['attributes' => $attributes]
        );

        $fieldSetDefaultButtons->addElement(
            $this->createColorPickerField(
                'btn-default-top-bg',
                '@btn-default-top-bg',
                '#FFFFFF'
            )
        );
        $fieldSetDefaultButtons->addElement(
            $this->createColorPickerField(
                'btn-default-bottom-bg',
                '@btn-default-bottom-bg',
                '@gray-light'
            )
        );
        $fieldSetDefaultButtons->addElement(
            $this->createColorPickerField(
                'btn-default-hover-bg',
                '@btn-default-hover-bg',
                '#FFFFFF'
            )
        );
        $fieldSetDefaultButtons->addElement(
            $this->createColorPickerField(
                'btn-default-text-color',
                '@btn-default-text-color',
                '@text-color'
            )
        );
        $fieldSetDefaultButtons->addElement(
            $this->createColorPickerField(
                'btn-default-hover-text-color',
                '@btn-default-hover-text-color',
                '@brand-primary'
            )
        );
        $fieldSetDefaultButtons->addElement(
            $this->createColorPickerField(
                'btn-default-border-color',
                '@btn-default-border-color',
                '@border-color'
            )
        );
        $fieldSetDefaultButtons->addElement(
            $this->createColorPickerField(
                'btn-default-hover-border-color',
                '@btn-default-hover-border-color',
                '@brand-primary'
            )
        );

        $tab->addElement($fieldSetDefaultButtons);

        $attributes = array_merge($this->fieldSetDefaults, ['height' => 170]);
        $fieldSetPrimaryButtons = $this->createFieldSet(
            'buttons_primary_fieldset',
            '__responsive_tab_buttons_fieldset_primary__',
            ['attributes' => $attributes]
        );

        $fieldSetPrimaryButtons->addElement(
            $this->createColorPickerField(
                'btn-primary-top-bg',
                '@btn-primary-top-bg',
                '@brand-primary-light'
            )
        );
        $fieldSetPrimaryButtons->addElement(
            $this->createColorPickerField(
                'btn-primary-bottom-bg',
                '@btn-primary-bottom-bg',
                '@brand-primary'
            )
        );
        $fieldSetPrimaryButtons->addElement(
            $this->createColorPickerField(
                'btn-primary-hover-bg',
                '@btn-primary-hover-bg',
                '@brand-primary'
            )
        );
        $fieldSetPrimaryButtons->addElement(
            $this->createColorPickerField(
                'btn-primary-text-color',
                '@btn-primary-text-color',
                '#FFFFFF'
            )
        );
        $fieldSetPrimaryButtons->addElement(
            $this->createColorPickerField(
                'btn-primary-hover-text-color',
                '@btn-primary-hover-text-color',
                '@btn-primary-text-color'
            )
        );

        $tab->addElement($fieldSetPrimaryButtons);

        $attributes = array_merge($this->fieldSetDefaults, ['height' => 170]);
        $fieldSetSecondaryButtons = $this->createFieldSet(
            'buttons_secondary_fieldset',
            '__responsive_tab_buttons_fieldset_secondary__',
            ['attributes' => $attributes]
        );

        $fieldSetSecondaryButtons->addElement(
            $this->createColorPickerField(
                'btn-secondary-top-bg',
                '@btn-secondary-top-bg',
                '@brand-secondary'
            )
        );
        $fieldSetSecondaryButtons->addElement(
            $this->createColorPickerField(
                'btn-secondary-bottom-bg',
                '@btn-secondary-bottom-bg',
                '@brand-secondary-dark'
            )
        );
        $fieldSetSecondaryButtons->addElement(
            $this->createColorPickerField(
                'btn-secondary-hover-bg',
                '@btn-secondary-hover-bg',
                '@brand-secondary-dark'
            )
        );
        $fieldSetSecondaryButtons->addElement(
            $this->createColorPickerField(
                'btn-secondary-text-color',
                '@btn-secondary-text-color',
                '#FFFFFF'
            )
        );
        $fieldSetSecondaryButtons->addElement(
            $this->createColorPickerField(
                'btn-secondary-hover-text-color',
                '@btn-secondary-hover-text-color',
                '@btn-secondary-text-color'
            )
        );

        $tab->addElement($fieldSetSecondaryButtons);

        $attributes = array_merge($this->fieldSetDefaults, ['height' => 170]);
        $fieldSetPanels = $this->createFieldSet(
            'panels_fieldset',
            '__responsive_tab_buttons_fieldset_panels__',
            ['attributes' => $attributes]
        );

        $fieldSetPanels->addElement(
            $this->createColorPickerField(
                'panel-header-bg',
                '@panel-header-bg',
                '@gray-light'
            )
        );
        $fieldSetPanels->addElement(
            $this->createTextField(
                'panel-header-font-size',
                '@panel-header-font-size',
                '14'
            )
        );
        $fieldSetPanels->addElement(
            $this->createColorPickerField(
                'panel-header-color',
                '@panel-header-color',
                '@text-color'
            )
        );
        $fieldSetPanels->addElement(
            $this->createColorPickerField(
                'panel-border',
                '@panel-border',
                '@border-color'
            )
        );
        $fieldSetPanels->addElement(
            $this->createColorPickerField(
                'panel-bg',
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
            [
                'attributes' => [
                    'autoScroll' => true
                ]
            ]
        );

        $attributes = array_merge($this->fieldSetDefaults, ['height' => 90]);
        $fieldSetLabels = $this->createFieldSet(
            'labels_fieldset',
            '__responsive_tab_forms_fieldset_labels__',
            ['attributes' => $attributes]
        );

        $fieldSetLabels->addElement(
            $this->createTextField(
                'label-font-size',
                '@label-font-size',
                '14'
            )
        );
        $fieldSetLabels->addElement(
            $this->createColorPickerField(
                'label-color',
                '@label-color',
                '@text-color'
            )
        );

        $tab->addElement($fieldSetLabels);

        $attributes = array_merge($this->fieldSetDefaults, ['height' => 160]);
        $fieldSetFormBase = $this->createFieldSet(
            'form_base_fieldset',
            '__responsive_tab_forms_fieldset_global__',
            ['attributes' => $attributes]
        );

        $fieldSetFormBase->addElement(
            $this->createTextField(
                'input-font-size',
                '@input-font-size',
                '14'
            )
        );
        $fieldSetFormBase->addElement(
            $this->createColorPickerField(
                'input-bg',
                '@input-bg',
                '@gray-light'
            )
        );
        $fieldSetFormBase->addElement(
            $this->createColorPickerField(
                'input-color',
                '@input-color',
                '@brand-secondary'
            )
        );
        $fieldSetFormBase->addElement(
            $this->createColorPickerField(
                'input-placeholder-color',
                '@input-placeholder-color',
                'lighten(@text-color, 15%)'
            )
        );
        $fieldSetFormBase->addElement(
            $this->createColorPickerField(
                'input-border',
                '@input-border',
                '@border-color'
            )
        );

        $tab->addElement($fieldSetFormBase);

        $attributes = array_merge($this->fieldSetDefaults, ['height' => 240]);
        $fieldSetFormStates = $this->createFieldSet(
            'form_states_fieldset',
            '__responsive_tab_forms_fieldset_states__',
            ['attributes' => $attributes]
        );

        $fieldSetFormStates->addElement(
            $this->createColorPickerField(
                'input-focus-bg',
                '@input-focus-bg',
                '#FFFFFF'
            )
        );
        $fieldSetFormStates->addElement(
            $this->createColorPickerField(
                'input-focus-border',
                '@input-focus-border',
                '@brand-primary'
            )
        );
        $fieldSetFormStates->addElement(
            $this->createColorPickerField(
                'input-focus-color',
                '@input-focus-color',
                '@brand-secondary'
            )
        );
        $fieldSetFormStates->addElement(
            $this->createColorPickerField(
                'input-error-bg',
                '@input-error-bg',
                'desaturate(lighten(@highlight-error, 38%), 20%)'
            )
        );
        $fieldSetFormStates->addElement(
            $this->createColorPickerField(
                'input-error-border',
                '@input-error-border',
                '@highlight-error'
            )
        );
        $fieldSetFormStates->addElement(
            $this->createColorPickerField(
                'input-error-color',
                '@input-error-color',
                '@highlight-error'
            )
        );
        $fieldSetFormStates->addElement(
            $this->createColorPickerField(
                'input-success-bg',
                '@input-success-bg',
                '#FFFFFF'
            )
        );
        $fieldSetFormStates->addElement(
            $this->createColorPickerField(
                'input-success-border',
                '@input-success-border',
                '@highlight-success'
            )
        );
        $fieldSetFormStates->addElement(
            $this->createColorPickerField(
                'input-success-color',
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
            [
                'attributes' => [
                    'autoScroll' => true
                ]
            ]
        );

        $attributes = array_merge($this->fieldSetDefaults, ['height' => 200]);
        $fieldSetTables = $this->createFieldSet(
            'tables_fieldset',
            '__responsive_tab_tables_fieldset_tables__',
            ['attributes' => $attributes]
        );

        $fieldSetTables->addElement(
            $this->createColorPickerField(
                'panel-table-header-bg',
                '@panel-table-header-bg',
                '@panel-bg'
            )
        );
        $fieldSetTables->addElement(
            $this->createColorPickerField(
                'panel-table-header-color',
                '@panel-table-header-color',
                '@text-color-dark'
            )
        );
        $fieldSetTables->addElement(
            $this->createColorPickerField(
                'table-row-bg',
                '@table-row-bg',
                '#FFFFFF'
            )
        );
        $fieldSetTables->addElement(
            $this->createColorPickerField(
                'table-row-color',
                '@table-row-color',
                '@brand-secondary'
            )
        );
        $fieldSetTables->addElement(
            $this->createColorPickerField(
                'table-row-highlight-bg',
                '@table-row-highlight-bg',
                'darken(@table-row-bg, 4%)'
            )
        );
        $fieldSetTables->addElement(
            $this->createColorPickerField(
                'table-header-bg',
                '@table-header-bg',
                '@brand-secondary'
            )
        );
        $fieldSetTables->addElement(
            $this->createColorPickerField(
                'table-header-color',
                '@table-header-color',
                '#FFFFFF'
            )
        );

        $tab->addElement($fieldSetTables);

        $attributes = array_merge($this->fieldSetDefaults, ['height' => 200]);
        $fieldSetBadges = $this->createFieldSet(
            'badges_fieldset',
            '__responsive_tab_tables_fieldset_badges__',
            ['attributes' => $attributes]
        );

        $fieldSetBadges->addElement(
            $this->createColorPickerField(
                'badge-discount-bg',
                '@badge-discount-bg',
                '@highlight-error'
            )
        );
        $fieldSetBadges->addElement(
            $this->createColorPickerField(
                'badge-discount-color',
                '@badge-discount-color',
                '#FFFFFF'
            )
        );
        $fieldSetBadges->addElement(
            $this->createColorPickerField(
                'badge-newcomer-bg',
                '@badge-newcomer-bg',
                '@highlight-notice'
            )
        );
        $fieldSetBadges->addElement(
            $this->createColorPickerField(
                'badge-newcomer-color',
                '@badge-newcomer-color',
                '#FFFFFF'
            )
        );
        $fieldSetBadges->addElement(
            $this->createColorPickerField(
                'badge-recommendation-bg',
                '@badge-recommendation-bg',
                '@highlight-success'
            )
        );
        $fieldSetBadges->addElement(
            $this->createColorPickerField(
                'badge-recommendation-color',
                '@badge-recommendation-color',
                '#FFFFFF'
            )
        );
        $fieldSetBadges->addElement(
            $this->createColorPickerField(
                'badge-download-bg',
                '@badge-download-bg',
                '@highlight-info'
            )
        );
        $fieldSetBadges->addElement(
            $this->createColorPickerField(
                'badge-download-color',
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
            [
                'attributes' => [
                    'layout' => 'anchor',
                    'autoScroll' => true,
                    'padding' => '0',
                    'defaults' => ['anchor' => '100%']
                ]
            ]
        );

        $fieldSet = $this->createFieldSet(
            'bareGlobal',
            '__global_configuration__',
            [
                'attributes' => [
                    'padding' => '10',
                    'margin' => '5',
                    'layout' => 'anchor',
                    'defaults' => ['labelWidth' => 155, 'anchor' => '100%']
                ]
            ]
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
                'sidebarFilter',
                '__show_filter_in_sidebar__',
                false,
                $this->getLabelAttribute(
                    'show_filter_in_sidebar_description'
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
                [
                    ['value' => 0, 'text' => '__lightbox_zoom_factor_auto__'],
                    ['value' => 1, 'text' => '__lightbox_zoom_factor_none__'],
                    ['value' => 2, 'text' => '__lightbox_zoom_factor_2x__'],
                    ['value' => 3, 'text' => '__lightbox_zoom_factor_3x__'],
                    ['value' => 5, 'text' => '__lightbox_zoom_factor_5x__']
                ],
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
                '',
                ['attributes' => ['lessCompatible' => false]]
            )
        );

        $fieldSet->addElement(
            $this->createCheckboxField(
                'ajaxVariantSwitch',
                '__ajax_variant_switch__',
                true,
                ['attributes' => [
                    'lessCompatible' => false,
                    'boxLabel' => Shopware()->Snippets()->getNamespace('themes/bare/backend/config')->get('ajax_variant_switch_description')
                ]]
            )
        );

        $fieldSet->addElement(
            $this->createCheckboxField(
                'asyncJavascriptLoading',
                '__async_javascript_loading__',
                true,
                ['attributes' => [
                    'lessCompatible' => false,
                    'boxLabel' => Shopware()->Snippets()->getNamespace('themes/bare/backend/config')->get('async_javascript_loading_description')
                ]]
            )
        );

        $tab->addElement($fieldSet);

        $fieldSet = $this->createFieldSet(
            'responsiveGlobal',
            '__advanced_settings__',
            [
                'attributes' => [
                    'padding' => '10',
                    'margin' => '5',
                    'layout' => 'anchor',
                    'defaults' => ['anchor' => '100%', 'labelWidth' => 155]
                ]
            ]
        );

        $fieldSet->addElement(
            $this->createTextAreaField(
                'additionalCssData',
                '__additional_css_data__',
                '',
                ['attributes' => ['xtype' => 'textarea', 'lessCompatible' => false], 'help' => '__additional_css_data_description__']
            )
        );

        $fieldSet->addElement(
            $this->createTextAreaField(
                'additionalJsLibraries',
                '__additional_js_libraries__',
                '',
                ['attributes' => ['xtype' => 'textarea', 'lessCompatible' => false], 'help' => '__additional_js_libraries_description__']
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
        return ['attributes' => [$labelType => $description]];
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
            array_merge(
                $this->themeColorDefaults,
                [
                    'brand-primary' => '#1db3b8',
                    'brand-primary-light' => 'lighten(@brand-primary, 5%)'
                ]
            )
        );
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('__color_scheme_green__')->setDescription(
            '__color_scheme_green_description__'
        )->setValues(
            array_merge(
                $this->themeColorDefaults,
                [
                    'brand-primary' => '#72a425',
                    'brand-primary-light' => 'saturate(lighten(@brand-primary, 5%), 5%)'
                ]
            )
        );
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('__color_scheme_red__')->setDescription(
            '__color_scheme_red_description__'
        )->setValues(
            array_merge(
                $this->themeColorDefaults,
                [
                    'brand-primary' => '#be0a30',
                    'brand-primary-light' => 'saturate(lighten(@brand-primary, 10%), 5%)'
                ]
            )
        );
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('__color_scheme_pink__')->setDescription(
            '__color_scheme_pink_description__'
        )->setValues(
            array_merge(
                $this->themeColorDefaults,
                [
                    'brand-primary' => '#d31e81'
                ]
            )
        );
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('__color_scheme_gray__')->setDescription(
            '__color_scheme_gray_description__'
        )->setValues(
            array_merge(
                $this->themeColorDefaults,
                [
                    'brand-primary' => '#555555',
                    'brand-primary-light' => 'lighten(@brand-primary, 10%)',
                    'brand-secondary' => '#999999',
                    'brand-secondary-dark' => 'darken(@brand-secondary, 8%)',
                    'text-color' => '@brand-primary-light',
                    'text-color-dark' => '@brand-primary',
                    'link-color' => '@brand-secondary'
                ]
            )
        );
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('__color_scheme_brown__')->setDescription(
            '__color_scheme_brown_description__'
        )->setValues(
            array_merge(
                $this->themeColorDefaults,
                [
                    'brand-primary' => '#613400',
                    'brand-primary-light' => 'saturate(lighten(@brand-primary,5%), 5%)'
                ]
            )
        );
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('__color_scheme_blue__')->setDescription(
            '__color_scheme_blue_description__'
        )->setValues(
            array_merge(
                $this->themeColorDefaults,
                [
                    'brand-primary' => '#009ee0'
                ]
            )
        );
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('__color_scheme_black__')->setDescription(
            '__color_scheme_black_description__'
        )->setValues(
            array_merge(
                $this->themeColorDefaults,
                [
                    'brand-primary' => '#000000',
                    'brand-primary-light' => 'lighten(@brand-primary, 20%)',
                    'brand-secondary' => '#555555',
                    'brand-secondary-dark' => 'darken(@brand-secondary, 10%)'
                ]
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
