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
 *
 * @category   Shopware
 * @package    Translation
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/translation/view/main}

/**
 * Shopware UI - Translation Manager Main Window
 *
 * todo@all: Documentation
 */
//{block name="backend/translation/view/main/window"}
Ext.define('Shopware.apps.Translation.view.main.Window',
/** @lends Enlight.app.Window# */
{
    extend: 'Enlight.app.Window',
    title: '{s name=window_title}Translation{/s}',
    cls: Ext.baseCSSPrefix + 'translation-manager-window',
    alias: 'widget.translation-main-window',
    border: false,
    autoShow: true,
    layout: 'border',
    height: 600,
    width: 1000,

    /**
     * Initializes the component and builds up the main interface
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;

        me.items = me.createItems();
        me.buttons = me.createActionButtons();

        me.callParent(arguments);
    },

    /**
     * Creates the items for the main window.
     *
     * @private
     * @return [array] generated items array
     */
    createItems: function() {
        var me = this;

        return [{
            xtype: 'translation-main-toolbar',
            dock: 'top',
            region: 'north'
        },{
            xtype: 'translation-main-navigation',
            treeStore: me.treeStore,
            region: 'west'
        }, {
            xtype: 'translation-main-form',
            translatableFields: me.translatableFields,
            region: 'center',
            autoScroll: true
        }];
    },

    /**
     * Creates the action buttons which will be
     * rendered to the main window footer
     *
     * @private
     * @return [array] generated buttons array
     */
    createActionButtons: function() {
        return [{
            text: '{s name=button/cancel}Cancel{/s}',
            cls: 'secondary',
            action: 'translation-main-window-cancel'
        }, {
            text: '{s name=button/save_and_close}Save and close{/s}',
            cls: 'primary',
            action: 'translation-main-window-save-and-close'
        }, {
            text: '{s name=button/save}Save translations{/s}',
            cls: 'primary',
            action: 'translation-main-window-save'
        }];
    }
});
//{/block}
