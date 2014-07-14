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
 * @package    Systeminfo
 * @subpackage View
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/systeminfo/view}

/**
 * Shopware UI - Grid for encoder
 *
 * todo@all: Documentation
 */
//{block name="backend/systeminfo/view/systeminfo/encoder"}
Ext.define('Shopware.apps.Systeminfo.view.systeminfo.Encoder', {

    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.container.Container',

    ui: 'shopware-ui',
    height: 40,

    /**
     * ID to access the component out of other components
     */
    id: 'systeminfo-main-encoder',
    layout: 'fit'
,
    /**
    * Alias name for the view. Could be used to get an instance
    * of the view through Ext.widget('systeminfo-main-encoder')
    * @string
    */
    alias: 'widget.systeminfo-main-encoder',
    /**
    * The window uses a border layout, so we need to set
    * a region for the grid panel
    * @string
    */
    region: 'north',

	style: 'margin-left: 15px; margin-right: 32px; margin-top: 10px;',

    initComponent: function(){
        var me = this;
		var block = Shopware.Notification.createBlockMessage('', 'success');
		me.items = block;

        Ext.Ajax.request({
            url:'{url controller="Systeminfo" action="getEncoder"}',
            scope: me,
            success:function (record) {
                if(!Ext.JSON.decode(record.responseText).data.name){
					var msgData = {
						text: 'No encoder',
						title: '',
						type: 'error'
					};
					block.items.items[0].update(msgData);
                }else{
					var msgData = {
						text: 'You use the ' + Ext.JSON.decode(record.responseText).data.name,
						type: 'success'
					};
					block.items.items[0].update(msgData);
                }
           }
        });

        this.callParent(arguments);
    }
});
//{/block}
