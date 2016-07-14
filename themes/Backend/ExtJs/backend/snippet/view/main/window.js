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

//{namespace name=backend/snippet/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/snippet/view/main/window"}
Ext.define('Shopware.apps.Snippet.view.main.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.snippet-main-window',

    layout: 'border',
    width: 980,
    height: '90%',
    stateful: true,
    stateId: 'shopware-snippet-main-window',

    /**
     * Contains all snippets for this view
     * @object
     */
    snippets: {
        title:                  '{s name=title}Snippet administration{/s}',
        buttonInstallLanguage:  '{s name=button_install_language}Install new Language{/s}',
        buttonRemoveLanguage:   '{s name=button_remove_language}Remove Language{/s}',
        buttonLanguages:        '{s name=button_languages}Languages{/s}',
        buttonImportExport:     '{s name=button_import_export}Import / Export{/s}',
        buttonExpert:           '{s name=button_expert}Expert-Mode{/s}'
    },

    /**
     * Initializes the component and builds up the main interface
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.title = me.snippets.title;

        me.items = [{
            xtype: 'snippet-main-navigation',
            region: 'west',
            width: 180,
            store: me.nSpaceStore
        }, {
            xtype: 'snippet-main-snippetPanel',
            region: 'center',
            nSpaceStore: me.nSpaceStore,
            snippetStore: me.snippetStore,
            shoplocaleStore: me.shoplocaleStore
        }];

        me.tbar = me.getToolbar();

        me.callParent(arguments);
    },

    /**
     * Creates the toolbar.
     *
     * @return [object] generated Ext.toolbar.Toolbar
     */
    getToolbar: function() {
        var me      = this,
            buttons = [];

        buttons.push({
            xtype: 'button',
            text: me.snippets.buttonImportExport,
            action: 'export',
            iconCls: 'sprite-arrow-circle-double-135'
        });

        buttons.push({
            xtype: 'tbseparator'
        });

        buttons.push({
            xtype: 'button',
            iconCls: 'sprite-construction',
            text: me.snippets.buttonExpert,
            action: 'expert',
            enableToggle: true
        });

        return {
            xtype: 'toolbar',
            ui: 'shopware-ui',
            items: buttons
        };
    }
});
//{/block}
