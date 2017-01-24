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
 * @package    Shopware_Config
 * @subpackage Config
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/config/view/main}

/**
 * todo@all: Documentation
 */
//{block name="backend/config/view/main/window"}
Ext.define('Shopware.apps.Config.view.main.Window', {
    extend: 'Enlight.app.Window',
    alias: 'widget.config-main-window',
    layout: 'border',

    title: '{s name=window/title}Basic settings{/s}',
    titleTemplate: '{s name=window/title_template}Basic settings - [label]{/s}',

    cls: Ext.baseCSSPrefix + 'template-main-window',
    hideNavigation: false,
    width: '80%',
    height:'90%',

    loadTitle: function(record) {
        var me = this,
            title = new Ext.Template(me.titleTemplate).applyTemplate(record.data);

        me.setTitle(title);
    },

    /**
     * Initializes the component and creates its items
     */
    initComponent: function() {
        var me = this;

        me.items = me.getItems();

        me.callParent(arguments);
    },

    /**
     * @return { Array }
     */
    getItems: function() {
        var me = this;
        me.contentPanel = Ext.create('Shopware.apps.Config.view.main.Panel', {
            region: 'center'
        });
        return [{
            region: 'west',
            hidden: me.hideNavigation,
            xtype: 'config-navigation'
        }, me.contentPanel ];
    }
});
//{/block}
