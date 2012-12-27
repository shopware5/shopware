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
Ext.define('Shopware.apps.Cache.controller.Main', {

    extend: 'Enlight.app.Controller',

    views: [ 'main.Window', 'main.Form', 'main.Info' ],
    stores:[ 'main.Info' ],

    refs: [
        { ref: 'window', selector: 'cache-window' },
        { ref: 'info', selector: 'cache-info dataview' },
        { ref: 'form', selector: 'cache-form' }
    ],

    infoTitle: '{s name=form/message_title}Shop cache{/s}',

    infoMessageSuccess: '{s name=form/message}Shop cache has been cleared.{/s}',

    /**
     * Class property which holds the main application if it is created
     *
     * @default null
     * @object
     */
    mainWindow: null,

    /**
     *
     */
    init: function () {
        var me = this;

        me.mainWindow = me.getView('main.Window').create({
            autoShow: true
        }).show();

        me.control({
            'cache-form button[action=clear]': {
                click: function(button, event) {
                    me.getForm().submit();
                }
            },
            'cache-form button[action=select-all]': {
                click: function(button, event) {
                    me.getForm().getForm().getFields().each(function(item) {
                        item.setValue(true);
                    });
                }
            },
            'cache-form': {
                actioncomplete: function(form, action) {
                    me.getStore('main.Info').load({
                        callback: function(records, operation) {
                            Shopware.Notification.createGrowlMessage(
                                me.infoTitle,
                                me.infoMessageSuccess,
                                me.infoTitle
                            );
                        }
                    });
                }
            }
        });

        me.callParent(arguments);
    }
});
//{/block}
