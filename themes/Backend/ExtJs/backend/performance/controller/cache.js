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
 * @package    Shopware_Performance
 * @subpackage Cache
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author shopware AG
 */

/**
 * The cache controller takes care of cache related events and also
 * handles the category fixing
 */

//{namespace name=backend/performance/main}
//{block name="backend/performance/controller/cache"}
Ext.define('Shopware.apps.Performance.controller.Cache', {
    extend: 'Enlight.app.Controller',

    refs: [
        { ref: 'form', selector: 'performance-tabs-cache-form' },
    ],

    infoTitle: '{s name=form/message_title}Shop cache{/s}',
    infoMessageSuccess: '{s name=form/message}Shop cache has been cleared.{/s}',

    init: function () {
        var me = this;

        me.control({
            'performance-tabs-cache-main button[action=clear]': {
                click: function() {
                    me.getForm().submit({
                        success: function(form) {
                            var themeCacheCleared = form.getFields().findBy(function(record) {
                                return (record.name !== undefined && record.name === 'cache[theme]' && record.checked == true);
                            });

                            me.getStore('Info').load({
                                callback: function() {
                                    if (themeCacheCleared) {
                                        Shopware.app.Application.fireEvent('shopware-theme-cache-warm-up-request');
                                    }

                                    Shopware.Notification.createGrowlMessage(
                                        me.infoTitle,
                                        me.infoMessageSuccess,
                                        me.infoTitle
                                    );
                                }
                            });
                        }
                    });
                }
            },

            'performance-tabs-cache-main button[action=select-all]': {
                click: function() {
                    me.getForm().getForm().getFields().each(function(item) {
                        item.setValue(true);
                    });
                }
            },
        });

        me.callParent(arguments);
    }
});
//{/block}
