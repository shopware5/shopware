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
    alias:'widget.first-run-wizard-location',

    /**
     * Name attribute used to generate event names
     */
    name:'location',

    snippets: {
        content: {
            title: '{s name=localization/content/title}Localization{/s}',
            message: '{s name=localization/content/message}Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.{/s}'
        }
    },

    initComponent: function() {
        var me = this;

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
            me.createLanguageSwitcherForm()
        ];

        me.callParent(arguments);
    },

    /**
     * Creates the existing account form
     *
     * @return Ext.form.FieldSet Contains the form for existing account login
     */
    createLanguageSwitcherForm: function () {
        var me = this, localeStore;

        me.buttons = [];

        localeStore = Ext.create('Ext.data.Store', {
            fields: ['name', 'locale'],
            data: [
                { name: 'German',   locale: 'de_DE' },
                { name: 'English',  locale: 'en_GB' }
            ]
        });

        localeStore.each(function(elem) {
            me.buttons.push(
                Ext.create('Ext.Button', {
                    text: elem.get('name'),
                    cls: 'primary',
                    handler: function() {
                        me.fireEvent('switchLanguage', elem.get('locale'));
                    }
                })
            );
        });


        return Ext.create('Ext.form.FieldSet', {
            cls: Ext.baseCSSPrefix + 'base-field-set',
            defaults:{
                anchor:'95%',
                labelWidth:150,
                minWidth:250,
                xtype:'textfield'
            },
            items: me.buttons
        });
    }
});

//{/block}
