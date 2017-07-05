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
//{block name="backend/first_run_wizard/view/main/localization_installer"}

Ext.define('Shopware.apps.FirstRunWizard.view.main.LocalizationInstaller', {

    /**
     * Define that the order main window is an extension of the enlight application window
     * @string
     */
    extend: 'Enlight.app.SubWindow',

    /**
     * List of short aliases for class names. Most useful for defining xtypes for widgets.
     * @string
     */
    alias:'widget.first-run-wizard-localization-installer',

    /**
     * Define window width
     * @integer
     */
    width: 450,

    /**
     * Define window height
     * @integer
     */
    height: 210,

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
    bodyPadding: 20,

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
        title: '{s name=localization_installer/content/title}Choose language{/s}',
        message: '{s name=localization_installer/content/message}During the installation you chose [language] as the preferred language.{/s}',
        button: '{s name=localization_installer/content/button}Install and continue in [language] ([country]).{/s}',
        continue: '{s name=localization_installer/continue}Continue in English{/s}',
        locales: {
            'bg_BG': { 'language': '{s name=localization_installer/content/bg_BG/language}Bulgarian{/s}', 'country': '{s name=localization_installer/content/bg_BG/country}Bulgaria{/s}' },
            'cz_CZ': { 'language': '{s name=localization_installer/content/cz_CZ/language}Czech{/s}', 'country': '{s name=localization_installer/content/cz_CZ/country}Czech Republic{/s}' },
            'de_DE': { 'language': '{s name=localization_installer/content/de_DE/language}German{/s}', 'country': '{s name=localization_installer/content/de_DE/country}Germany{/s}' },
            'es_ES': { 'language': '{s name=localization_installer/content/es_ES/language}Spanish{/s}', 'country': '{s name=localization_installer/content/es_ES/country}Spain{/s}' },
            'fi_FI': { 'language': '{s name=localization_installer/content/fi_FI/language}Finish{/s}', 'country': '{s name=localization_installer/content/fi_FI/country}Finland{/s}' },
            'fr_FR': { 'language': '{s name=localization_installer/content/fr_FR/language}French{/s}', 'country': '{s name=localization_installer/content/fr_FR/country}France{/s}' },
            'it_IT': { 'language': '{s name=localization_installer/content/it_IT/language}Italian{/s}', 'country': '{s name=localization_installer/content/it_IT/country}Italy{/s}' },
            'nl_NL': { 'language': '{s name=localization_installer/content/nl_NL/language}Dutch{/s}', 'country': '{s name=localization_installer/content/nl_NL/country}Netherlands{/s}' },
            'pt_PT': { 'language': '{s name=localization_installer/content/pt_PT/language}Portuguese{/s}', 'country': '{s name=localization_installer/content/pt_PT/country}Portugal{/s}' },
            'pl_Pl': { 'language': '{s name=localization_installer/content/pl_Pl/language}Polish{/s}', 'country': '{s name=localization_installer/content/pl_Pl/country}Poland{/s}' },
            'tk_TK': { 'language': '{s name=localization_installer/content/tk_TK/language}Turkish{/s}', 'country': '{s name=localization_installer/content/tk_TK/country}Turkey{/s}' },
            'ru_RU': { 'language': '{s name=localization_installer/content/ru_RU/language}Russian{/s}', 'country': '{s name=localization_installer/content/ru_RU/country}Russia{/s}' }
        }
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
        var me = this,
            locales = [],
            installerLocale = this.installerLocale;

        locales = Ext.Object.getKeys(me.snippets.locales);

        if (!Ext.Array.contains(locales, installerLocale)) {
            installerLocale = '{s namespace="backend/base/index" name=script/ext/locale}{/s}';
        }
        if (!Ext.Array.contains(locales, installerLocale)) {
            throw new Error('Locale unknown');
        }

        me.title = me.snippets.title;

        me.items = [
            {
                xtype: 'container',
                border: false,
                html: '<p>' + me.snippets.message.replace('[language]', me.snippets.locales[installerLocale]['language']) + '</p>',
                height: '40px'
            },
            me.createLanguageSwitcherForm(installerLocale)
        ];

        me.callParent(arguments);
    },

    /**
     * Creates the existing account form
     *
     * @param { string } installerLocale Locale used in installer
     * @return Ext.container.Container Contains the form for existing account login
     */
    createLanguageSwitcherForm: function (installerLocale) {
        var me = this;

        me.languageButton = Ext.create('Ext.Button', {
            margins: '5px 0 20px 0',
            text: me.snippets.button
                .replace('[language]', me.snippets.locales[installerLocale]['language'])
                .replace('[country]', me.snippets.locales[installerLocale]['country']),
            cls: 'primary',
            handler: function() {
                me.fireEvent('installLanguage', me.pluginName, installerLocale);
                me.fireEvent('closeWindow');
                me.destroy();
            }
        });

        /**
         * Creates the close button which allows the user to close the window.
         */
        me.closeButton = Ext.create('Ext.button.Button', {
            text: me.snippets.continue,
            action: 'closeWindow',
            cls: 'secondary',
            handler: function() {
                me.fireEvent('closeWindow');
                me.destroy();
            }
        });

        return Ext.create('Ext.container.Container', {
            items: [
                me.languageButton,
                me.closeButton
            ],
            layout: {
                align: 'stretch',
                type: 'vbox'
            }
        });
    }
});
//{/block}
