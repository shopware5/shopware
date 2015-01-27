/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

/**
 * Shopware First Run Wizard - Localization tab
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/first_run_wizard/main}
//{block name="backend/first_run_wizard/view/main/localization"}

Ext.define('Shopware.apps.FirstRunWizard.view.main.Localization', {
    extend: 'Ext.container.Container',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.first-run-wizard-localization',

    /**
     * Name attribute used to generate event names
     */
    name:'localization',

    overflowY: 'auto',

    snippets: {
        content: {
            title: '{s name=localization/content/title}Localization{/s}',
            message: '{s name=localization/content/message}Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.{/s}'
        },
        languagePicker: '{s name=localization/languagePicker}Select a language to filter{/s}'
    },

    initComponent: function() {
        var me = this;

        me.localizationStore = Ext.create('Shopware.apps.FirstRunWizard.store.Localization').load();

        me.items = [
            {
                xtype: 'container',
                border: false,
                bodyPadding: 20,
                style: 'font-weight: 700; line-height: 20px;',
                html: '<h1>' + me.snippets.content.title + '</h1>'
            },
            {
                xtype: 'container',
                border: false,
                bodyPadding: 20,
                style: 'margin-bottom: 10px;',
                html: '<p>' + me.snippets.content.message + '</p>'
            },
            me.createLanguagePicker(),
            me.createStoreListing()
        ];

        me.callParent(arguments);
    },

    /**
     * Creates the existing account form
     *
     * @return Ext.form.FieldSet Contains the form for existing account login
     */
    createLanguagePicker: function () {
        var me = this;

        me.languageFilter = Ext.create('Ext.form.field.ComboBox', {
            store: me.localizationStore,
            queryMode: 'local',
            valueField: 'locale',
            displayField: 'text',
            emptyText: me.snippets.languagePicker,
            editable: false,
            listeners: {
                change: {
                    fn: function(view, newValue, oldValue) {
                        me.fireEvent('changeLanguageFilter', newValue);
                    }
                }
            }
        });

        return Ext.create('Ext.form.FieldSet', {
            cls: Ext.baseCSSPrefix + 'base-field-set',
            width: 632,
            defaults: {
                anchor:'100%'
            },
            items: [
                me.languageFilter
            ]
        });
    },

    createStoreListing: function() {
        var me = this;

        me.communityStore = Ext.create('Shopware.apps.FirstRunWizard.store.LocalizationPlugin');
        me.storeListing = Ext.create('Shopware.apps.PluginManager.view.components.Listing', {
            store: me.communityStore,
            scrollContainer: me,
            width: 632
        });

        me.communityStore.on('load', function(store, records) {
            me.storeListing.setLoading(false);
        });

        me.content = Ext.create('Ext.container.Container', {
            items: [
                me.storeListing
            ]
        });

        return me.content;
    },

    refreshData: function() {
        var me = this;

        me.fireEvent('localizationResetData');

        if (me.languageFilter.getValue()) {
            me.storeListing.setLoading(true);
            me.storeListing.resetListing();
            me.communityStore.load();
        }
    }
});

//{/block}
