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
 * @package    Shopware_Cache
 * @subpackage Cache
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

//{namespace name=backend/cache/view/main}

/**
 * Shopware Controller - Cache backend module
 *
 * todo@all: Documentation
 */
//{block name="backend/article/controller/main"}
Ext.define('Shopware.apps.Cache.controller.Direct', {

    extend: 'Enlight.app.Controller',

    infoTitle: '{s name=direct/message_title}Shop cache{/s}',

    infoMessages: {
        'Template': '{s name=direct/messages/template}Template cache has been cleared{/s}',
        'Config': '{s name=direct/messages/config}Configuration cache has been cleared{/s}',
        'Frontend': '{s name=direct/messages/frontend}Article+category cache has been cleared{/s}',
        'Proxy': '{s name=direct/messages/proxy}Proxy/Model cache has been cleared{/s}'
    },

    init: function () {
        var me = this;

        Ext.Ajax.request({
            url: '{url action=clearDirect}?cache=' + me.action,
            success: function() {
                Shopware.Notification.createGrowlMessage(
                    me.infoTitle,
                    me.infoMessages[me.action],
					me.infoTitle
                );
            }
        });

        me.callParent(arguments);
    }
});
//{/block}
