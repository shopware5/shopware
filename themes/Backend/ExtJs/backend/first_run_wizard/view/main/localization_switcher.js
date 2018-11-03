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
 * Shopware First Run Wizard - Localization Switcher
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/first_run_wizard/main}
//{block name="backend/first_run_wizard/view/main/localization_switcher"}

Ext.define('Shopware.apps.FirstRunWizard.view.main.LocalizationSwitcher', {

    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend: 'Enlight.app.SubWindow',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.first-run-wizard-localization-switcher',

    /**
     * Define window width
     * @integer
     */
    width: 360,

    /**
     * Define window height
     * @integer
     */
    height: 260,

    /**
     * Display no footer button for the detail window
     * @boolean
     */
    footerButton: false,

    /**
     * Set vbox layout and stretch align to display the toolbar on top and the button container
     * under the toolbar.
     * @object
     */
    layout: {
        align: 'stretch',
        type: 'vbox'
    },

    /**
     * If the modal property is set to true, the user can't change the window focus to another window.
     * @boolean
     */
    modal: true,

    /**
     * The body padding is used in order to have a smooth side clearance.
     * @integer
     */
    bodyPadding: 10,

    /**
     * Disable window resize
     * @boolean
     */
    resizable: false,

    /**
     * Disables the maximize button in the window header
     * @boolean
     */
    maximizable: false,

    /**
     * Disables the minimize button in the window header
     * @boolean
     */
    minimizable: false,

    /**
     * Contains all snippets for the component
     * @object
     */
    snippets: {
        title: '{s name=localization_switcher/content/title}Choose language{/s}',
        message: '{s name=localization_switcher/content/message}If you installed a translation plugin, you can now reload the backend in that language.{/s}',
        continue:'{s name=localization_switcher/continue}Continue in English{/s}'
    },

    batchSize: 200,

    /**
     * The initComponent template method is an important initialization step for a Component.
     * It is intended to be implemented by each subclass of Ext.Component to provide any needed constructor logic.
     * The initComponent method of the class being created is called first,
     * with each initComponent method up the hierarchy to Ext.Component being called thereafter.
     * This makes it easy to implement and, if needed, override the constructor logic of the Component at any step in the hierarchy.
     * The initComponent method must contain a call to callParent in order to ensure that the parent class' initComponent method is also called.
     *
     * @return void
     */
    initComponent: function () {
        var me = this;

        me.title = me.snippets.title;

        me.items = [
            {
                xtype: 'container',
                border: false,
                bodyPadding: 20,
                style: 'margin-bottom: 10px;',
                html: '<p>' + me.snippets.message + '</p>',
                height: '40px'
            },
            me.createLanguageSwitcherForm()
        ];

        me.buttons = [
            me.createCloseButton()
        ];

        me.callParent(arguments);
    },

    /**
     * Creates the existing account form
     *
     * @return Ext.container.Container Contains the form for existing account login
     */
    createLanguageSwitcherForm: function () {
        var me = this;

        me.languageButtons = [];

        me.store.each(function(elem) {
            me.languageButtons.push(
                Ext.create('Ext.Button', {
                    text: elem.get('name'),
                    cls: 'primary',
                    style: {
                        marginTop: '5px'
                    },
                    handler: function() {
                        me.fireEvent('switchLanguage', elem.get('id'));
                    }
                })
            );
        });

        return Ext.create('Ext.container.Container', {
            items: me.languageButtons,
            overflowY: 'auto',
            height: 120,
            layout: {
                align: 'stretch',
                type: 'vbox'
            }
        });
    },

    /**
     * Creates the close button which allows the user to close the window.
     */
    createCloseButton: function() {
        var me = this;

        return Ext.create('Ext.button.Button', {
            text: me.snippets.continue,
            flex: 1,
            action: 'closeWindow',
            cls: 'secondary',
            handler: function() {
                me.fireEvent('closeWindow');
                me.destroy();
            }
        });
    }
});
//{/block}
