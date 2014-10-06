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
        'src/js/jquery.ajax-product-navigation.js',
        'src/js/jquery.newsletter.js',
        'src/js/jquery.menu-scroller.js',
        'src/js/jquery.shipping-payment.js',
        'src/js/jquery.add-article.js',
        'src/js/jquery.ui.datepicker.js',
        'src/js/jquery.collapse-cart.js',
        'src/js/jquery.product-compare-add.js',
        'src/js/jquery.product-compare-menu.js',
        'src/js/jquery.infinite-scrolling.js',
        'src/js/jquery.shopware-responsive.js'
    );

    private $fieldSetDefaults = array(
        'layout' => 'column',
        'height' => 170,
        'flex' => 0,
        'defaults' => array('columnWidth' => 0.5, 'labelWidth' => 180, 'margin' => '3 16 3 0')
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
                'saturate(lighten(@brand-primary,12%), 5%)'
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
                '_overlay-bg',
                '@overlay-bg',
                '#555555'
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
                'darken(@brand-primary, 10%)'
            )
        );
        $fieldSetScaffolding->addElement(
            $this->createColorPickerField(
                '_rating-star-color',
                '@rating-star-color',
                '@highlight-notice'
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
                '16'
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
                '600'
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
                '@font-size-base'
            )
        );
        $fieldSetHeadlines->addElement(
            $this->createTextField(
                '_font-size-h5',
                '@font-size-h5',
                '14'
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

        $attributes = array_merge($this->fieldSetDefaults, array('height' => 160));
        $fieldSetLabels = $this->createFieldSet(
            'labels_fieldset',
            '__responsive_tab_forms_fieldset_labels__',
            array('attributes' => $attributes)
        );

        $fieldSetLabels->addElement(
            $this->createTextField(
                '_label-font-size',
                '@label-font-size',
                '12'
            )
        );
        $fieldSetLabels->addElement(
            $this->createColorPickerField(
                '_label-color',
                '@label-color',
                '#FFFFFF'
            )
        );
        $fieldSetLabels->addElement(
            $this->createColorPickerField(
                '_label-highlight-success',
                '@label-highlight-success',
                '@highlight-success'
            )
        );
        $fieldSetLabels->addElement(
            $this->createColorPickerField(
                '_label-highlight-error',
                '@label-highlight-error',
                '@highlight-error'
            )
        );
        $fieldSetLabels->addElement(
            $this->createColorPickerField(
                '_label-highlight-notice',
                '@label-highlight-notice',
                '@highlight-notice'
            )
        );
        $fieldSetLabels->addElement(
            $this->createColorPickerField(
                '_label-highlight-info',
                '@label-highlight-info',
                '@highlight-info'
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
                '16'
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
            '__responsive_tab_tables__'
        );

        $attributes = array_merge($this->fieldSetDefaults, array('height' => 140));
        $fieldSetTables = $this->createFieldSet(
            'tables_fieldset',
            '__responsive_tab_tables_fieldset_tables__',
            array('attributes' => $attributes)
        );

        $fieldSetTables->addElement(
            $this->createColorPickerField(
                '_table-header-bg',
                '@table-header-bg',
                '@brand-secondary-dark'
            )
        );
        $fieldSetTables->addElement(
            $this->createColorPickerField(
                '_table-header-color',
                '@table-header-color',
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
                '@gray-light'
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
            'responsiveGlobal',
            '__global_configuration__',
            array(
                'attributes' => array(
                    'padding' => '10',
                    'margin' => '5',
                    'layout' => 'anchor',
                    'defaults' => array('anchor' => '100%', 'labelWidth' => 150)
                )
            )
        );

        $fieldSet->addElement(
            $this->createTextAreaField(
                'additionalCssData',
                '__additional_css_data__',
                '',
                array('attributes' => array('xtype' => 'textarea'))
            )
        );
        $fieldSet->addElement(
            $this->createTextAreaField(
                'additionalJsLibraries',
                '__additional_js_libraries__',
                '',
                array('attributes' => array('xtype' => 'textarea'))
            )
        );
        $fieldSet->addElement(
            $this->createTextField(
                'bodyFontStack',
                'bodyFontStack',
                '"Open Sans", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif'
            )
        );
        $fieldSet->addElement(
            $this->createTextField(
                'headlineFontStack',
                'headlineFontStack',
                '"Open Sans", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif'
            )
        );
        $fieldSet->addElement(
            $this->createTextField(
                'subheadlineFontStack',
                'subheadlineFontStack',
                '"Open Sans", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif'
            )
        );

        $description = Shopware()->Snippets()->getNamespace('themes/responsive/backend/config')->get(
            'desktop_responsive_description'
        );
        $fieldSet->addElement(
            $this->createCheckboxField(
                'desktopResponsive',
                '__desktop_responsive__',
                true,
                array('attributes' => array('boxLabel' => $description))
            )
        );

        $tab->addElement($fieldSet);

        return $tab;
    }

    /**
     * @param ArrayCollection $collection
     */
    public function createConfigSets(ArrayCollection $collection)
    {
        $set = new ConfigSet();
        $set->setName('Grünes Farbschema')->setDescription(
            'Konfiguriert die Farben des Responsive Themes mit unterschiedlichen Grün-Tönen, die von Shopware aufeinander abgestimmt sind'
        )->setValues(array('color' => '#fff'));
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('Blaues Farbschema')->setDescription(
            'Konfiguriert die Farben des Responsive Themes mit unterschiedlichen Blau-Tönen, die von Shopware aufeinander abgestimmt sind'
        )->setValues(array('color' => '#fff'));
        $collection->add($set);

        $set = new ConfigSet();
        $set->setName('Rotes Farbschema')->setDescription(
            'Konfiguriert die Farben des Responsive Themes mit unterschiedlichen Rot-Tönen, die von Shopware aufeinander abgestimmt sind'
        )->setValues(array('color' => '#fff'));

        $collection->add($set);
    }
}
