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
 * @package    Systeminfo
 * @subpackage View
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/systeminfo/view}

/**
 * Shopware UI - Grid for encoder
 *
 * todo@all: Documentation
 */
//{block name="backend/systeminfo/view/systeminfo/timezone"}
Ext.define('Shopware.apps.Systeminfo.view.systeminfo.Timezone', {

    /**
    * Extend from the standard ExtJS 4
    * @string
    */
    extend: 'Ext.container.Container',

    ui: 'shopware-ui',
    height: 60,

    layout: 'fit',

    /**
    * Alias name for the view. Could be used to get an instance
    * of the view through Ext.widget('systeminfo-main-encoder')
    * @string
    */
    alias: 'widget.systeminfo-main-timezone',
    /**
    * The window uses a border layout, so we need to set
    * a region for the grid panel
    * @string
    */
    region: 'north',

    style: 'margin-left: 15px; margin-right: 15px; margin-top: 15px;',

    initComponent: function(){
        var me = this,
            block = Shopware.Notification.createBlockMessage('', 'notice');

        me.items = block;

        Ext.Ajax.request({
            url:'{url controller="Systeminfo" action="getTimezone"}',
            success: function (record) {
                var decodedResponse = Ext.JSON.decode(record.responseText),
                    snippet = '';
                if (!decodedResponse.offset) {
                    me.destroy();
                } else {
                    snippet = Ext.String.format(
                        '{s name=time/difference_detected}Difference between database time and php time is [0] minutes{/s}',
                        decodedResponse.offset
                    );
                    block.items.items[0].update({
                        text: snippet
                    });
                }
            }
        });

        me.callParent(arguments);
    }
});
//{/block}
