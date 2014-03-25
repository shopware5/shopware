/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
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
    width: 1100,
    height:'90%',

    loadTitle: function(record) {
        var me = this,
            title = me.titleTemplate;
        title = new Ext.Template(title).applyTemplate(record.data);
        me.setTitle(title);
    },

    /**
     *
     */
    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: me.getItems()
        });

        if(me.mode && me.mode === 'iframe-mode') {
            me.title = '';
            me.renderTo = Ext.getBody();
            me.unstyled = true;
            me.width = '100%';
            me.height = '90%';
        }
        me.callParent(arguments);
    },

    /**
     * @return array
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