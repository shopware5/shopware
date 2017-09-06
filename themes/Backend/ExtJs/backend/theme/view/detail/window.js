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
 * Shopware Application
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */

//{namespace name=backend/theme/main}

//{block name="backend/theme/view/detail/window"}

Ext.define('Shopware.apps.Theme.view.detail.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.theme-detail-window',
    title : '{s name=detail_window}Theme details{/s}',
    height: 600,
    width: 1080,
    layout: {
        type: 'vbox',
        align: 'stretch'
    },
    defaults: {
        flex: 1
    },
    cls: 'theme-config-window',

    initComponent: function() {
        var me = this;

        me.items = [ me.createFormPanel() ];
        me.dockedItems = [ me.createToolbar() ];

        me.callParent(arguments);
    },

    /**
     * Creates the form panel for the window.
     * @returns { Ext.form.Panel }
     */
    createFormPanel: function() {
        var me = this;

        if (me.theme.get('themeInfo')) {
            me.createThemeInfoTab();
        }

        me.formPanel = Ext.create('Ext.form.Panel', {
            layout: 'fit',
            items: me.configLayout
        });

        return me.formPanel;
    },

    /**
     * If the theme has the folder "themeInfo" and a language html file like "en_EN.html"
     * then add a theme info tab to the layout items to display the passed
     * HTML content in the container.
     */
    createThemeInfoTab: function() {
        var me = this;

        me.configLayout[0].items.splice(0, 0, {
            title: '{s name="info_tab_title"}{/s}',
            html: me.theme.get('themeInfo'),
            bodyPadding: 0,
            xtype: 'theme-tab'
        });
    },

    /**
     * Creates the window toolbar.
     *
     * @returns { Ext.toolbar.Toolbar }
     */
    createToolbar: function () {
        var me = this;

        me.toolbar = Ext.create('Ext.toolbar.Toolbar', {
            items: me.createToolbarItems(),
            dock: 'bottom'
        });

        return me.toolbar;
    },

    /**
     * Creates all toolbar elements.
     *
     * @returns { Array }
     */
    createToolbarItems: function() {
        var me = this, items = [];

        if (me.theme.get('hasConfigSet')) {
            items.push(me.createConfigSetButton());
        }

        items.push({ xtype: 'tbfill' });

        items.push(me.createCancelButton());

        items.push(me.createSaveButton());

        return items;
    },


    createConfigSetButton: function () {
        var me = this;

        me.cancelButton = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            name: 'cancel-button',
            text: '{s name=config_sets}Config sets{/s}',
            handler: function () {
                me.fireEvent('load-config-sets', me, me.theme);
            }
        });
        return me.cancelButton;
    },


    /**
     * Creates the cancel button which will be displayed
     * in the bottom toolbar of the detail window.
     * The button handler will be raised to the internal
     * function me.onCancel
     *
     * @return Ext.button.Button
     */
    createCancelButton: function () {
        var me = this;

        me.cancelButton = Ext.create('Ext.button.Button', {
            cls: 'secondary',
            name: 'cancel-button',
            text: '{s name=cancel}Cancel{/s}',
            handler: function () {
                me.destroy();
            }
        });
        return me.cancelButton;
    },

    /**
     * Creates the save button which will be displayed
     * in the bottom toolbar of the detail window.
     * The button handler will be raised to the internal
     * function me.onSave
     *
     * @return Ext.button.Button
     */
    createSaveButton: function () {
        var me = this;

        me.saveButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            name: 'detail-save-button',
            text: '{s name=save}Save{/s}',
            handler: function () {
                me.fireEvent(
                    'saveConfig',
                    me.theme,
                    me.shop,
                    me.formPanel,
                    me
                );
            }
        });
        return me.saveButton;
    }

});

//{/block}
