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
 * Shopware UI - Translation Manager Main Toolbar
 *
 * todo@all: Documentation
 */

//{block name="backend/translation/view/main/toolbar"}
Ext.define('Shopware.apps.Translation.view.main.Toolbar', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.translation-main-toolbar',
    ui: 'shopware-ui',

    /**
     * Initializes the component and builds up the main interface
     *
     * @public
     * @return void
     */
    initComponent: function() {
        var me = this;
        me.items = me.createToolbarItems();
        me.callParent(arguments);
    },

    /**
     * Creates the toolbar items.
     *
     * @private
     * @return [array] generated toolbar buttons
     */
    createToolbarItems: function() {
        return [{
            text: '{s name=button/google}Google translator{/s}',
            action: 'translation-main-toolbar-google',
            iconCls: 'sprite-google'
        }];
    }
});
//{/block}
